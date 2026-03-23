from flask import Flask, render_template, request, jsonify
from models import Emitter, Position, Cell
from grid_search import grid_search
from fingerprint import knn_fingerprint
from markov import MarkovService

app = Flask(__name__)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/grid_search', methods=['POST'])
def run_grid_search():
    data = request.json
    emitters_data = data.get('emitters', [])
    precision = float(data.get('precision', 0.1))
    
    emitters = []
    max_x, max_y, max_z = 0.0, 0.0, 0.0
    for e in emitters_data:
        x, y, z = float(e['x']), float(e['y']), float(e.get('z', 0.0))
        max_x = max(max_x, x)
        max_y = max(max_y, y)
        max_z = max(max_z, z)
        emitters.append(Emitter(
            id=e['id'], 
            position=Position(x=x, y=y, z=z),
            measured_distance=float(e['distance'])
        ))
    
    # We add 2 to the bounds to ensure grid search expands beyond the absolute bounds of the emitters slightly
    best_pos = grid_search(emitters, precision=precision, max_coords=(max_x+2.0, max_y+2.0, max_z+2.0))
    
    return jsonify({
        "x": round(best_pos.x, 3), 
        "y": round(best_pos.y, 3), 
        "z": round(best_pos.z, 3)
    })

@app.route('/api/fingerprint', methods=['POST'])
def run_fingerprint():
    data = request.json
    target_measurements = data.get('target', {"E1": -26, "E2": -42, "E3": -13, "E4": -46})
    target_measurements = {k: float(v) for k, v in target_measurements.items()}
    k_val = int(data.get('k', 3))
    
    # Optional dynamic cells
    custom_cells = data.get('cells', None)
    cells = []
    if custom_cells:
        for c in custom_cells:
            coords = c['coords'].split(',')
            x, y = float(coords[0]), float(coords[1])
            z = float(coords[2]) if len(coords) > 2 else 0.0
            cells.append(Cell(
                id=c['id'],
                position=Position(x, y, z),
                rssi_signatures={k: float(v) for k, v in c['rssi'].items()}
            ))
    else:
        # Defaults
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
    
    pos = knn_fingerprint(cells, target_measurements, k=k_val)
    return jsonify({"x": round(pos.x, 3), "y": round(pos.y, 3), "z": round(pos.z, 3)})

@app.route('/api/markov', methods=['POST'])
def run_markov():
    data = request.json
    history_str = data.get('history', "")
    current_state = data.get('current_state', "").strip()
    
    # Parse history
    historical_path = [s.strip() for s in history_str.split(',') if s.strip()]
    
    states = list(dict.fromkeys(historical_path + ([current_state] if current_state else [])))
    
    if not states or (len(historical_path) < 2 and not current_state):
        return jsonify({"error": "Insufficient history data to build Markov Chain"})
        
    service = MarkovService(states=states)
    service.train(historical_path)
    
    next_states = service.get_next_probable_states(current_state)
    most_likely = service.predict_most_likely_next(current_state)
    
    prev_states = service.get_prev_probable_states(current_state)
    most_likely_prev = service.predict_most_likely_prev(current_state)

    # Pack full transition matrix for display
    matrix_data = {
        "headers": states,
        "rows": service.matrix,
        "rows_backward": service.matrix_backward
    }

    return jsonify({
        "matrix": matrix_data,
        "probabilities": next_states,
        "most_likely": most_likely,
        "prev_probabilities": prev_states,
        "most_likely_prev": most_likely_prev
    })

if __name__ == '__main__':
    app.run(debug=True, port=8080)
