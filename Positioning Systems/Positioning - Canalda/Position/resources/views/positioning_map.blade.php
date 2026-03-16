@extends('layouts.app')

@section('styles')
    <style>
        .controls {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .btn-group button {
            padding: 10px 20px;
            margin: 0 5px;
            cursor: pointer;
            border: 2px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-group button.active {
            background: var(--primary);
            color: white;
        }

        .map-container {
            width: 100%;
            height: 600px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        #container-3d {
            display: none;
        }

        canvas {
            display: block;
            margin: 0 auto;
        }
    </style>
@endsection

@section('content')
    <div class="controls">
        <h2>Indoor Positioning: N-Lateration
            @if (isset($example) && $example == 3)
                (3 Emitters Example)
            @else
                (TD n°2 - 4 Emitters)
            @endif
        </h2>
        <div
            style="background: #eef2f5; padding: 15px; margin-top: 15px; margin-bottom: 15px; border-left: 4px solid var(--primary); text-align: left; font-size: 0.9em; border-radius: 4px;">
            <strong>Initial Data (Emitters):</strong>
            <ul style="margin-top: 5px; margin-bottom: 10px;">
                @foreach ($emitters as $index => $emitter)
                    <li>
                        <strong>E<sub>{{ $index }}</sub>:</strong>
                        (X: {{ $emitter->position->x }}m, Y: {{ $emitter->position->y }}m, Z: {{ $emitter->position->z }}m)
                        | <strong>d<sub>{{ $index }}</sub></strong>: {{ $emitter->measuredDistance }}m
                    </li>
                @endforeach
            </ul>
            <strong>Mathematical Logic:</strong> Grid Search SAE (Sum of Absolute Errors).<br>
            <strong>Cost Function:</strong> f(P) = &sum; | dist(P, E<sub>i</sub>) - d<sub>i</sub> |
        </div>

        <div style="margin-bottom: 20px;">
            <strong>Test Precision Interval:</strong>
            <div class="btn-group" style="display: inline-block; margin-left: 10px;">
                <a href="{{ route('lateration', ['precision' => 1.0, 'example' => isset($example) ? $example : 1]) }}"><button
                        class="{{ $precision == 1.0 ? 'active' : '' }}">1.0m</button></a>
                <a href="{{ route('lateration', ['precision' => 0.5, 'example' => isset($example) ? $example : 1]) }}"><button
                        class="{{ $precision == 0.5 ? 'active' : '' }}">0.5m</button></a>
                <a href="{{ route('lateration', ['precision' => 0.1, 'example' => isset($example) ? $example : 1]) }}"><button
                        class="{{ $precision == 0.1 ? 'active' : '' }}">0.1m</button></a>
            </div>

            @if (isset($executionTime))
                <div style="margin-top: 10px; font-size: 0.9em; color: #636e72;">
                    <strong>Execution Time:</strong> {{ $executionTime }} ms
                </div>
            @endif
        </div>

        <p>
            <strong>Calculated Position (P̂):</strong>
            @if ($result)
                <span style="color: var(--accent); font-weight: bold;">
                    X: {{ round($result->x, 2) }}m, Y: {{ round($result->y, 2) }}m, Z: {{ round($result->z, 2) }}m
                </span>
            @endif
        </p>
        <div class="btn-group">
            <button id="btn-2d" class="active" onclick="toggleView('2d')">2D Plan View</button>
            <button id="btn-3d" onclick="toggleView('3d')">3D Volumetric View</button>
        </div>
    </div>

    <div id="container-2d" class="map-container">
        <canvas id="canvas-2d" width="840" height="600"></canvas>
    </div>

    <div id="container-3d" class="map-container"></div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script>
        const emitters = @json($emitters);
        const result = @json($result);

        function toggleView(view) {
            document.getElementById('container-2d').style.display = view === '2d' ? 'block' : 'none';
            document.getElementById('container-3d').style.display = view === '3d' ? 'block' : 'none';
            document.getElementById('btn-2d').classList.toggle('active', view === '2d');
            document.getElementById('btn-3d').classList.toggle('active', view === '3d');
        }

        // --- 2D CANVAS LOGIC ---
        function init2D() {
            const canvas = document.getElementById('canvas-2d');
            const ctx = canvas.getContext('2d');
            const scale = 90;
            const offsetX = 100;
            const offsetY = 80;
            const gridSize = 6;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw Grid
            ctx.strokeStyle = '#e0e0e0';
            ctx.fillStyle = '#999';
            ctx.font = '11px Arial';
            for (let i = 0; i <= gridSize; i++) {
                const posX = (i * scale) + offsetX;
                const posY = (i * scale) + offsetY;

                ctx.beginPath();
                ctx.moveTo(posX, offsetY);
                ctx.lineTo(posX, (gridSize * scale) + offsetY);
                ctx.stroke();
                ctx.fillText(i + "m", posX - 8, offsetY - 15);

                ctx.beginPath();
                ctx.moveTo(offsetX, posY);
                ctx.lineTo((gridSize * scale) + offsetX, posY);
                ctx.stroke();
                ctx.fillText(i + "m", offsetX - 30, posY + 4);
            }

            if (!result) return;

            const resX = (result.x * scale) + offsetX;
            const resY = (result.y * scale) + offsetY;

            emitters.forEach((e, index) => {
                const ex = (e.position.x * scale) + offsetX;
                const ey = (e.position.y * scale) + offsetY;
                const radius = e.measuredDistance * scale;

                // Sphere Projection
                ctx.beginPath();
                ctx.arc(ex, ey, radius, 0, Math.PI * 2);
                ctx.strokeStyle = 'rgba(0, 123, 255, 0.2)';
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(ex, ey, 5, 0, Math.PI * 2);
                ctx.fillStyle = '#0984e3';
                ctx.fill();
            });

            // P̂
            ctx.beginPath();
            ctx.arc(resX, resY, 8, 0, Math.PI * 2);
            ctx.fillStyle = '#d63031';
            ctx.fill();
        }

        // --- 3D THREE.JS LOGIC ---
        function init3D() {
            if (!result) return;
            const container = document.getElementById('container-3d');
            
            // Clear prior canvases to prevent stack duplicates
            container.innerHTML = '';
            
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0xffffff);

            const camera = new THREE.PerspectiveCamera(45, 840 / 600, 0.1, 1000);
            camera.position.set(12, 12, 12);

            const renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(840, 600);
            container.appendChild(renderer.domElement);

            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.target.set(3, 1, 3);
            controls.update();

            scene.add(new THREE.AmbientLight(0xffffff, 0.8));
            const gridHelper = new THREE.GridHelper(6, 6, 0x000000, 0xcccccc);
            gridHelper.position.set(3, 0, 3);
            scene.add(gridHelper);

            // PHP (x, y, z) -> Three.js (x, z_height, y_depth)
            const toThree = (pos) => new THREE.Vector3(pos.x, pos.z, pos.y);

            // Smartphone
            const phone = new THREE.Mesh(
                new THREE.SphereGeometry(0.2, 32, 32),
                new THREE.MeshLambertMaterial({
                    color: 0xd63031
                })
            );
            phone.position.copy(toThree(result));
            scene.add(phone);

            // Emitters
            emitters.forEach((e) => {
                const emVec = toThree(e.position);
                const sphere = new THREE.Mesh(
                    new THREE.SphereGeometry(e.measuredDistance, 32, 32),
                    new THREE.MeshBasicMaterial({
                        color: 0x0984e3,
                        wireframe: true,
                        transparent: true,
                        opacity: 0.1
                    })
                );
                sphere.position.copy(emVec);
                scene.add(sphere);
            });

            function animate() {
                requestAnimationFrame(animate);
                renderer.render(scene, camera);
            }
            animate();
        }

        init2D();
        init3D();
    </script>
@endsection

