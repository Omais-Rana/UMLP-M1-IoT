#include <jetson-utils/videoSource.h>
#include <jetson-utils/videoOutput.h>
#include <jetson-utils/cudaResize.h>
#include <jetson-utils/cudaMappedMemory.h>
#include <jetson-utils/cudaFont.h>
#include <chrono>
#include <cuda_runtime.h>
#include <iostream>
#include <vector>
#include <limits>
#include <cmath>

using namespace std;

#define SEARCH_STRIP_WIDTH 32
#define MAX_BLOCKS 16

struct Block
{
    int start_y;
    int size;
};

__device__ inline float get_lum(uchar3 p)
{
    return 0.299f * p.x + 0.587f * p.y + 0.114f * p.z;
}

__global__ void searchKeyKernel(const uchar3 *strip,
                                float *results,
                                int height,
                                Block *blocks,
                                int num_blocks)
{
    int idx = blockIdx.x * blockDim.x + threadIdx.x;
    if (idx >= 32768)
        return;

    int r = idx % 256;
    int s = idx / 256;
    int a = 2 * s + 1;
    int start  = blocks[0].start_y;
    int size   = blocks[0].size;
    int end_y  = start + size - 1;

    float score = 0.0f;
    for (int y = start; y < end_y; ++y)
    {
        long long posA = ((long long)a * (y - start) + r) % size;
        long long posB = ((long long)a * (y + 1 - start) + r) % size;

        int src_y_A = start + (int)posA;
        int src_y_B = start + (int)posB;

        float sum_sq = 0.0f;

        for (int x = 0; x < SEARCH_STRIP_WIDTH; x++)
        {
            uchar3 pA = strip[src_y_A * SEARCH_STRIP_WIDTH + x];
            uchar3 pB = strip[src_y_B * SEARCH_STRIP_WIDTH + x];

            float lumA = 0.299f * pA.x + 0.587f * pA.y + 0.114f * pA.z;
            float lumB = 0.299f * pB.x + 0.587f * pB.y + 0.114f * pB.z;

            float diff = lumA - lumB;
            sum_sq += diff * diff;
        }

        score += sqrtf(sum_sq);
    }

    results[idx] = score;
}

__global__ void unscrambleKernel(uchar3 *src, uchar3 *dst, int width, int height,
                                 Block *blocks, int num_blocks, int r, int s)
{
    int x = blockIdx.x * blockDim.x + threadIdx.x;
    int y = blockIdx.y * blockDim.y + threadIdx.y;
    if (x >= width || y >= height)
        return;

    int start = 0, size = 0;
    for (int i = 0; i < num_blocks; i++)
    {
        if (y >= blocks[i].start_y && y < blocks[i].start_y + blocks[i].size)
        {
            start = blocks[i].start_y;
            size = blocks[i].size;
            break;
        }
    }
    if (size == 0)
        return;

    int a = 2 * s + 1;
    int local_y = y - start;
    long long pos = ((long long)a * local_y + r) % size;
    int src_y = start + (int)pos;
    dst[y * width + x] = src[src_y * width + x];
}

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

int main(int argc, char **argv)
{
    videoSource *input = videoSource::Create(argc, argv, ARG_POSITION(0));
    if (!input)
    {
        cerr << "Input failed." << endl;
        return 1;
    }

    videoOutput *output = nullptr;
    if (argc > 2)
    {
        cout << "FORCING OUTPUT: " << argv[2] << endl;
        output = videoOutput::Create(argv[2]);
    }
    if (!output)
    {
        cerr << "Usage: ./unscramble in.mp4 file://out.mp4" << endl;
        return 1;
    }

    int width = input->GetWidth();
    int height = input->GetHeight();

    uchar3 *gpu_src = nullptr, *gpu_dst = nullptr;
    cudaMalloc((void **)&gpu_dst, width * height * sizeof(uchar3));

    uchar3 *d_strip;
    float *d_results;
    cudaMalloc((void **)&d_strip, height * SEARCH_STRIP_WIDTH * sizeof(uchar3));
    cudaMalloc((void **)&d_results, 32768 * sizeof(float));

    uchar3 *cpu_frame = nullptr;
    cudaAllocMapped((void **)&cpu_frame, width * height * sizeof(uchar3));
    float *h_results = (float *)malloc(32768 * sizeof(float));
    uchar3 *h_strip = (uchar3 *)malloc(height * SEARCH_STRIP_WIDTH * sizeof(uchar3));

    cudaFont *font = cudaFont::Create();
    int status = 0;

    cout << "Capturing..." << endl;
    if (!input->Capture(&gpu_src, 1000, &status))
        return 1;

    cudaMemcpy(cpu_frame, gpu_src, width * height * sizeof(uchar3), cudaMemcpyDeviceToDevice);
    cudaDeviceSynchronize();

    int start_x = (width / 2) - (SEARCH_STRIP_WIDTH / 2);
    for (int y = 0; y < height; y++)
    {
        for (int x = 0; x < SEARCH_STRIP_WIDTH; x++)
        {
            h_strip[y * SEARCH_STRIP_WIDTH + x] = cpu_frame[y * width + (start_x + x)];
        }
    }
    cudaMemcpy(d_strip, h_strip, height * SEARCH_STRIP_WIDTH * sizeof(uchar3), cudaMemcpyHostToDevice);

    std::vector<Block> h_blocks = generate_blocks(height);
    Block *d_blocks;
    cudaMalloc((void **)&d_blocks, h_blocks.size() * sizeof(Block));
    cudaMemcpy(d_blocks, h_blocks.data(), h_blocks.size() * sizeof(Block), cudaMemcpyHostToDevice);

    cout << "High Precision GPU Search..." << endl;
    searchKeyKernel<<<256, 128>>>(d_strip, d_results, height, d_blocks, h_blocks.size());

    cudaError_t err = cudaDeviceSynchronize();
    if (err != cudaSuccess)
    {
        cerr << "CUDA ERROR: " << cudaGetErrorString(err) << endl;
        return 1;
    }

    cudaMemcpy(h_results, d_results, 32768 * sizeof(float), cudaMemcpyDeviceToHost);
    float min_score = std::numeric_limits<float>::max();
    int best_idx = 0;
    for (int i = 0; i < 32768; i++)
    {
        if (h_results[i] < min_score && h_results[i] >= 0)
        {
            min_score = h_results[i];
            best_idx = i;
        }
    }
    int best_r = best_idx % 256;
    int best_s = best_idx / 256;
    cout << "KEY: " << best_r << ", " << best_s << endl;
    cout << "Rendering..." << endl;
    dim3 block_dim(16, 16);
    dim3 grid_dim((width + 15) / 16, (height + 15) / 16);
    char str[256];
    int consec_err = 0;

    auto last_time = std::chrono::high_resolution_clock::now();
    float fps = 0.0f;

    while (true)
    {
        if (!input->Capture(&gpu_src, 1000, &status))
        {
            if (status == videoSource::EOS || !input->IsStreaming())
                break;
            if (status == videoSource::TIMEOUT && ++consec_err > 5)
                break;
            continue;
        }
        consec_err = 0;

        auto current_time = std::chrono::high_resolution_clock::now();
        float elapsed = std::chrono::duration<float>(current_time - last_time).count();
        last_time = current_time;
        fps = 1.0f / elapsed;

        unscrambleKernel<<<grid_dim, block_dim>>>(gpu_src, gpu_dst, width, height,
                                                  d_blocks, h_blocks.size(), best_r, best_s);
        cudaDeviceSynchronize();

        sprintf(str, "Key: %d, R=%d, S=%d, FPS=%.1f", best_r + best_s * 256, best_r, best_s, fps);
        font->OverlayText(gpu_dst, width, height, str, 5, 5, make_float4(255, 255, 255, 255), make_float4(0, 0, 0, 150));

        if (output)
        {
            output->Render(gpu_dst, width, height);
            if (!output->IsStreaming())
                break;
        }
    }

    SAFE_DELETE(output);
    SAFE_DELETE(input);
    SAFE_DELETE(font);
    cudaFree(gpu_dst);
    cudaFree(d_strip);
    cudaFree(d_results);
    cudaFree(d_blocks);
    free(h_strip);
    free(h_results);
    cudaFreeHost(cpu_frame);

    cout << "Done." << endl;
    return 0;
}
