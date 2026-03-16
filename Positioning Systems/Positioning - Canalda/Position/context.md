# Indoor Positioning System: Technical Documentation

**Project Scope:** Implementation of UFR STGI Master IoT Course - TD2, TD3, and TD4.
**Environment:** Laravel 11 / PHP 8.2

## 1. TD n°2: N-Lateration (Geometric Approach)

### Concept

Locates a Mobile Terminal (MT) by finding the intersection of four 3D spheres centered at fixed anchors ($E_0...E_3$).

### Mathematical Logic

- **Input:** Coordinates $(x, y, z)$ for 4 emitters and their measured distances $d_i$.
- **Error Minimization:** Because of signal noise, we use a **Grid Search SAE (Sum of Absolute Errors)**.
- **Cost Function:** $f(P) = \sum | \text{dist}(P, E_i) - d_i |$
- **Implementation:** `App\Managers\NLaterationManager::solveStaticScenario()` iterates through a $0.1$m grid in a $6 \times 6 \times 6$ space to find the point $P$ that minimizes the cost function.

## 2. TD n°3: Fingerprinting (Probabilistic Approach)

### Concept

Uses "Environment Photography" (Radio Map) to match live RSSI scans against a pre-calibrated grid.

### Grid Configuration

- **Area:** $12$m $\times$ $12$m.
- **Layout:** $3 \times 3$ grid of $4$m $\times$ $4$m cells.
- **Reference Points:** Measured at centers $(2, 6, 10)$ for both X and Y axes.

### Algorithm: Weighted K-Nearest Neighbors (WKNN)

1. **Signal Distance:** Euclidean distance in RSSI space: $\sqrt{\sum (RSSI_{obs} - RSSI_{db})^2}$.
2. **K-Neighboring:** Selects $k=4$ cells with best minimization of RSSI differences.
3. **Barycentric Ponderation:** - Weighting coefficients $c_i$ are derived from distance ratios: if $d_1 \cdot \alpha = d_2 \implies c_2 = (1/\alpha) \cdot c_1$.
    - Constraint: $\sum c_i = 1$.

- **Final Result:** Vector sum $OM = c_1 \cdot OK_1 + ... + c_k \cdot OK_k$ where $OK_i$ are reference point coordinates.

## 3. TD n°4: Markov Model (Behavioral Approach)

### Concept

A first-order Hidden Markov Model (HMM) that adds temporal dependency to the positioning logic.

### State Transition Logic (Appendix Logic)

- **States:** The 9 grid cells from TD3.
- **Transition Matrix ($MM$):** Tracks the probability of moving from $S_{prev}$ to $S_{curr}$.
- **Learning Rule:**
    - $MM[prev][curr].nb += 1$
    - $MM[prev][\text{Totalizer}].nb += 1$
- **Probability Rule:**
    - $Stat = \frac{MM[prev][curr].nb}{\text{Row Total}}$

### Positioning Refinement

The estimated position is the product of the **Emission Probability** (Signal matching from TD3) and the **Transition Probability** (Physical likelihood of the move based on the learned matrix).

## 4. Project File Structure for AI Context

- `App\Models\Cellule`: Holds RSSI vector and $(x, y)$ center.
- `App\Services\FingerprintService`: Implements WKNN and Barycentric math.
- `App\Services\MarkovService`: Manages the transition matrix and probabilistic state estimation.
- `App\Http\Controllers\PositioningController`: Manages session-based state and coordinates the three methodologies.
