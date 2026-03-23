@extends('layouts.app')

@section('styles')
    <style>
        .container {
            max-width: 100% !important;
            padding: 20px 40px !important;
        }

        .card {
            max-width: 1600px !important;
            width: 100% !important;
        }

        /* Make it stack on laptops */
        .main-wrapper {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            flex-direction: column;
            /* Force vertical stacking on small screens */
        }

        @media (min-width: 1200px) {
            .main-wrapper {
                flex-direction: column;
                /* Changed to always stack to make table visible */
            }
        }
    </style>
@endsection

@section('content')
    <h1>TD n°4: Dynamic Markov Model</h1>
    <p>Click the cells below to simulate movement and update the <b>Transition Matrix</b> in real-time.</p>

    <div class="main-wrapper">
        <!-- Top Column -->
        <div style="width: 100%;">

            <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 30px;">
                <!-- Left side: The Grid -->
                <div>
                    <div style="display: grid; grid-template-columns: repeat(3, 100px); gap: 10px;">
                        @for ($i = 0; $i < 6; $i++)
                            @php
                                $isPredicted =
                                    isset($predictedCells) && in_array($i, $predictedCells) && $maxProbability > 0;
                                $isBackward =
                                    isset($predictedPrevCells) &&
                                    in_array($i, $predictedPrevCells) &&
                                    $maxBackwardProbability > 0;

                                $cellClass = '';
                                if ($isPredicted) {
                                    $cellClass = 'glow-prediction';
                                } elseif ($isBackward) {
                                    $cellClass = 'glow-backward';
                                }

                                $bgColor = '#fff';
                                if (session('last_cell_id') === $i) {
                                    $bgColor = '#d63031';
                                } elseif ($isPredicted) {
                                    $bgColor = '#fff9e6';
                                } elseif ($isBackward) {
                                    $bgColor = '#f4ebf9';
                                }
                            @endphp
                            <a href="?move_to={{ $i }}" class="{{ $cellClass }}"
                                style="width:100px; height:100px; border:2px solid #333; display:flex; align-items:center; justify-content:center; text-decoration:none; font-weight:bold; background: {{ $bgColor }}; color: {{ session('last_cell_id') === $i ? '#fff' : '#000' }}; transition: all 0.3s; transform: {{ $isPredicted || $isBackward ? 'scale(1.05)' : 'none' }};">
                                Cell {{ $i }}
                            </a>
                        @endfor
                    </div>
                </div>

                <!-- Right side: The History -->
                <div
                    style="width: 250px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; max-height: 220px; overflow-y: auto;">
                    <h4 style="margin-top: 0; font-size: 1.1em; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                        Movement
                        History
                    </h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                        @if (empty($history))
                            <span style="color: #888; font-style: italic;">No moves yet.</span>
                        @else
                            @foreach ($history as $index => $cellId)
                                <span
                                    style="background: #0984e3; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.9em;">
                                    {{ $index == 0 ? 'Start' : '->' }} C{{ $cellId }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <style>
                .matrix-table-container {
                    margin: 30px auto;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                    background: #fff;
                }

                .matrix-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0;
                    font-size: 0.95em;
                }

                .matrix-table thead tr {
                    background-color: #2d3436;
                    color: #ffffff;
                    text-align: center;
                }

                .matrix-table th {
                    padding: 15px;
                    text-transform: uppercase;
                    font-size: 0.85em;
                    letter-spacing: 0.5px;
                    border: 1px solid #3b4245;
                }

                .matrix-table td {
                    padding: 12px 15px;
                    text-align: center;
                    border: 1px solid #f1f2f6;
                }

                .matrix-table tbody tr {
                    border-bottom: 1px solid #f1f2f6;
                }

                .matrix-table tbody tr:hover {
                    background-color: #fdfdfd;
                }

                .matrix-table td.header-col {
                    background-color: #f8f9fa;
                    font-weight: bold;
                    color: #2d3436;
                    border-right: 2px solid #e0e6ed;
                }

                .matrix-table td.total-col {
                    background-color: #fff4f4;
                    font-weight: bold;
                    color: #d63031;
                    border-left: 2px solid #e0e6ed;
                }

                .stat-cell {
                    transition: all 0.3s ease;
                    font-weight: 600;
                }

                @keyframes predictionPulse {
                    0% {
                        box-shadow: 0 0 5px #f1c40f, inset 0 0 5px #f1c40f;
                        border-color: #f39c12;
                    }

                    100% {
                        box-shadow: 0 0 20px #f39c12, inset 0 0 10px #f39c12;
                        border-color: #e67e22;
                    }
                }

                @keyframes predictionTextPulse {
                    0% {
                        box-shadow: 0 0 5px rgba(243, 156, 18, 0.4);
                    }

                    100% {
                        box-shadow: 0 0 15px rgba(243, 156, 18, 0.8);
                    }
                }

                .glow-prediction {
                    animation: predictionPulse 1.5s infinite alternate !important;
                    border: 3px solid #f39c12 !important;
                    z-index: 10;
                    position: relative;
                }

                @keyframes backwardPulse {
                    0% {
                        box-shadow: 0 0 5px #9b59b6, inset 0 0 5px #9b59b6;
                        border-color: #8e44ad;
                    }

                    100% {
                        box-shadow: 0 0 20px #8e44ad, inset 0 0 10px #8e44ad;
                        border-color: #9b59b6;
                    }
                }

                .glow-backward {
                    animation: backwardPulse 1.5s infinite alternate !important;
                    border: 3px solid #8e44ad !important;
                    z-index: 9;
                    position: relative;
                }
            </style>

            <div class="matrix-table-container">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th>From \ To</th>
                            @for ($i = 0; $i < 6; $i++)
                                <th>C{{ $i }}</th>
                            @endfor
                            <th>Total (nb)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Calculate column totals for Backward percentage matches
                            $colTotals = array_fill(0, 6, 0);
                            foreach ($matrix as $r) {
                                for ($c = 0; $c < 6; $c++) {
                                    $colTotals[$c] += $r[$c]->nb;
                                }
                            }
                        @endphp
                        @foreach ($matrix as $rowIdx => $row)
                            @php
                                $isCurrentRow = session('last_cell_id') === $rowIdx;
                            @endphp
                            <tr>
                                <td class="header-col">C{{ $rowIdx }}</td>
                                @foreach (array_slice($row, 0, 6) as $cellIdx => $cell)
                                    @php
                                        $isPredictedCell =
                                            $isCurrentRow &&
                                            isset($predictedCells) &&
                                            in_array($cellIdx, $predictedCells) &&
                                            $maxProbability > 0;

                                        $isBackwardCell =
                                            isset($predictedPrevCells) &&
                                            in_array($rowIdx, $predictedPrevCells) &&
                                            $cellIdx === session('last_cell_id') &&
                                            $maxBackwardProbability > 0;

                                        $glowClass = '';
                                        if ($isPredictedCell) {
                                            $glowClass = 'glow-prediction';
                                        } elseif ($isBackwardCell) {
                                            $glowClass = 'glow-backward';
                                        }

                                        $backwardPerc =
                                            $colTotals[$cellIdx] > 0 ? ($cell->nb / $colTotals[$cellIdx]) * 100 : 0;
                                    @endphp
                                    <td class="stat-cell {{ $glowClass }}"
                                        style="vertical-align: top; text-align: left; padding: 6px; border: 1px solid #ddd; background: #fff; min-width: 75px;">
                                        <div style="font-size: 0.9em; margin-bottom: 2px; color: #333;">
                                            Nb={{ $cell->nb }} ;</div>
                                        <div
                                            style="background: yellow; color: black; display: inline-block; padding: 2px 4px; margin-bottom: 2px; font-weight: bold; width: fit-content; min-width: 35px; font-size: 0.85em;">
                                            {{ number_format($cell->stat * 100, 0) }}%
                                        </div><br>
                                        <div
                                            style="background: red; color: white; display: inline-block; padding: 2px 4px; font-weight: bold; width: fit-content; min-width: 35px; font-size: 0.85em;">
                                            {{ number_format($backwardPerc, 0) }}%
                                        </div>
                                    </td>
                                @endforeach
                                <td class="total-col" style="vertical-align: top; text-align: left; padding: 6px;">
                                    <div style="font-size: 0.9em; margin-bottom: 2px; color: #333;">Nb={{ $row[6]->nb }}
                                        ;</div>
                                    <div
                                        style="background: yellow; color: black; display: inline-block; padding: 2px 4px; font-weight: bold; font-size: 0.85em;">
                                        @if ($row[6]->nb > 0)
                                            100%
                                        @else
                                            0%
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Bottom total row (Somme) -->
                        <tr style="background-color: #f8f9fa;">
                            <td class="header-col" style="border-right: 2px solid #e0e6ed;">Somme</td>
                            @for ($c = 0; $c < 6; $c++)
                                <td style="vertical-align: top; text-align: left; padding: 6px; border: 1px solid #ddd;">
                                    <div style="font-size: 0.9em; margin-bottom: 2px; color: #333; font-weight:bold;">
                                        Nb={{ $colTotals[$c] }} ;</div>
                                    <div
                                        style="background: transparent; color: transparent; display: inline-block; padding: 2px 4px; margin-bottom: 2px; font-weight: bold; width: fit-content; min-width: 35px; font-size: 0.85em;">
                                        <br></div><br>
                                    <div
                                        style="background: red; color: white; display: inline-block; padding: 2px 4px; font-weight: bold; font-size: 0.85em;">
                                        @if ($colTotals[$c] > 0)
                                            100%
                                        @else
                                            0%
                                        @endif
                                    </div>
                                </td>
                            @endfor
                            <td class="total-col" style="vertical-align: middle;">
                                -
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 20px;">
                <form action="{{ route('markov.reset') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger"
                        style="background: #d63031; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        Clear History & Reset Matrix
                    </button>
                </form>
            </div>
        </div> <!-- End Top Column -->

        <!-- === BOTTOM COLUMN: Information/Network Graph === -->
        <div style="width: 100%;">
            <!-- The Vis.js Network Graph Container -->
            <div style="margin-top: 0;">
                <h3 style="margin-top: 0;">Markov State Transition Graph</h3>
                <p style="font-size: 0.9em; color: #555;">Real-time visualization of the transition matrix percentages.</p>
                <div id="markov-network"
                    style="width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 8px; background: #fafafa; box-shadow: inset 0 0 10px rgba(0,0,0,0.05);">
                </div>
            </div>
        </div>
    </div> <!-- End Main Flex Wrapper -->
@endsection

@section('scripts')
    <!-- Load Vis.js from CDN -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare the matrix data from PHP to JS
            const matrixData = @json($matrix);
            const currentCell = {{ session('last_cell_id') !== null ? session('last_cell_id') : 'null' }};
            const predictedCells = @json($predictedCells ?? []);
            const predictedPrevCells = @json($predictedPrevCells ?? []);

            // 1. Create Nodes (The 6 Cells)
            const nodesArray = [];
            for (let i = 0; i < 6; i++) {
                let nodeBg = '#74b9ff';
                let nodeBorder = '#0984e3';

                if (currentCell === i) {
                    nodeBg = '#d63031'; // Current (Red)
                    nodeBorder = '#b33939';
                } else if (predictedCells.includes(i)) {
                    nodeBg = '#f39c12'; // Forward (Yellow)
                    nodeBorder = '#e67e22';
                } else if (predictedPrevCells.includes(i)) {
                    nodeBg = '#9b59b6'; // Backward (Purple)
                    nodeBorder = '#8e44ad';
                }

                nodesArray.push({
                    id: i,
                    label: 'Cell ' + i,
                    color: {
                        background: nodeBg,
                        border: nodeBorder
                    },
                    font: {
                        color: nodeBg === '#74b9ff' ? 'black' : 'white'
                    },
                    borderWidth: (nodeBg !== '#74b9ff') ? 3 : 1
                });
            }

            const nodes = new vis.DataSet(nodesArray);

            // 2. Create Edges (The Transitions > 0%)
            const edgesArray = [];

            for (let from = 0; from < 6; from++) {
                if (!matrixData[from]) continue;

                for (let to = 0; to < 6; to++) {
                    const stat = matrixData[from][to].stat;
                    const nb = matrixData[from][to].nb;

                    // Only draw an edge if the probability is greater than 0
                    if (stat > 0) {
                        const percentage = Math.round(stat * 100) + '%';
                        edgesArray.push({
                            from: from,
                            to: to,
                            label: nb + ' (' + percentage + ')',
                            arrows: 'to',
                            font: {
                                align: 'middle',
                                size: 14,
                                color: '#2d3436',
                                strokeWidth: 3,
                                strokeColor: '#ffffff'
                            },
                            color: {
                                color: '#0984e3',
                                highlight: '#d63031'
                            },
                            width: stat * 4 + 1, // Make the line thicker if probability is higher
                            smooth: {
                                type: 'dynamic', // Allows curved lines so A->B and B->A don't overlap perfectly
                                roundness: from === to ? 1 :
                                    0.2 // If self-referencing (from A to A), make a perfect circle
                            }
                        });
                    }
                }
            }

            const edges = new vis.DataSet(edgesArray);

            // 3. Initialize the Network Graph
            const container = document.getElementById('markov-network');
            const data = {
                nodes: nodes,
                edges: edges
            };
            const options = {
                physics: {
                    barnesHut: {
                        gravitationalConstant: -2000,
                        centralGravity: 0.3,
                        springLength: 200
                    }
                },
                nodes: {
                    shape: 'ellipse',
                    size: 30,
                    font: {
                        size: 16,
                        bold: true
                    },
                    borderWidth: 2,
                    shadow: true
                },
                layout: {
                    randomSeed: 2 // Keeps the graph from spinning wildly on every page load
                }
            };

            const network = new vis.Network(container, data, options);
        });
    </script>
@endsection
