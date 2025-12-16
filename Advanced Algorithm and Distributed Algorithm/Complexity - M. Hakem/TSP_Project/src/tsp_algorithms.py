import itertools

def calculate_tour_distance(tour, distance_matrix):
    """Calcs total distance of a tour."""
    dist = 0
    n = len(tour)
    for i in range(n):
        u, v = tour[i], tour[(i + 1) % n]
        dist += distance_matrix[u][v]
    return dist

def nearest_neighbor(distance):
    n = len(distance)
    visited = [False] * n 
    tour = []
    
    current = 0 
    visited[current] = True
    tour.append(current) 
    
    total_distance = 0
    
    for _ in range(n - 1):
        nearest = None
        min_dist = float('inf')
        
        for city in range(n):
            if not visited[city] and distance [current][city] < min_dist:
                min_dist = distance[current][city]
                nearest = city
        
        visited[nearest] = True
        tour.append(nearest)
        total_distance += min_dist
        current = nearest
        
    total_distance += distance[current][tour[0]]
    tour.append(tour[0])
    
    # We return tour without the duplicated last element for consistency with other algos usually,
    # but the user's NN code appended it. Let's keep consistency.
    # Actually, 2-opt and Brute Force usually work with a list of unique cities.
    # Let's standardize: Return list of N cities (0..N-1) implies cycle back to start.
    # But user's NN appended the start at the end. I will fix NN to remove the duplicate for processing 
    # OR adapt others to match.
    # Let's adapt others to return the same format: [0, 1, 2, 0] for A->B->C->A
    
    return tour[:-1] if tour[-1] == tour[0] else tour, total_distance

def brute_force_tsp(distance):
    """
    Tries ALL permutations `(N-1)!`. Guaranteed optimal.
    VERY SLOW for N > 10.
    """
    n = len(distance)
    cities = list(range(n))
    shortest_tour = []
    min_dist = float('inf')
    
    # Fix start city at 0 to reduce permutations by factor of N (since cycles are rotation invariant)
    for p in itertools.permutations(cities[1:]):
        current_tour = [0] + list(p)
        d = calculate_tour_distance(current_tour, distance)
        
        if d < min_dist:
            min_dist = d
            shortest_tour = current_tour
            
    return shortest_tour, min_dist

def naive_dijkstra_tsp(distance_matrix):
    """
    "Naive Dijkstra" Strategy:
    1. Run Dijkstra (or look up distances) from Start City (0) to all others.
    2. Sort all cities based on how close they are to City 0.
    3. Visit them in that sorted order.
    
    Why it is 'bad': It ignores the distance between neighbors!
    If B and C are both 10km from A, but 100km from each other,
    this path goes A -> B -> C (traveling that 100km!)
    """
    n = len(distance_matrix)
    start_node = 0
    
    # Get all nodes except start
    nodes = list(range(1, n))
    
    # Sort nodes by distance from start_node
    # In a complete graph, Dijkstra distance is just the edge weight
    nodes.sort(key=lambda x: distance_matrix[start_node][x])
    
    # Construct tour: Start -> Closest to Start -> 2nd Closest -> ... -> Start
    tour = [start_node] + nodes
    
    return tour, calculate_tour_distance(tour, distance_matrix)


