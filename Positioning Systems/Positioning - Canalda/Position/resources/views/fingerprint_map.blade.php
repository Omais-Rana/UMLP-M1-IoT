@extends('layouts.app')

@section('content')
    <h1>Indoor Positioning: Fingerprinting</h1>

    <div
        style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 5px solid #d63031; text-align: left;">
        <strong>TM Measured RSSI:</strong> [{{ implode(', ', $tmRssi) }}] <br>
        <strong>Estimated Position (P̂):</strong>
        <span style="color: #d63031; font-weight: bold;">X: {{ round($result->x, 2) }}m, Y:
            {{ round($result->y, 2) }}m</span>
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
