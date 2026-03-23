import math
from models import Cell, Emitter, Position

def calculate_rssi_distance(cell: Cell, target_measurements: dict[str, float]) -> float:
    # Calculates the Euclidean distance in signal space.
    distance_squared = 0.0
    for emitter_id, rssi in target_measurements.items():
        cell_rssi = cell.rssi_signatures.get(emitter_id, 0.0)
        distance_squared += (cell_rssi - rssi) ** 2
    return math.sqrt(distance_squared)

def knn_fingerprint(
    cells: list[Cell], 
    target_measurements: dict[str, float], 
    k: int = 3
) -> Position:
    """
    K-Nearest Neighbors Algorithm based on signal strength fingerprinting.
    Uses inverse distance weighting to find a physical barycenter.
    """
    if not cells:
        return Position(0, 0, 0)
        
    distances = []
    for cell in cells:
        dist = calculate_rssi_distance(cell, target_measurements)
        distances.append((dist, cell))
        
    distances.sort(key=lambda x: x[0])
    
    # K Nearest
    neighbors = distances[:k]
    
    # If the closest neighbor is practically identical (distance ~0), return its position
    if neighbors[0][0] < 1e-6:
        return neighbors[0][1].position
        
    closest_dist = neighbors[0][0]
    weights = []
    total_w = 0.0
    
    # Calculate weights based on the ratio: d1 / di
    for dist, cell in neighbors:
        alpha = dist / closest_dist
        w = 1.0 / alpha  # this simplifies back to closest_dist / dist
        weights.append(w)
        total_w += w
        
    # Calculate the weighted barycenter (final position)
    final_x = 0.0
    final_y = 0.0
    final_z = 0.0
    
    for i, (dist, cell) in enumerate(neighbors):
        normalized_weight = weights[i] / total_w
        final_x += normalized_weight * cell.position.x
        final_y += normalized_weight * cell.position.y
        final_z += normalized_weight * cell.position.z
        
    return Position(final_x, final_y, final_z)
