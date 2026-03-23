class MarkovService:
    def __init__(self, states: list):
        """
        Initialize the Markov chain with a list of state identifiers (e.g., cell names, IDs, coordinates).
        """
        self.states = states
        self.num_states = len(states)
        self.state_to_index = {state: i for i, state in enumerate(states)}
        self.index_to_state = {i: state for i, state in enumerate(states)}
        
        # Initialize an empty square matrix with zeroes
        self.matrix = [[0.0 for _ in range(self.num_states)] for _ in range(self.num_states)]
        self.matrix_backward = [[0.0 for _ in range(self.num_states)] for _ in range(self.num_states)]

    def train(self, historical_sequence: list):
        """
        Build transition probabilities from a sequence of visited states.
        """
        if len(historical_sequence) < 2:
            return
            
        counts = [[0 for _ in range(self.num_states)] for _ in range(self.num_states)]
        
        # Count identical transitions
        for i in range(len(historical_sequence) - 1):
            prev_state = self.state_to_index.get(historical_sequence[i])
            next_state = self.state_to_index.get(historical_sequence[i+1])
            
            if prev_state is not None and next_state is not None:
                counts[prev_state][next_state] += 1
            
        # Normalize into probabilities (Forward)
        for i in range(self.num_states):
            total_transitions_from_i = sum(counts[i])
            if total_transitions_from_i > 0:
                for j in range(self.num_states):
                    self.matrix[i][j] = counts[i][j] / total_transitions_from_i

        # Normalize into probabilities (Backward)
        for j in range(self.num_states):
            total_transitions_into_j = sum(counts[i][j] for i in range(self.num_states))
            if total_transitions_into_j > 0:
                for i in range(self.num_states):
                    # matrix_backward[j][i] is P(came from i | currently at j)
                    self.matrix_backward[j][i] = counts[i][j] / total_transitions_into_j

    def get_next_probable_states(self, current_state) -> dict:
        """
        Returns all non-zero probabilities for the next likely state(s), sorted by highest probability.
        """
        idx = self.state_to_index.get(current_state)
        if idx is None:
            return {}
            
        probabilities = self.matrix[idx]
        
        state_probs = {
            self.index_to_state[j]: prob 
            for j, prob in enumerate(probabilities) if prob > 0
        }
        
        # Sort by probability descending
        return dict(sorted(state_probs.items(), key=lambda item: item[1], reverse=True))

    def get_prev_probable_states(self, current_state) -> dict:
        """
        Returns all non-zero probabilities for the likely previous state(s), sorted by highest probability.
        """
        idx = self.state_to_index.get(current_state)
        if idx is None:
            return {}

        probabilities = self.matrix_backward[idx]

        state_probs = {
            self.index_to_state[i]: prob
            for i, prob in enumerate(probabilities) if prob > 0
        }

        # Sort by probability descending
        return dict(sorted(state_probs.items(), key=lambda item: item[1], reverse=True))

    def predict_most_likely_next(self, current_state):
        """
        Helper method to return just the single most likely next state.
        """
        next_states = self.get_next_probable_states(current_state)
        if not next_states:
            return None
        return list(next_states.keys())[0]
        
    def predict_most_likely_prev(self, current_state):
        """
        Helper method to return just the single most likely previous state.
        """
        prev_states = self.get_prev_probable_states(current_state)
        if not prev_states:
            return None
        return list(prev_states.keys())[0]

    def display_matrix(self):
        """Print the Markov Chain visually."""
        # Print header using standard ASCII characters for better console compatibility
        header = "States\t| " + "\t".join(str(s) for s in self.states)
        print(header)
        print("-" * len(header.expandtabs(8)))
        
        for i, row in enumerate(self.matrix):
            state_name = str(self.states[i])
            row_str = "\t".join([f"{val:.2f}" for val in row])
            print(f"{state_name}\t| {row_str}")
