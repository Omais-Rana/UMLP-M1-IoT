/*
 * FINAL VIDEO UNSCRAMBLER
 * - Fixes EOS Loop (Clean Exit)
 * - Fixes Key Search (Height Scaling)
 * - Saves Output to File
 */

#include <jetson-utils/videoSource.h>
#include <jetson-utils/videoOutput.h>
#include <jetson-utils/cudaResize.h>
#include <jetson-utils/cudaMappedMemory.h>
#include <jetson-utils/cudaFont.h>

#include <cuda_runtime.h>
#include <iostream>
#include <vector>
#include <cmath>
#include <limits>
#include <chrono>

using namespace std;

// ==========================================
// 1. DATA STRUCTURES
// ==========================================
struct Block
{
    int start_y;
    int size;
};

// ==========================================
// 2. HOST HELPERS
// ==========================================
int get_largest_pow2(int n)
{
    int p = 1;
    while ((p << 1) <= n)
        p <<= 1;
    return p;
}

std::vector<Block> generate_blocks(int height)
{
    std::vector<Block> blocks;
    int processed = 0;
    while (processed < height)
    {
        int remaining = height - processed;
        int size = get_largest_pow2(remaining);
        blocks.push_back({processed, size});
        processed += size;
    }
    return blocks;
}

inline float get_lum(const uchar3 &p)
{
    return 0.299f * p.x + 0.587f * p.y + 0.114f * p.z;
}

// ==========================================
// 3. KEY SEARCH
// ==========================================
double calculate_score(const uchar3 *scrambledBuf, int width, int height,
                       const std::vector<Block> &blocks, int r, int s)
{
    double total_diff = 0.0;
    int center_x = width / 2;

    for (int y = 0; y < height - 1; ++y)
    {
        int start = 0, size = 0;
        for (const auto &b : blocks)
        {
            if (y >= b.start_y && y < b.start_y + b.size)
            {
                start = b.start_y;
                size = b.size;
                break;
            }
        }

        if (y + 1 >= start + size)
            continue;

        int local_y = y - start;
        int term = 2 * s + 1;
        long long src_offset = ((long long)term * local_y + r) % size;
        int src_y = start + (int)src_offset;

        int local_y_next = (y + 1) - start;
        long long src_offset_next = ((long long)term * local_y_next + r) % size;
        int src_y_next = start + (int)src_offset_next;

        uchar3 p1 = scrambledBuf[src_y * width + center_x];
        uchar3 p2 = scrambledBuf[src_y_next * width + center_x];

        float diff = get_lum(p1) - get_lum(p2);
        total_diff += (diff * diff);
    }
    return total_diff;
}

void find_best_key(const uchar3 *mappedBuf, int w, int h, uint8_t &best_r, uint8_t &best_s)
{
    std::vector<Block> blocks = generate_blocks(h);
    double min_score = std::numeric_limits<double>::max();
    int found_r = 0, found_s = 0;

    for (int s = 0; s < 128; ++s)
    {
        for (int r = 0; r < 256; ++r)
        {
            double s_val = calculate_score(mappedBuf, w, h, blocks, r, s);
            if (s_val < min_score)
            {
                min_score = s_val;
                found_r = r;
                found_s = s;
            }
        }
    }
    best_r = (uint8_t)found_r;
    best_s = (uint8_t)found_s;
}

// ==========================================
// 4. GPU KERNEL
// ==========================================
__global__ void unscramble_kernel(uchar3 *src, uchar3 *dst, int width, int height,
                                  Block *device_blocks, int num_blocks,
                                  int r, int s)
{
    int x = blockIdx.x * blockDim.x + threadIdx.x;
    int y = blockIdx.y * blockDim.y + threadIdx.y;

    if (x >= width || y >= height)
        return;

    int start = 0, size = 0;
    for (int i = 0; i < num_blocks; i++)
    {
        if (y >= device_blocks[i].start_y && y < (device_blocks[i].start_y + device_blocks[i].size))
        {
            start = device_blocks[i].start_y;
            size = device_blocks[i].size;
            break;
        }
    }

    if (size == 0)
        return;

    int dst_local = y - start;
    int term = 2 * s + 1;
    long long calc = ((long long)term * dst_local + r) % size;
    int src_y = start + (int)calc;

    dst[y * width + x] = src[src_y * width + x];
}

// ==========================================
// 5. MAIN PROGRAM
// ==========================================
int main(int argc, char **argv)
{
    // Create Input
    videoSource *input = videoSource::Create(argc, argv, ARG_POSITION(0));
    if (!input)
    {
        std::cerr << "Failed to create input\n";
        return 1;
    }

    // Create Output - FORCE ARGUMENT 2
    videoOutput *output = nullptr;

    // Check if user provided a second argument (argv[2])
    if (argc > 2)
    {
        const char *outputURI = argv[2];
        std::cout << "FORCING OUTPUT TO: " << outputURI << std::endl;
        // Create purely from the string, ignoring other flags for the URI
        output = videoOutput::Create(outputURI);
    }

    if (!output)
    {
        std::cerr << "ERROR: Could not create output stream.\n";
        std::cerr << "Make sure you run: ./unscramble input.mp4 file://output.mp4\n";
        return 1; // Stop if we can't create the output
    }

    int width = input->GetWidth();
    int height = input->GetHeight();

    uchar3 *cpu_access_ptr = nullptr;
    if (!cudaAllocMapped((void **)&cpu_access_ptr, width * height * sizeof(uchar3)))
        return 1;

    uchar3 *gpu_src = nullptr;
    uchar3 *gpu_dst = nullptr;
    cudaMalloc((void **)&gpu_dst, width * height * sizeof(uchar3));

    cudaFont *font = cudaFont::Create();

    // --- PHASE 1: SKIP & SEARCH ---
    int status = 0;

    std::cout << "Capturing for Key Search...\n";
    if (!input->Capture(&gpu_src, 1000, &status))
        return 1;

    cudaMemcpy(cpu_access_ptr, gpu_src, width * height * sizeof(uchar3), cudaMemcpyDeviceToDevice);
    cudaDeviceSynchronize();

    uint8_t best_r = 0, best_s = 0;
    find_best_key(cpu_access_ptr, width, height, best_r, best_s);
    std::cout << "KEY FOUND: r=" << (int)best_r << " s=" << (int)best_s << "\n";

    // --- PHASE 2: UPLOAD BLOCKS ---
    std::vector<Block> host_blocks = generate_blocks(height);
    Block *dev_blocks = nullptr;
    cudaMalloc((void **)&dev_blocks, host_blocks.size() * sizeof(Block));
    cudaMemcpy(dev_blocks, host_blocks.data(), host_blocks.size() * sizeof(Block), cudaMemcpyHostToDevice);

    // --- PHASE 3: PROCESS & SAVE ---
    std::cout << "Processing video...\n";

    dim3 block_dim(16, 16);
    dim3 grid_dim((width + 15) / 16, (height + 15) / 16);

    int frames = 0;
    int consecutive_errors = 0;
    char str[256];

    while (true)
    {
        if (!input->Capture(&gpu_src, 1000, &status))
        {
            // STOP condition: EOS or Stream Closed
            if (status == videoSource::EOS || !input->IsStreaming())
            {
                std::cout << "Video finished. Saving file...\n";
                break;
            }
            // TIMEOUT condition: Try a few times, then quit
            if (status == videoSource::TIMEOUT)
            {
                consecutive_errors++;
                if (consecutive_errors > 5)
                    break;
                continue;
            }
            break;
        }
        consecutive_errors = 0;

        // Unscramble
        unscramble_kernel<<<grid_dim, block_dim>>>(gpu_src, gpu_dst, width, height,
                                                   dev_blocks, (int)host_blocks.size(),
                                                   (int)best_r, (int)best_s);
        cudaDeviceSynchronize();

        // Overlay text
        frames++;
        if (frames % 20 == 0)
        {
            sprintf(str, "Key: r=%d s=%d | FPS: %.0f", (int)best_r, (int)best_s, input->GetFrameRate());
        }
        font->OverlayText(gpu_dst, width, height, str, 5, 5, make_float4(255, 255, 255, 255), make_float4(0, 0, 0, 150));

        // SAVE FRAME
        if (output)
        {
            output->Render(gpu_dst, width, height);
            if (!output->IsStreaming())
                break; // Exit if window closed
        }
    }

    // --- CLEANUP (CRITICAL FOR SAVING FILE) ---
    cudaFree(gpu_dst);
    cudaFree(dev_blocks);
    cudaFreeHost(cpu_access_ptr);
    SAFE_DELETE(input);

    // This line finalizes the MP4 file
    SAFE_DELETE(output);
    SAFE_DELETE(font);

    std::cout << "Done.\n";
    return 0;
}