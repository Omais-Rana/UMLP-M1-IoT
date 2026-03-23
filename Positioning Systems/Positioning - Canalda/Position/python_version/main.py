from models import Emitter, Position, Cell
from grid_search import grid_search
from fingerprint import knn_fingerprint
from markov import MarkovService

def demo_grid_search():
    print("--- Grid Search / N-Lateration ---")
    # Define emitters and their measured distances to a generic target
    emitters = [
        Emitter(id="E1", position=Position(0.5, 0.5, 0.5), measured_distance=3.0),
        Emitter(id="E2", position=Position(4, 0, 0), measured_distance=2.0),
        Emitter(id="E3", position=Position(4, 5, 5), measured_distance=4.2)
    ]
    # Expect roughly somewhere around (3.0, 4.0, 0.0) from Pythagoras
    best_pos = grid_search(emitters, precision=0.1, max_coords=(5.0, 5.0, 5.0))
    print(f"Estimated Position: X={best_pos.x:.2f}, Y={best_pos.y:.2f}, Z={best_pos.z:.2f}")

def demo_fingerprinting():
    print("\n--- KNN Fingerprinting ---")
    # Using the 9 cells from the original 12x12m grid dataset
    # Emitters: 1, 2, 3, 4
    cells = [
        Cell(id="(0,0)", position=Position(2, 2, 0), rssi_signatures={"E1": -38, "E2": -27, "E3": -54, "E4": -13}),
        Cell(id="(0,1)", position=Position(6, 2, 0), rssi_signatures={"E1": -74, "E2": -62, "E3": -48, "E4": -33}),
        Cell(id="(0,2)", position=Position(10, 2, 0), rssi_signatures={"E1": -13, "E2": -28, "E3": -12, "E4": -40}),
        
        Cell(id="(1,0)", position=Position(2, 6, 0), rssi_signatures={"E1": -34, "E2": -27, "E3": -38, "E4": -41}),
        Cell(id="(1,1)", position=Position(6, 6, 0), rssi_signatures={"E1": -64, "E2": -48, "E3": -72, "E4": -35}),
        Cell(id="(1,2)", position=Position(10, 6, 0), rssi_signatures={"E1": -45, "E2": -37, "E3": -20, "E4": -15}),
        
        Cell(id="(2,0)", position=Position(2, 10, 0), rssi_signatures={"E1": -17, "E2": -50, "E3": -44, "E4": -33}),
        Cell(id="(2,1)", position=Position(6, 10, 0), rssi_signatures={"E1": -27, "E2": -28, "E3": -32, "E4": -45}),
        Cell(id="(2,2)", position=Position(10, 10, 0), rssi_signatures={"E1": -30, "E2": -20, "E3": -60, "E4": -40})
    ]
    
    # Measurements from an unknown target (Mobile Terminal)
    target_measurements = {"E1": -26, "E2": -42, "E3": -13, "E4": -46}
    
    pos = knn_fingerprint(cells, target_measurements, k=3)
    print(f"Estimated Target Position: X={pos.x:.2f}, Y={pos.y:.2f}, Z={pos.z:.2f}")

def demo_markov_chain():
    print("\n--- Markov Service (Movement Prediction) ---")
    # Represent the 6 grid cells (0 to 5)
    states = ["0", "1", "2", "3", "4", "5"]

    service = MarkovService(states=states)

    # Simulate a user walking through the rooms
    historical_path = [
        "0", "1", "2", "5", "4",
        "3", "0", "1", "4", "5", "2", "1"
    ]
    print("Transition Probability Matrix:")
    service.display_matrix()

    # We are currently at state '(1,1)'. Where to next?
    current_state = "(1,1)"
    next_states = service.get_next_probable_states(current_state)

    print(f"\nTrained on path length {len(historical_path)}")
    print(f"From state {current_state}, transition probabilities are:")
    for st, prob in next_states.items():
        print(f"  -> State {st} : {prob * 100:.1f} %")
        
    most_likely = service.predict_most_likely_next(current_state)
    print(f"\nMost likely next predicted state: {most_likely}")

if __name__ == "__main__":
    demo_grid_search()
    demo_fingerprinting()
    demo_markov_chain()
