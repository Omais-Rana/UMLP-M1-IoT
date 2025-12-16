import random
import heapq
from collections import deque
import networkx as nx
import matplotlib.pyplot as plt

class Graph:
    def __init__(self, n_vertices):
        self.V = n_vertices
        # Adjacency list: adj[u] = [(v, weight), ...]
        self.adj = {i: [] for i in range(n_vertices)}

    def add_edge(self, u, v, w):
        self.adj[u].append((v, w))
        self.adj[v].append((u, w))

    # Q1: Define a function that can generate a random graph.
    @staticmethod
    def generate_random_graph(n, prob, max_weight=20):
        """
        Generates a random undirected weighted graph.
        n: Number of vertices
        prob: Probability of an edge existing between any two nodes
        max_weight: Maximum weight for an edge
        """
        g = Graph(n)
        for i in range(n):
            for j in range(i + 1, n):
                if random.random() < prob:
                    w = random.randint(1, max_weight)
                    g.add_edge(i, j, w)
        return g

    @staticmethod
    def generate_connected_graph(n, prob, max_weight=20):
        """
        Generates a GUARANTEED connected graph.
        Uses a Spanning Tree backbone + random edges.
        """
        g = Graph(n)
        # 1. Spanning Tree (Guarantees Connectivity)
        for i in range(1, n):
            j = random.randint(0, i - 1)
            w = random.randint(1, max_weight)
            g.add_edge(i, j, w)
            
        # 2. Add random edges
        for i in range(n):
            for j in range(i + 1, n):
                if random.random() < prob:
                    w = random.randint(1, max_weight)
                    g.add_edge(i, j, w)
        return g

    # Q2: Define a function that checks whether the graph of cities is connected.
    def is_connected(self):
        """
        Checks if the graph is connected using BFS.
        Returns True if connected, False otherwise.
        """
        if self.V == 0:
            return True
            
        visited = set()
        queue = deque([0])
        visited.add(0)
        
        while queue:
            u = queue.popleft()
            for v, w in self.adj[u]:
                if v not in visited:
                    visited.add(v)
                    queue.append(v)
                    
        return len(visited) == self.V

    # Q3: Calculate all the lengths of the shortest paths between all the cities.
    def compute_distance_matrix(self):
        """
        Computes the all-pairs shortest path distance matrix using Floyd-Warshall Algorithm.
        Time Complexity: O(V^3)
        Returns a 2D list where matrix[i][j] is the distance from i to j.
        """
        # 1. Initialize distance matrix with Infinity
        dist = [[float('inf')] * self.V for _ in range(self.V)]
        
        # 2. Set distance to self to 0
        for i in range(self.V):
            dist[i][i] = 0
            
        # 3. Initialize with edge weights
        for u in range(self.V):
            for v, w in self.adj[u]:
                # In case of multiple edges, take the minimum (though simple graph assumption usually applies)
                if w < dist[u][v]:
                    dist[u][v] = w

        # 4. Floyd-Warshall Algorithm
        for k in range(self.V):
            for i in range(self.V):
                for j in range(self.V):
                    if dist[i][j] > dist[i][k] + dist[k][j]:
                        dist[i][j] = dist[i][k] + dist[k][j]
                        
        return dist
    
    def visualize(self, title="Graph Visualization"):
        """
        Visualizes the graph using networkx and matplotlib.
        """
        G = nx.Graph()
        labels_map = {i: chr(65 + i) for i in range(self.V)} # A, B, C...
        
        for u in self.adj:
            G.add_node(u, label=labels_map[u])
            for v, w in self.adj[u]:
                if u < v: # Add each edge once
                    G.add_edge(u, v, weight=w)
        
        pos = nx.spring_layout(G)
        edge_labels = nx.get_edge_attributes(G, 'weight')
        
        plt.figure(title)
        plt.title(title)
        
        # Draw nodes
        nx.draw_networkx_nodes(G, pos, node_color='lightblue', node_size=500)
        # Draw labels
        nx.draw_networkx_labels(G, pos, labels=labels_map)
        # Draw edges
        nx.draw_networkx_edges(G, pos)
        # Draw edge weights
        nx.draw_networkx_edge_labels(G, pos, edge_labels=edge_labels)
        
        plt.show()
