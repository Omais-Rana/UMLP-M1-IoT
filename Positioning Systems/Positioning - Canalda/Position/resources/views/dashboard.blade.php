@extends('layouts.app')

@section('styles')
    <style>
        .welcome-header {
            margin-bottom: 40px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .exercise-card {
            border: 1px solid #eee;
            padding: 25px;
            border-radius: 12px;
            transition: 0.3s;
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
        }

        .exercise-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }

        .exercise-card h3 {
            color: var(--secondary);
            margin-top: 0;
        }

        .exercise-card p {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .badge-geo {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-stat {
            background: #fff3e0;
            color: #ef6c00;
        }

        .badge-markov {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .btn-open {
            margin-top: 20px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')
    <div class="welcome-header">
        <h1>Indoor Positioning Systems</h1>
        <p>UFR STGI - Master IoT. Explore the technical evolution of indoor tracking through three distinct methodologies.
        </p>
    </div>

    <div class="card-grid">
        <div class="exercise-card">
            <div>
                <span class="badge badge-geo">Geometric Approach</span>
                <h3>TD n°2: N-Lateration</h3>
                <p>
                    Triangulates coordinates by calculating the intersection of 3D spheres.
                    Focuses on geometric minimization of measurement residuals from multiple anchors.
                </p>
            </div>
            <a href="{{ route('lateration') }}" class="btn-open">Open Exercise</a>
        </div>

        <div class="exercise-card">
            <div>
                <span class="badge badge-stat">Probabilistic Approach</span>
                <h3>TD n°3: Fingerprinting</h3>
                <p>
                    Matches live signal "photography" against a pre-calibrated Radio Map.
                    Uses a weighted barycenter of the k-nearest cells to determine a precise location.
                </p>
            </div>
            <a href="{{ route('fingerprint') }}" class="btn-open">Open Exercise</a>
        </div>

        <div class="exercise-card">
            <div>
                <span class="badge badge-markov">Behavioral Approach</span>
                <h3>TD n°4: Markov Model</h3>
                <p>
                    Analyzes movement history to build a transition probability matrix.
                    Filters signal noise by predicting the most likely next state based on temporal dependencies.
                </p>
            </div>
            <a href="{{ route('markov') }}" class="btn-open">Open Exercise</a>
        </div>
    </div>
@endsection
