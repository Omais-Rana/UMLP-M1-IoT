import math
from models import Emitter, Position

def calculate_distance(p1: Position, p2: Position) -> float:
    return math.sqrt((p1.x - p2.x)**2 + (p1.y - p2.y)**2 + (p1.z - p2.z)**2)

def grid_search(
    emitters: list[Emitter], 
    precision: float = 0.1, 
    max_coords: tuple[float, float, float] = (5.0, 5.0, 5.0)
) -> Position:
    """
    Exhaustive 3D Grid Search algorithm for N-Lateration.
    Finds the (x,y,z) coordinate that minimizes the sum of absolute errors 
    between calculated and measured distances to emitters.
    """
    min_error = float('inf')
    best_pos = Position(0, 0, 0)
    
    # Iterate through the 3D grid with the given precision
    x = 0.0
    while x <= max_coords[0]:
        y = 0.0
        while y <= max_coords[1]:
            z = 0.0
            while z <= max_coords[2]:
                current_pos = Position(x, y, z)
                current_error = 0.0
                
                # Calculate the total error for this grid point
                for emitter in emitters:
                    dist = calculate_distance(current_pos, emitter.position)
                    current_error += abs(dist - emitter.measured_distance)
                
                # Update best position if this grid point has lower error
                if current_error < min_error:
                    min_error = current_error
                    best_pos = current_pos
                    
                z += precision
            y += precision
        x += precision
        
    return best_pos
