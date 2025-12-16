import time
import random
import matplotlib.pyplot as plt
from tsp_algorithms import nearest_neighbor, naive_dijkstra_tsp, brute_force_tsp

def generate_random_symmetric_matrix(n):
    matrix = [[0]*n for _ in range(n)]
    for i in range(n):
        for j in range(i+1, n):
            w = random.randint(10, 100)
            matrix[i][j] = w
            matrix[j][i] = w
    return matrix

def measure_time(algo_func, matrix):
    start = time.perf_counter()
    algo_func(matrix)
    end = time.perf_counter()
    return (end - start) * 1000 # Convert to milliseconds

def main():
    # We limit N to 10 because Brute Force O(N!) explodes after that.
    # 10! = 3.6 million operations (manageable)
    # 11! = 39 million (slow)
    sizes = list(range(2, 11))
    
    times_nn = []
    times_dijkstra = []
    times_brute = []
    
    print("Measuring performance (this might take a few seconds due to Brute Force)...")
    
    for n in sizes:
        print(f"Running for N={n}...")
        matrix = generate_random_symmetric_matrix(n)
        
        # Measure Nearest Neighbor
        t_nn = measure_time(nearest_neighbor, matrix)
        times_nn.append(t_nn)
        
        # Measure Naive Dijkstra
        t_dij = measure_time(naive_dijkstra_tsp, matrix)
        times_dijkstra.append(t_dij)
        
        # Measure Brute Force
        t_bf = measure_time(brute_force_tsp, matrix)
        times_brute.append(t_bf)

    # Plotting
    plt.figure(figsize=(10, 6))
    
    plt.plot(sizes, times_nn, marker='o', label='Nearest Neighbor (O(N^2))')
    plt.plot(sizes, times_dijkstra, marker='s', label='Dijkstra (O(N log N))')
    plt.plot(sizes, times_brute, marker='^', label='Brute Force (O(N!))', color='red')
    
    plt.title('Time Complexity Comparison of TSP Algorithms')
    plt.xlabel('Number of Cities (N)')
    plt.ylabel('Execution Time (milliseconds)')
    plt.yscale('log') # Log scale is crucial because N! grows insanely fast
    plt.grid(True, which="both", ls="-")
    plt.legend()
    
    print("Plot generated. Close the window to finish.")
    plt.show()

if __name__ == "__main__":
    main()
