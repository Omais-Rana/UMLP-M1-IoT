#include <iostream>
#include <vector>
#include <fstream>
#include <string>
#include <sstream>
#include <algorithm>
#include <iomanip>
#include <stack>
#include <map>

using namespace std;

const int INF = 1e9;

/**
 * @brief Loads an n x n matrix from a text file.
 * Assumes the first line is the size 'n', which we skip
 * as 'n' is passed as an argument.
 */
vector<vector<int>> loadMatrix(const string &filename, int n)
{
    vector<vector<int>> matrix(n, vector<int>(n));
    ifstream file(filename);
    if (!file.is_open())
    {
        cerr << "Error: Could not open file " << filename << endl;
        exit(1);
    }

    int size_from_file;
    file >> size_from_file; // Read and discard the '6'

    for (int i = 0; i < n; ++i)
    {
        for (int j = 0; j < n; ++j)
        {
            file >> matrix[i][j];
        }
    }
    file.close();
    return matrix;
}

/**
 * @brief Prints a 2D matrix with nice formatting.
 */
void printMatrix(const vector<vector<int>> &matrix, const string &title, int width = 5)
{
    cout << "--- " << title << " ---" << endl;
    for (const auto &row : matrix)
    {
        for (int val : row)
        {
            if (val == INF)
            {
                cout << setw(width) << "INF";
            }
            else
            {
                cout << setw(width) << val;
            }
        }
        cout << endl;
    }
    cout << "-----------------------------------" << endl
         << endl;
}

// TASK 1: FLOYD-WARSHALL ALGORITHM

void task1_floyd_warshall(const vector<vector<int>> &costMatrix, int n)
{
    vector<vector<int>> dist = costMatrix;

    // 1. Initialize distance matrix
    for (int i = 0; i < n; ++i)
    {
        for (int j = 0; j < n; ++j)
        {
            if (i == j)
            {
                dist[i][j] = 0;
            }
            else if (dist[i][j] == 0)
            {
                dist[i][j] = INF;
            }
        }
    }

    // 2. Run Floyd-Warshall
    for (int k = 0; k < n; ++k)
    {
        for (int i = 0; i < n; ++i)
        {
            for (int j = 0; j < n; ++j)
            {
                if (dist[i][k] != INF && dist[k][j] != INF)
                {
                    dist[i][j] = min(dist[i][j], dist[i][k] + dist[k][j]);
                }
            }
        }
    }

    printMatrix(dist, "Task 1: All-Pairs Shortest Path (Floyd-Warshall)");
}

// TASK 2: KRUSKAL'S ALGORITHM (MST)

// Edge structure for Kruskal's
struct Edge
{
    int src, dest, weight;
};

// DSU (Disjoint Set Union) structure for cycle detection
struct DSU
{
    vector<int> parent;
    DSU(int n)
    {
        parent.resize(n);
        for (int i = 0; i < n; ++i)
        {
            parent[i] = i;
        }
    }

    // Find representative of set i (with path compression)
    int find(int i)
    {
        if (parent[i] == i)
            return i;
        return parent[i] = find(parent[i]);
    }

    // Unite two sets
    void unite(int i, int j)
    {
        int root_i = find(i);
        int root_j = find(j);
        if (root_i != root_j)
        {
            parent[root_i] = root_j;
        }
    }
};

// Comparison function for sorting edges
bool compareEdges(const Edge &a, const Edge &b)
{
    return a.weight < b.weight;
}

void task2_kruskal(const vector<vector<int>> &installMatrix, int n)
{
    vector<Edge> edges;

    // 1. Extract all unique edges from the (symmetric) matrix
    for (int i = 0; i < n; ++i)
    {
        for (int j = i + 1; j < n; ++j)
        {
            edges.push_back({i, j, installMatrix[i][j]});
        }
    }

    // 2. Sort edges by weight
    sort(edges.begin(), edges.end(), compareEdges);

    DSU dsu(n);
    vector<Edge> mst;
    int totalCost = 0;

    // 3. Iterate through sorted edges
    for (const auto &edge : edges)
    {
        if (dsu.find(edge.src) != dsu.find(edge.dest))
        {
            mst.push_back(edge);
            totalCost += edge.weight;
            dsu.unite(edge.src, edge.dest);
        }
    }

    // 6. Print results
    cout << "--- Task 2: Minimum Spanning Tree (Kruskal's) ---" << endl;
    cout << "Total Minimum Installation Cost: " << totalCost << endl;
    cout << "Edges to install:" << endl;
    for (const auto &edge : mst)
    {
        cout << "  (" << edge.src << ", " << edge.dest << ") with cost " << edge.weight << endl;
    }
    cout << "--------------------------------------------------" << endl
         << endl;
}

// TASK 3: TARJAN'S ALGORITHM (SCCs)

// State variables for Tarjan's (passed by reference)
int tarjan_time;
stack<int> scc_stack;
vector<int> disc, low;
vector<bool> onStack;

// The resulting list of SCCs
vector<vector<int>> sccList;
// Map to store which SCC each node belongs to (for Task 4)
map<int, int> nodeToSCC;

/**
 * @brief A recursive DFS helper for Tarjan's algorithm.
 */
void dfs_scc(int u, const vector<vector<int>> &adjMatrix)
{
    disc[u] = low[u] = ++tarjan_time;
    scc_stack.push(u);
    onStack[u] = true;

    // Visit all neighbors 'v'
    for (int v = 0; v < adjMatrix.size(); ++v)
    {
        if (adjMatrix[u][v] == 1)
        {
            if (disc[v] == -1)
            {
                dfs_scc(v, adjMatrix);
                low[u] = min(low[u], low[v]);
            }
            else if (onStack[v])
            {
                low[u] = min(low[u], disc[v]);
            }
        }
    }

    // Check if 'u' is the root of an SCC
    if (low[u] == disc[u])
    {
        vector<int> currentSCC;
        int scc_id = sccList.size();

        while (scc_stack.top() != u)
        {
            int v = scc_stack.top();
            scc_stack.pop();
            onStack[v] = false;
            currentSCC.push_back(v);
            nodeToSCC[v] = scc_id;
        }
        // Pop 'u' itself
        int v = scc_stack.top();
        scc_stack.pop();
        onStack[v] = false;
        currentSCC.push_back(v);
        nodeToSCC[v] = scc_id;

        sccList.push_back(currentSCC);
    }
}

/**
 * @brief Main function to run Tarjan's SCC algorithm.
 * Returns the mapping of node to SCC ID for Task 4.
 */
map<int, int> task3_tarjan_scc(const vector<vector<int>> &adjMatrix, int n)
{
    // Clear global state
    tarjan_time = 0;
    disc.assign(n, -1);
    low.assign(n, -1);
    onStack.assign(n, false);
    while (!scc_stack.empty())
        scc_stack.pop();
    sccList.clear();
    nodeToSCC.clear();

    // Run DFS
    for (int i = 0; i < n; ++i)
    {
        if (disc[i] == -1)
        {
            dfs_scc(i, adjMatrix);
        }
    }

    // Print results
    cout << "--- Task 3: Clusters (Tarjan's SCCs) ---" << endl;
    cout << "Found " << sccList.size() << " clusters:" << endl;
    for (size_t i = 0; i < sccList.size(); ++i)
    {
        cout << "  Cluster " << i << " (X" << i << "): { ";
        for (int node : sccList[i])
        {
            cout << node << " ";
        }
        cout << "}" << endl;
    }
    cout << "------------------------------------------" << endl
         << endl;

    return nodeToSCC;
}

// TASK 4: CONDENSATION GRAPH (MATRIX N)

void task4_matrix_N(const vector<vector<int>> &adjMatrix, int n,
                    const vector<vector<int>> &sccs, const map<int, int> &nodeMap)
{

    int numClusters = sccs.size();
    if (numClusters == 0)
    {
        cout << "--- Task 4: Matrix N ---" << endl;
        cout << "No clusters found, cannot build Matrix N." << endl;
        cout << "------------------------" << endl
             << endl;
        return;
    }

    // 1. Initialize Matrix N to all zeros
    vector<vector<int>> matrixN(numClusters, vector<int>(numClusters, 0));

    // 2. Iterate through all original edges (i, j)
    for (int i = 0; i < n; ++i)
    {
        for (int j = 0; j < n; ++j)
        {
            if (adjMatrix[i][j] == 1)
            {
                // 3. Find which cluster i and j belong to
                int cluster_i = nodeMap.at(i);
                int cluster_j = nodeMap.at(j);

                // 4. If it's an inter-cluster edge, increment count
                if (cluster_i != cluster_j)
                {
                    matrixN[cluster_i][cluster_j]++;
                }
            }
        }
    }

    printMatrix(matrixN, "Task 4: Cluster Adjacency Matrix N", 3);
}

// MAIN FUNCTION

int main()
{
    const int n = 6; // Number of data centers (0 to 5)

    vector<vector<int>> adjMatrix = loadMatrix("mat3.txt", n);
    vector<vector<int>> costMatrix = loadMatrix("cost3.txt", n);
    vector<vector<int>> installMatrix = loadMatrix("instal_cost3.txt", n);

    // --- Task 1 ---
    task1_floyd_warshall(costMatrix, n);

    // --- Task 2 ---
    task2_kruskal(installMatrix, n);

    // --- Task 3 ---
    // We need to capture the results from Tarjan's for Task 4
    map<int, int> nodeToClusterMap = task3_tarjan_scc(adjMatrix, n);

    // --- Task 4 ---
    // 'sccList' is a global modified by task3, passed here for its size
    task4_matrix_N(adjMatrix, n, sccList, nodeToClusterMap);

    return 0;
}