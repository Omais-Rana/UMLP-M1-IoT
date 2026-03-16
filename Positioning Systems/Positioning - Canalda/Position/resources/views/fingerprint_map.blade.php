@extends('layouts.app')

@section('content')
    <h1>Indoor Positioning: Fingerprinting</h1>

    <div
        style="background: #eef2f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #0984e3; text-align: left; font-size: 0.9em; border-radius: 4px;">
        <strong>Initial Data (Radio Map Grid):</strong>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; margin-bottom: 10px;">
            @foreach ($grid as $row)
                @foreach ($row as $cell)
                    <div
                        style="background: white; padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                        <strong>Center:</strong> ({{ $cell->x }}m, {{ $cell->y }}m)<br>
                        <strong>RSSI:</strong> [{{ implode(', ', $cell->rssi) }}]
                    </div>
                @endforeach
            @endforeach
        </div>
        <strong>Mathematical Logic:</strong> Weighted K-Nearest Neighbors (WKNN) and Barycentric Ponderation.<br>
        <strong>Signal Distance:</strong> &radic;&sum; (RSSI<sub>obs</sub> - RSSI<sub>db</sub>)<sup>2</sup><br>
        <strong>Result Vector:</strong> OM = c<sub>1</sub> &middot; OK<sub>1</sub> + ... + c<sub>k</sub> &middot;
        OK<sub>k</sub>
    </div>

    <!-- Configuration Tabs -->
    <div style="margin-bottom: 20px; display: flex; justify-content: center; gap: 10px;">
        <span style="align-self: center; font-weight: bold; margin-right: 10px;">K-Neighbors:</span>
        <a href="?k=3"
            style="padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: bold; border: 1px solid #0984e3; 
                  background-color: {{ request('k') == 3 ? '#0984e3' : 'transparent' }}; 
                  color: {{ request('k') == 3 ? '#fff' : '#0984e3' }};">
            k = 3
        </a>
        <a href="?k=4"
            style="padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: bold; border: 1px solid #0984e3; 
                  background-color: {{ request('k', 4) == 4 ? '#0984e3' : 'transparent' }}; 
                  color: {{ request('k', 4) == 4 ? '#fff' : '#0984e3' }};">
            k = 4
        </a>
        <a href="?k=5"
            style="padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: bold; border: 1px solid #0984e3; 
                  background-color: {{ request('k') == 5 ? '#0984e3' : 'transparent' }}; 
                  color: {{ request('k') == 5 ? '#fff' : '#0984e3' }};">
            k = 5
        </a>
    </div>

    <div
        style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 5px solid #d63031; text-align: left;">
        <strong>TM Measured RSSI:</strong> [{{ implode(', ', $tmRssi) }}] <br>
        <strong>Estimated Position (P̂):</strong>
        <span style="color: #d63031; font-weight: bold;">X: {{ round($result->x, 2) }}m, Y:
            {{ round($result->y, 2) }}m</span>

        <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">
        <strong>Best Ordered Cells (Top K Nearest Neighbors):</strong>
        <div style="margin-top: 10px; font-size: 0.95em;">
            @foreach ($neighbors as $index => $n)
                <div style="margin-bottom: 5px;">
                    <span style="display:inline-block; width: 60px; font-weight:bold;">Rank {{ $index + 1 }}:</span>
                    Cell ({{ $n['cell']->x }}m, {{ $n['cell']->y }}m)
                    &mdash; <span style="color: #0984e3;">Distance: {{ round($n['d'], 4) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <canvas id="fingerprintCanvas" width="600" height="600"
        style="border: 2px solid #333; background: #fff; display: block; margin: 0 auto;"></canvas>

    <div style="margin-top: 15px; display: flex; justify-content: center; gap: 20px; font-size: 0.9em;">
        <span><b style="color: #0984e3;">●</b> Reference Point (Cell Center)</span>
        <span><b style="color: #d63031;">●</b> Mobile Terminal (Weighted)</span>
    </div>
@endsection

@section('scripts')
    <script>
        const grid = @json($grid);
        const res = @json($result);
        const canvas = document.getElementById('fingerprintCanvas');
        const ctx = canvas.getContext('2d');

        // Scale: 12m room / 600px canvas => 1m = 50px
        const scale = 50;

        function draw() {
            // 1. Draw the 4x4m Grid Cases (9 cells total)
            ctx.strokeStyle = '#dfe6e9';
            ctx.lineWidth = 1;
            for (let i = 0; i <= 3; i++) {
                // Vertical lines
                ctx.beginPath();
                ctx.moveTo(i * 4 * scale, 0);
                ctx.lineTo(i * 4 * scale, 600);
                ctx.stroke();
                // Horizontal lines
                ctx.beginPath();
                ctx.moveTo(0, i * 4 * scale);
                ctx.lineTo(600, i * 4 * scale);
                ctx.stroke();
            }

            // 2. Draw Cell Centers and RSSI Vectors
            grid.forEach(row => {
                row.forEach(cell => {
                    const cx = cell.x * scale;
                    const cy = cell.y * scale;

                    // Reference Point Dot (Offline Calibration)
                    ctx.fillStyle = '#0984e3';
                    ctx.beginPath();
                    ctx.arc(cx, cy, 6, 0, Math.PI * 2);
                    ctx.fill();

                    // RSSI Label (Radio Map Database)
                    ctx.fillStyle = '#636e72';
                    ctx.font = '10px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText(`[${cell.rssi.join(',')}]`, cx, cy + 20);
                });
            });

            // 3. Draw the Weighted Barycenter Result (Online Phase)
            const rx = res.x * scale;
            const ry = res.y * scale;

            // Visual pulse effect
            ctx.beginPath();
            ctx.arc(rx, ry, 15, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(214, 48, 49, 0.2)';
            ctx.fill();

            // The Mobile Terminal Dot
            ctx.beginPath();
            ctx.arc(rx, ry, 10, 0, Math.PI * 2);
            ctx.fillStyle = '#d63031';
            ctx.fill();
            ctx.strokeStyle = 'white';
            ctx.lineWidth = 3;
            ctx.stroke();

            ctx.fillStyle = '#d63031';
            ctx.font = 'bold 14px Arial';
            ctx.textAlign = 'left';
            ctx.fillText("Smartphone (TM)", rx + 15, ry + 5);
        }

        draw();
    </script>
@endsection
