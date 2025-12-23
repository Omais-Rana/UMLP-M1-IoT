<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IoT FPS - Controller Link (Silky Smooth)</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">

    <!-- Three.js & Real-time Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-color: #020617;
            font-family: 'Inter', sans-serif;
        }

        .ui-font {
            font-family: 'Orbitron', sans-serif;
        }

        #hud-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 50;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        #crosshair {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 2px solid rgba(16, 185, 129, 0.5);
            border-radius: 50%;
            transition: transform 0.1s;
        }

        #crosshair.firing {
            transform: scale(1.5);
            border-color: #ef4444;
        }

        #crosshair::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 4px;
            margin: -2px 0 0 -2px;
            background-color: #10b981;
            border-radius: 50%;
        }
    </style>
</head>

<body class="antialiased">

    <div id="hud-container">
        <!-- Top Header -->
        <div class="p-6 flex justify-between items-start">
            <div
                class="bg-slate-900/80 backdrop-blur-md border border-slate-700 p-4 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                <div class="flex items-center gap-3">
                    <div id="status-indicator" class="w-3 h-3 bg-red-500 rounded-full transition-colors duration-300">
                    </div>
                    <div>
                        <h1 class="ui-font text-white text-xs tracking-widest uppercase">Controller Link</h1>
                        <p id="connection-text" class="text-slate-400 text-[10px] uppercase font-bold">Offline</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900/80 backdrop-blur-md border border-slate-700 p-4 rounded-xl text-right">
                <h1 class="ui-font text-emerald-400 text-xs tracking-widest uppercase">Score</h1>
                <p id="score-val" class="text-3xl text-white font-bold tracking-tighter">0000</p>
            </div>
        </div>

        <!-- Center Crosshair -->
        <div id="crosshair"></div>

        <!-- Bottom Telemetry -->
        <div class="p-6 flex flex-col items-center justify-center">
            <div
                class="bg-black/60 backdrop-blur-sm border border-slate-800 px-6 py-2 rounded-full flex gap-8 text-[10px] ui-font text-slate-500 transition-colors mb-2">
                <div id="tel-p">PITCH: 0.0</div>
                <div id="tel-y">YAW: 0.0</div>
                <div id="tel-j" class="transition-colors">MOVE: STOP</div>
                <div id="tel-b" class="transition-colors">TRIGGER: SAFE</div>
            </div>
            <!-- Raw Data Debug Display -->
            <div id="tel-raw" class="text-[9px] font-mono text-slate-500 bg-slate-900/50 px-2 py-1 rounded">RAW:
                Waiting for packet...</div>
        </div>
    </div>

    <!-- Background Canvas Container -->
    <div id="game-canvas"></div>

    <script>
        /**
         * 1. REVERB CONFIGURATION
         */
        const ENV = {
            key: "{{ env('VITE_REVERB_APP_KEY') }}",
            host: "{{ env('VITE_REVERB_HOST') }}" || window.location.hostname,
            port: "{{ env('VITE_REVERB_PORT', 8080) }}",
            scheme: "{{ env('VITE_REVERB_SCHEME', 'http') }}"
        };

        // --- CALIBRATION & SMOOTHING CONFIG ---
        // Joystick Calibration
        let JOY_CENTER_X = 2048;
        let JOY_CENTER_Y = 2048;

        // IMU Centering (Offsets)
        let yawOffset = 0;
        let pitchOffset = 0;
        let isCalibrated = false;

        // --- SETTINGS ---
        const JOY_DEADZONE = 1500;
        const MAX_SPEED = 0.25;

        // ANTI-DRIFT SETTINGS (Refined for smoothness)
        // Pitch is stable (gravity based), so we lower threshold to near-zero for smooth aim
        const PITCH_DRIFT_THRESHOLD = 0.01;
        // Yaw drifts, but 1.2 was too "steppy". 0.5 is a better balance.
        const YAW_DRIFT_THRESHOLD = 0.5;

        // Increased LERP slightly for better responsiveness (0.05 -> 0.07)
        const LERP_FACTOR = 0.07;

        /**
         * 2. THREE.JS ENGINE SETUP
         */
        let scene, camera, renderer, raycaster, gun, playerGroup;
        let targets = [];
        let score = 0;
        let lastB = 0;

        // --- SMOOTHING STATE VARIABLES ---
        let targetPitch = 0;
        let targetYaw = 0;
        let targetMoveX = 0;
        let targetMoveZ = 0;

        // Track last RAW values to detect drift
        let lastRawP = 0;
        let lastRawY = 0;

        function initScene() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x020617);
            scene.fog = new THREE.FogExp2(0x020617, 0.02);

            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);

            playerGroup = new THREE.Group();
            playerGroup.position.set(0, 1.7, 5);
            playerGroup.add(camera);
            scene.add(playerGroup);

            renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('game-canvas').appendChild(renderer.domElement);

            // Lighting
            scene.add(new THREE.AmbientLight(0xffffff, 0.4));
            const pLight = new THREE.PointLight(0x10b981, 1, 40);
            pLight.position.set(2, 5, 2);
            scene.add(pLight);

            // Grid Floor
            const grid = new THREE.GridHelper(200, 60, 0x1e293b, 0x0f172a);
            scene.add(grid);

            // Gun Model
            const gunBody = new THREE.Mesh(
                new THREE.BoxGeometry(0.1, 0.15, 0.6),
                new THREE.MeshStandardMaterial({
                    color: 0x475569,
                    roughness: 0.3
                })
            );
            gunBody.position.set(0.3, -0.25, -0.5);
            gun = gunBody;
            camera.add(gun);

            raycaster = new THREE.Raycaster();
            spawnEnemies(15);
            animate();
        }

        function spawnEnemies(count) {
            const geo = new THREE.IcosahedronGeometry(0.6, 0);
            const mat = new THREE.MeshStandardMaterial({
                color: 0xea580c,
                emissive: 0x7c2d12,
                flatShading: true
            });
            for (let i = 0; i < count; i++) {
                const mesh = new THREE.Mesh(geo, mat);
                mesh.position.set(Math.random() * 60 - 30, 1 + Math.random() * 4, Math.random() * 60 - 30);
                mesh.userData = {
                    floatOffset: Math.random() * Math.PI * 2
                };
                targets.push(mesh);
                scene.add(mesh);
            }
        }

        /**
         * 3. IOT DATA PROCESSING
         */
        function handleIoTData(payload) {
            let data = payload;
            if (data.data) {
                data = data.data;
            }
            if (data.data) {
                data = data.data;
            }

            if (!data || typeof data !== 'object') return;
            const hasPitch = data.P !== undefined;
            const hasYaw = data.Y !== undefined;
            const hasJoy = (data.JX !== undefined && data.JY !== undefined);
            const hasBtn = data.B !== undefined;

            // --- 1. One-Time Calibration (On Connect) ---
            if (!isCalibrated && hasJoy && hasYaw) {
                JOY_CENTER_X = Number(data.JX);
                JOY_CENTER_Y = Number(data.JY);
                yawOffset = Number(data.Y);
                pitchOffset = Number(data.P);

                lastRawY = Number(data.Y);
                lastRawP = Number(data.P);

                isCalibrated = true;
                console.log("Calibrated. Yaw Offset:", yawOffset);
            }

            // Update UI Status
            const indicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('connection-text');
            if (!indicator.classList.contains('bg-emerald-500')) {
                indicator.className = 'w-3 h-3 rounded-full transition-colors duration-300 bg-emerald-500';
                statusText.innerText = "ONLINE (RX)";
                statusText.className = 'text-[10px] uppercase font-bold text-emerald-400';
            }

            // --- 2. UPDATE ROTATION TARGETS (Anti-Drift Logic) ---

            if (hasPitch) {
                const rawP = Number(data.P);
                // Reduced threshold for smooth vertical look
                if (Math.abs(rawP - lastRawP) > PITCH_DRIFT_THRESHOLD) {
                    const calibratedP = rawP - pitchOffset;
                    targetPitch = -(calibratedP * (Math.PI / 180));
                    document.getElementById('tel-p').innerText = `PITCH: ${calibratedP.toFixed(1)}`;
                    lastRawP = rawP;
                }
            }

            if (hasYaw) {
                const rawY = Number(data.Y);
                // Moderate threshold for yaw to prevent drift but allow aim
                if (Math.abs(rawY - lastRawY) > YAW_DRIFT_THRESHOLD) {
                    const calibratedY = rawY - yawOffset;
                    targetYaw = (calibratedY * (Math.PI / 180));
                    document.getElementById('tel-y').innerText = `YAW: ${calibratedY.toFixed(1)}`;
                    lastRawY = rawY;
                }
            }

            // --- 3. MOVEMENT (Joystick) ---
            if (hasJoy) {
                const JX = Number(data.JX);
                const JY = Number(data.JY);

                const dx = JX - JOY_CENTER_X;
                const dy = JY - JOY_CENTER_Y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                const telJ = document.getElementById('tel-j');

                if (distance > JOY_DEADZONE) {
                    telJ.innerText = "MOVE: ENGAGED";
                    telJ.classList.replace('text-slate-500', 'text-emerald-400');

                    const normX = dx / distance;
                    const normY = dy / distance;

                    const maxDist = 2048;
                    let speedRatio = (distance - JOY_DEADZONE) / (maxDist - JOY_DEADZONE);
                    if (speedRatio > 1) speedRatio = 1;

                    const finalSpeed = (speedRatio * speedRatio) * MAX_SPEED;

                    targetMoveX = -normX * finalSpeed;
                    targetMoveZ = -normY * finalSpeed;

                    document.getElementById('tel-raw').innerText = `RAW: Mov | Dist:${distance.toFixed(0)}`;
                } else {
                    telJ.innerText = "MOVE: STOP";
                    telJ.classList.replace('text-emerald-400', 'text-slate-500');
                    targetMoveX = 0;
                    targetMoveZ = 0;
                    document.getElementById('tel-raw').innerText = `RAW: Idle | Dist:${distance.toFixed(0)}`;
                }
            }

            // --- 4. Button ---
            if (hasBtn) {
                const B = Number(data.B);
                if (B === 1 && lastB === 0) {
                    triggerFire();
                }
                lastB = B;
            }
        }

        function triggerFire() {
            gun.position.z += 0.2;
            document.getElementById('crosshair').classList.add('firing');
            document.getElementById('tel-b').innerText = "TRIGGER: FIRE";
            document.getElementById('tel-b').classList.replace('text-slate-500', 'text-red-500');

            fetch('/api/haptics/fire', {
                method: 'POST'
            }).catch(e => console.log('Haptic API Error', e));

            setTimeout(() => {
                gun.position.z -= 0.2;
                document.getElementById('crosshair').classList.remove('firing');
                document.getElementById('tel-b').innerText = "TRIGGER: SAFE";
                document.getElementById('tel-b').classList.replace('text-red-500', 'text-slate-500');
            }, 100);

            raycaster.setFromCamera(new THREE.Vector2(0, 0), camera);
            const hits = raycaster.intersectObjects(targets);

            if (hits.length > 0) {
                const hit = hits[0].object;
                const tempMat = hit.material.clone();
                tempMat.emissive.setHex(0xffffff);
                hit.material = tempMat;
                setTimeout(() => {
                    scene.remove(hit);
                    targets = targets.filter(t => t !== hit);
                    score += 150;
                    document.getElementById('score-val').innerText = score.toString().padStart(4, '0');
                    if (targets.length < 5) spawnEnemies(10);
                }, 50);
            }
        }

        function setupRealtime() {
            Pusher.logToConsole = true;
            const indicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('connection-text');

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: ENV.key,
                wsHost: ENV.host,
                wsPort: ENV.port,
                forceTLS: (ENV.scheme === 'https'),
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log('Reverb WebSocket Connected');
                if (!statusText.innerText.includes('RX')) {
                    indicator.className =
                        'w-3 h-3 rounded-full transition-colors duration-300 bg-yellow-500 animate-pulse';
                    statusText.innerText = "SOCKET CONNECTED (WAITING FOR DATA)";
                    statusText.className = 'text-[10px] uppercase font-bold text-yellow-500';
                }
            });

            window.Echo.connector.pusher.connection.bind('failed', () => {
                console.error('Reverb WebSocket Failed');
                indicator.className = 'w-3 h-3 rounded-full transition-colors duration-300 bg-red-600';
                statusText.innerText = "SOCKET FAILED";
                statusText.className = 'text-[10px] uppercase font-bold text-red-600';
            });

            window.Echo.channel('game-controls')
                .listen('.data.received', (e) => {
                    handleIoTData(e);
                });
        }

        function animate() {
            requestAnimationFrame(animate);

            // --- SMOOTHING INTERPOLATION ---
            camera.rotation.x += (targetPitch - camera.rotation.x) * LERP_FACTOR;
            playerGroup.rotation.y += (targetYaw - playerGroup.rotation.y) * LERP_FACTOR;

            if (targetMoveX !== 0 || targetMoveZ !== 0) {
                playerGroup.translateX(targetMoveX);
                playerGroup.translateZ(targetMoveZ);
            }

            const time = Date.now() * 0.001;
            targets.forEach(t => {
                t.rotation.x += 0.01;
                t.rotation.y += 0.01;
                t.position.y += Math.sin(time + t.userData.floatOffset) * 0.005;
            });

            renderer.render(scene, camera);
        }

        window.onload = () => {
            initScene();
            setupRealtime();
        };

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>

</html>
