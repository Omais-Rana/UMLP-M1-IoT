from graph import Graph
import random

def print_matrix(matrix):
    n = len(matrix)
    # geneate labels: A, B, C, ...
    labels = [chr(65 + i) for i in range(n)]
    
    print("Distance Matrix:")
    # Print column headers
    print("      " + "  ".join(f"{l:>3}" for l in labels))
    print("    " + "-" * (5 * n + 2))
    
    for i, row in enumerate(matrix):
        # Format row values: 'INF' or number
        row_str = []
        for x in row:
            if x == float('inf'):
                row_str.append(f"{'INF':>3}")
            else:
                row_str.append(f"{x:>3}")
        
        # Print row label + values
        print(f"{labels[i]} |  " + "  ".join(row_str))
    print()

def main():
    print("--- TSP Project Part 1 Demo ---")
    
    # Parameters
    N = 10  # Number of cities
    PROB = 0.4 # Connection probability
    MAX_WEIGHT = 20 # Maximum weight for an edge
    
    print(f"Generating random graph with {N} vertices and probability {PROB}...")
    
    # Q1: Generate Random Graph
    g_random = Graph.generate_random_graph(N, PROB, MAX_WEIGHT)
    print(f"Random Graph generated.")
    
    # Visualize Q1 Graph
    print("Visualizing Random Graph (Check UI window)...")
    try:
        g_random.visualize("Q1: Random Generated Graph")
    except Exception as e:
        print(f"Visualization failed (missing libraries?): {e}")
    
    # Q2: Check Connectivity
    connected = g_random.is_connected()
    print(f"Is graph connected? {connected}")
    
    # Prepare Graph for Q3
    g_for_q3 = None
    
    if connected:
        print("Graph is connected. Using it for Q3.")
        g_for_q3 = g_random
    else:
        print("Graph is not connected (Q2 result: False).")
        print("Requirement for Q3: The graph must be connected.")
        print("Generatng a DEFAULT connected graph for Q3 (keeping Q1 graph as is)...")
        
        # Use declared fallback for Q3
        g_for_q3 = Graph.generate_connected_graph(N, PROB, MAX_WEIGHT)
        
        # Visualize Default Connected Graph
        print("Visualizing Default Connected Graph for Q3 (Check UI window)...")
        try:
             g_for_q3.visualize("Q3: Default Connected Graph")
        except Exception as e:
            print(f"Visualization failed: {e}")

    # Q3: Compute Distance Matrix
    print("Computing distance matrix (Q3)...")
    dist_matrix = g_for_q3.compute_distance_matrix()
    print_matrix(dist_matrix)

    # Part 2: Solve TSP
    print("\n--- TSP Part 2: Solving with Nearest Neighbor ---")
    print("Using a separate, fully connected (complete) graph 10x10 example with direct links:")
    
    # Hardcoded 10x10 Symmetric Distance Matrix
    example_distance_matrix = [
        [0, 29, 20, 21, 16, 31, 100, 12, 4, 31],
        [29, 0, 15, 29, 28, 40, 72, 21, 29, 41],
        [20, 15, 0, 15, 14, 25, 81, 9, 23, 27],
        [21, 29, 15, 0, 4, 12, 92, 12, 25, 13],
        [16, 28, 14, 4, 0, 16, 94, 9, 20, 16],
        [31, 40, 25, 12, 16, 0, 95, 24, 36, 3],
        [100, 72, 81, 92, 94, 95, 0, 90, 101, 99],
        [12, 21, 9, 12, 9, 24, 90, 0, 15, 25],
        [4, 29, 23, 25, 20, 36, 101, 15, 0, 35],
        [31, 41, 27, 13, 16, 3, 99, 25, 35, 0]
    ]
    
    print_matrix(example_distance_matrix)
    
    from tsp_algorithms import nearest_neighbor, brute_force_tsp, naive_dijkstra_tsp
    import time
    
    # 1. Nearest Neighbor
    print("1. Algo: Nearest Neighbor")
    start_time = time.perf_counter()
    nn_tour, nn_dist = nearest_neighbor(example_distance_matrix)
    end_time = time.perf_counter()
    nn_time_ms = (end_time - start_time) * 1000
    
    nn_labels = [chr(65 + i) for i in nn_tour]
    print(f"   Tour: {' -> '.join(nn_labels)}")
    print(f"   Dist: {nn_dist}")
    print(f"   Time: {nn_time_ms:.4f} ms")
    print(f"   Avg Time/City: {nn_time_ms/10:.4f} ms")

    # 2. Naive Dijkstra Strategy (The "Bad" one)
    print("\n2. Algo: Naive Dijkstra Strategy (Sort by distance from A)")
    start_time = time.perf_counter()
    dij_tour, dij_dist = naive_dijkstra_tsp(example_distance_matrix)
    end_time = time.perf_counter()
    dij_time_ms = (end_time - start_time) * 1000
    
    dij_labels = [chr(65 + i) for i in dij_tour]
    print(f"   Tour: {' -> '.join(dij_labels)}")
    print(f"   Dist: {dij_dist}")
    print(f"   Time: {dij_time_ms:.4f} ms")
    print(f"   Avg Time/City: {dij_time_ms/10:.4f} ms")

    # 3. Brute Force (Exact)
    print("\n3. Algo: Brute Force (Exact Solution)")
    start_time = time.perf_counter()
    bf_tour, bf_dist = brute_force_tsp(example_distance_matrix)
    end_time = time.perf_counter()
    bf_time_ms = (end_time - start_time) * 1000
    
    bf_labels = [chr(65 + i) for i in bf_tour]
    print(f"   Tour: {' -> '.join(bf_labels)}")
    print(f"   Dist: {bf_dist}")
    print(f"   Time: {bf_time_ms:.4f} ms")
    print(f"   Avg Time/City: {bf_time_ms/10:.4f} ms")
    
    print("\n--- Comparison ---")
    print(f"Nearest Neighbor Error vs Optimal: {nn_dist - bf_dist}")
    print(f"Naive Dijkstra Error vs Optimal:   {dij_dist - bf_dist}")
    
    run_complexity_analysis()

def generate_random_symmetric_matrix_local(n):
    import random
    matrix = [[0]*n for _ in range(n)]
    for i in range(n):
        for j in range(i+1, n):
            w = random.randint(10, 100)
            matrix[i][j] = w
            matrix[j][i] = w
    return matrix

def run_complexity_analysis():
    print("\n========================================")
    print("COMPLEXITY GROWTH ANALYSIS (Time vs N)")
    print("========================================")
    print(f"{'N':<5} | {'NN (ms)':<12} | {'Dijkstra (ms)':<15} | {'BruteForce (ms)':<15}")
    print("-" * 55)
    
    from tsp_algorithms import nearest_neighbor, brute_force_tsp, naive_dijkstra_tsp
    import time
    
    # We test sizes 3 to 10
    for n in range(3, 11):
        matrix = generate_random_symmetric_matrix_local(n)
        
        # NN
        start = time.perf_counter()
        nearest_neighbor(matrix)
        t_nn = (time.perf_counter() - start) * 1000
        
        # Dijkstra
        start = time.perf_counter()
        naive_dijkstra_tsp(matrix)
        t_dij = (time.perf_counter() - start) * 1000
        
        # Brute Force
        start = time.perf_counter()
        brute_force_tsp(matrix)
        t_bf = (time.perf_counter() - start) * 1000
        
        print(f"{n:<5} | {t_nn:<12.4f} | {t_dij:<15.4f} | {t_bf:<15.4f}")


if __name__ == "__main__":
    main()
