from dataclasses import dataclass

@dataclass
class Position:
    x: float
    y: float
    z: float = 0.0

@dataclass
class Emitter:
    id: str
    position: Position
    measured_distance: float = 0.0  # Used in Grid Search
    measured_rssi: float = 0.0      # Used in Fingerprinting

@dataclass
class Cell:
    id: str
    position: Position
    rssi_signatures: dict[str, float]  # signature dictionary mapping emitter_id to expected RSSI
