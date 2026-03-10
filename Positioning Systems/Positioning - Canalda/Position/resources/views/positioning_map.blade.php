<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>TD n°2: N-Lateration Raw Data Visualization</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
            width: 850px;
            position: relative;
            z-index: 10;
        }

        .btn-group button {
            padding: 12px 24px;
            margin: 0 8px;
            cursor: pointer;
            border: 2px solid #007bff;
            background: white;
            color: #007bff;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-group button.active {
            background: #007bff;
            color: white;
        }

        .map-container {
            width: 850px;
            height: 600px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        #container-2d {
            display: block;
        }

        #container-3d {
            display: none;
        }

        canvas {
            display: block;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
</head>

<body>

    <div class="controls">
        <h2>Indoor Positioning: N-Lateration (Raw Data)</h2>
        <p>
            <strong>Calculated Position (P̂):</strong>
            @if ($result)
                <span style="color: #d63031;">X: {{ round($result->x, 2) }}, Y: {{ round($result->y, 2) }}, Z:
                    {{ round($result->z, 2) }}</span>
            @endif
        </p>
        <div class="btn-group">
            <button id="btn-2d" class="active" onclick="toggleView('2d')">2D Plan View</button>
            <button id="btn-3d" onclick="toggleView('3d')">3D Volumetric View</button>
        </div>
    </div>

    <div id="container-2d" class="map-container"><canvas id="canvas-2d" width="850" height="600"></canvas></div>
    <div id="container-3d" class="map-container"></div>

    <script>
        const emitters = @json($emitters);
        const result = @json($result);

        function toggleView(view) {
            document.getElementById('container-2d').style.display = view === '2d' ? 'block' : 'none';
            document.getElementById('container-3d').style.display = view === '3d' ? 'block' : 'none';
            document.getElementById('btn-2d').classList.toggle('active', view === '2d');
            document.getElementById('btn-3d').classList.toggle('active', view === '3d');
        }

        // --- 2D CANVAS ---
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
                // USE ORIGINAL MEASURED DISTANCE AS RADIUS
                const radius = e.measuredDistance * scale;

                // Range Circle (Overlapping)
                ctx.beginPath();
                ctx.arc(ex, ey, radius, 0, Math.PI * 2);
                ctx.strokeStyle = 'rgba(0, 123, 255, 0.3)';
                ctx.lineWidth = 2;
                ctx.stroke();

                // 'd' Line to Smartphone
                ctx.beginPath();
                ctx.moveTo(ex, ey);
                ctx.lineTo(resX, resY);
                ctx.strokeStyle = '#fab1a0';
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.setLineDash([]);

                // Emitter Center
                ctx.beginPath();
                ctx.arc(ex, ey, 5, 0, Math.PI * 2);
                ctx.fillStyle = '#0984e3';
                ctx.fill();
                ctx.fillStyle = '#2d3436';
                ctx.fillText(`E${index} (${e.measuredDistance}m)`, ex + 8, ey - 8);
            });

            // Result Point
            ctx.beginPath();
            ctx.arc(resX, resY, 8, 0, Math.PI * 2);
            ctx.fillStyle = '#d63031';
            ctx.fill();
            ctx.fillText("P̂ (Calculated)", resX + 12, resY + 12);
        }

        // --- 3D THREE.JS ---
        function init3D() {
            if (!result) return;
            const container = document.getElementById('container-3d');
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0xffffff);

            const camera = new THREE.PerspectiveCamera(45, 850 / 600, 0.1, 1000);
            // Position camera to look at the room from the front-right
            camera.position.set(12, 12, 12);

            const renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(850, 600);
            container.appendChild(renderer.domElement);
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.target.set(3, 1, 3); // Focus on the center of the 6m room
            controls.update();

            // 1. LIGHTING & HELPERS
            scene.add(new THREE.AmbientLight(0xffffff, 0.7));
            const light = new THREE.DirectionalLight(0xffffff, 0.5);
            light.position.set(10, 10, 10);
            scene.add(light);

            // X=Red, Y=Green, Z=Blue. In this view: Red=X, Green=Z(height), Blue=Y
            scene.add(new THREE.AxesHelper(6));

            // The Grid sits on the X-Z plane (the floor). 
            // We treat PHP Y as Three.js Z (depth) and PHP Z as Three.js Y (height).
            const grid = new THREE.GridHelper(6, 6, 0x000000, 0xcccccc);
            grid.position.set(3, 0, 3);
            scene.add(grid);

            // 2. COORDINATE MAPPING FUNCTION
            // PHP (x, y, z) -> Three.js (x, z_height, y_depth)
            const toThree = (pos) => new THREE.Vector3(pos.x, pos.z, pos.y);

            // 3. DRAW RESULT (The Smartphone)
            const resultVec = toThree(result);
            const phone = new THREE.Mesh(
                new THREE.SphereGeometry(0.2, 32, 32),
                new THREE.MeshLambertMaterial({
                    color: 0xd63031
                })
            );
            phone.position.copy(resultVec);
            scene.add(phone);

            // 4. DRAW EMITTERS & SPHERES
            emitters.forEach((e) => {
                const emVec = toThree(e.position);

                // Physical Anchor Dot
                const anchor = new THREE.Mesh(
                    new THREE.SphereGeometry(0.12, 16, 16),
                    new THREE.MeshLambertMaterial({
                        color: 0x0000ff
                    })
                );
                anchor.position.copy(emVec);
                scene.add(anchor);

                // The Measurement Sphere
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

                // Add a line connecting Anchor to Phone for visual verification
                const lineGeo = new THREE.BufferGeometry().setFromPoints([emVec, resultVec]);
                const line = new THREE.Line(lineGeo, new THREE.LineBasicMaterial({
                    color: 0xaaaaaa
                }));
                scene.add(line);
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
</body>

</html>
