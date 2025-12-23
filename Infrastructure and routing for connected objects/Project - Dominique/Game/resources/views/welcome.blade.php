<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IoT FPS - Tactical Link</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Allerta+Stencil&family=Roboto+Mono:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Three.js & Real-time Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-color: #1a1a1a;
            font-family: 'Roboto Mono', monospace;
        }

        .ui-font {
            font-family: 'Allerta Stencil', sans-serif;
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
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            /* Simple Mil-Dot Crosshair */
            background-image: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><line x1="20" y1="5" x2="20" y2="35" stroke="rgba(0, 0, 0, 0.8)" stroke-width="2"/><line x1="5" y1="20" x2="35" y2="20" stroke="rgba(0, 0, 0, 0.8)" stroke-width="2"/><circle cx="20" cy="20" r="18" stroke="rgba(0,0,0,0.5)" stroke-width="1" fill="none"/></svg>');
            transition: transform 0.05s;
        }

        #crosshair.firing {
            transform: scale(1.4);
        }

        .hud-panel {
            background: rgba(20, 20, 20, 0.85);
            border: 2px solid #4b5563;
            /* Gray-600 */
            color: #d1d5db;
            /* Gray-300 */
        }

        .hud-text-amber {
            color: #f59e0b;
            text-shadow: 0 0 2px rgba(245, 158, 11, 0.5);
        }
    </style>
</head>

<body class="antialiased">

    <div id="hud-container">
        <!-- Top Header -->
        <div class="p-6 flex justify-between items-start">
            <div class="hud-panel p-4 rounded-sm">
                <div class="flex items-center gap-3">
                    <div id="status-indicator" class="w-3 h-3 bg-red-600 rounded-full border border-red-900"></div>
                    <div>
                        <h1 class="ui-font hud-text-amber text-xs tracking-widest uppercase">UPLINK STATUS</h1>
                        <p id="connection-text" class="text-gray-500 text-[10px] uppercase font-bold">DISCONNECTED</p>
                    </div>
                </div>
            </div>

            <div class="hud-panel p-4 rounded-sm text-right">
                <h1 class="ui-font hud-text-amber text-xs tracking-widest uppercase">CONFIRMED KILLS</h1>
                <p id="score-val" class="text-3xl text-white font-bold tracking-tighter">000</p>
            </div>
        </div>

        <!-- Center Crosshair -->
        <div id="crosshair"></div>

        <!-- Bottom Telemetry -->
        <div class="p-6 flex flex-col items-center justify-center">
            <div
                class="hud-panel px-6 py-2 rounded-sm flex gap-8 text-[10px] ui-font text-gray-500 transition-colors mb-2">
                <div id="tel-p">PITCH: 0.0</div>
                <div id="tel-y">YAW: 0.0</div>
                <div id="tel-j" class="transition-colors">MOVE: HOLD</div>
            </div>
            <!-- Raw Data Debug Display -->
            <div id="tel-raw" class="text-[9px] font-mono text-gray-600 bg-black/80 px-2 py-1 rounded">RAW: --</div>
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

        // --- CALIBRATION & CONFIG ---
        let JOY_CENTER_X = 2048;
        let JOY_CENTER_Y = 2048;
        let yawOffset = 0;
        let pitchOffset = 0;
        let isCalibrated = false;

        // Lowered deadzone for instant response (was 500)
        const JOY_DEADZONE = 250;
        const MAX_SPEED = 0.25;

        // --- LOW LATENCY SMOOTHING CONFIG ---
        // History buffer: Reduced to 2 for minimal lag, but enough to kill spikes
        const HISTORY_SIZE = 2;
        let pHistory = [];
        let yHistory = [];

        // Weights: Heavily favor the NEWEST data (0.7)
        const WEIGHTS = [0.3, 0.7];

        // Anti-Drift Gates
        const DRIFT_GATE_P = 0.02;
        const DRIFT_GATE_Y = 0.1;

        // Render Interpolation: Increased to 0.25 for snappy response
        const LERP_FACTOR = 0.25;

        /**
         * 2. THREE.JS ENGINE SETUP
         */
        let scene, camera, renderer, raycaster, gunContainer, muzzleLight;
        let targets = [];
        let score = 0;
        let lastB = 0;

        // --- SMOOTHING STATE VARIABLES ---
        let targetPitch = 0;
        let targetYaw = 0;
        let targetMoveX = 0;
        let targetMoveZ = 0;

        // Sensor State
        let lastRegisteredP = 0;
        let lastRegisteredY = 0;

        function initScene() {
            scene = new THREE.Scene();
            // Realistic Sky Color (Overcast/Dusty)
            scene.background = new THREE.Color(0x8899a6);
            scene.fog = new THREE.Fog(0x8899a6, 10, 60);

            camera = new THREE.PerspectiveCamera(65, window.innerWidth / window.innerHeight, 0.1, 1000);

            playerGroup = new THREE.Group();
            playerGroup.position.set(0, 1.7, 5);
            playerGroup.add(camera);
            scene.add(playerGroup);

            renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.getElementById('game-canvas').appendChild(renderer.domElement);

            // -- LIGHTING --
            scene.add(new THREE.AmbientLight(0xffffff, 0.4));

            // Sun Light
            const sunLight = new THREE.DirectionalLight(0xfffaed, 1.2);
            sunLight.position.set(50, 100, 50);
            sunLight.castShadow = true;
            sunLight.shadow.mapSize.width = 2048;
            sunLight.shadow.mapSize.height = 2048;
            scene.add(sunLight);

            // Muzzle Flash (Warm Orange)
            muzzleLight = new THREE.PointLight(0xffaa33, 0, 8);
            muzzleLight.position.set(0.3, -0.2, -1.5);
            camera.add(muzzleLight);

            // -- ENVIRONMENT --
            createEnvironment();
            createGunModel();

            raycaster = new THREE.Raycaster();
            spawnEnemies(10);
            animate();
        }

        function createEnvironment() {
            // Ground (Dirt)
            const planeGeo = new THREE.PlaneGeometry(200, 200);
            const planeMat = new THREE.MeshStandardMaterial({
                color: 0x5d5b50, // Muddy Brown/Grey
                roughness: 1.0,
                metalness: 0.0
            });
            const floor = new THREE.Mesh(planeGeo, planeMat);
            floor.rotation.x = -Math.PI / 2;
            floor.receiveShadow = true;
            scene.add(floor);

            // Obstacles
            const boxGeo = new THREE.BoxGeometry(1, 1, 1);
            const concreteMat = new THREE.MeshStandardMaterial({
                color: 0x888888,
                roughness: 0.9
            });
            const woodMat = new THREE.MeshStandardMaterial({
                color: 0x8b5a2b,
                roughness: 0.8
            });

            for (let i = 0; i < 40; i++) {
                const isCrate = Math.random() > 0.5;
                const mesh = new THREE.Mesh(boxGeo, isCrate ? woodMat : concreteMat);
                const sX = 1 + Math.random() * 2;
                const sY = 1 + Math.random() * 3;
                const sZ = 1 + Math.random() * 2;
                mesh.scale.set(sX, sY, sZ);
                mesh.position.set((Math.random() - 0.5) * 80, sY / 2, (Math.random() - 0.5) * 80);
                mesh.castShadow = true;
                mesh.receiveShadow = true;
                scene.add(mesh);
            }
        }

        function createGunModel() {
            gunContainer = new THREE.Group();

            // Materials
            const matGunmetal = new THREE.MeshStandardMaterial({
                color: 0x222222,
                roughness: 0.4,
                metalness: 0.6
            });
            const matWood = new THREE.MeshStandardMaterial({
                color: 0x5c4033,
                roughness: 0.8
            });
            const matSteel = new THREE.MeshStandardMaterial({
                color: 0x555555,
                roughness: 0.3,
                metalness: 0.8
            });

            // 1. Receiver
            const receiver = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.12, 0.4), matGunmetal);

            // 2. Stock
            const stock = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.14, 0.35), matWood);
            stock.position.set(0, -0.05, 0.35);

            // 3. Barrel
            const barrel = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.015, 0.6), matSteel);
            barrel.rotation.x = Math.PI / 2;
            barrel.position.set(0, 0.03, -0.4);

            // 4. Handguard
            const handguard = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.07, 0.3), matWood);
            handguard.position.set(0, 0, -0.3);

            // 5. Mag
            const mag = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.25, 0.08), matGunmetal);
            mag.rotation.x = 0.3;
            mag.position.set(0, -0.15, -0.05);

            // 6. Sight
            const sight = new THREE.Mesh(new THREE.BoxGeometry(0.01, 0.04, 0.01), matSteel);
            sight.position.set(0, 0.05, -0.68);

            gunContainer.add(receiver, stock, barrel, handguard, mag, sight);
            gunContainer.position.set(0.25, -0.2, -0.4);
            camera.add(gunContainer);
        }

        function spawnEnemies(count) {
            const geo = new THREE.CylinderGeometry(0.4, 0.4, 0.1, 16);
            geo.rotateX(Math.PI / 2);

            const matRed = new THREE.MeshStandardMaterial({
                color: 0xcc0000
            });
            const matWhite = new THREE.MeshStandardMaterial({
                color: 0xffffff
            });

            for (let i = 0; i < count; i++) {
                const targetGroup = new THREE.Group();
                const ring1 = new THREE.Mesh(geo, matRed);
                const ring2 = new THREE.Mesh(new THREE.CylinderGeometry(0.25, 0.25, 0.11, 16), matWhite);
                ring2.rotateX(Math.PI / 2);
                const ring3 = new THREE.Mesh(new THREE.CylinderGeometry(0.1, 0.1, 0.12, 16), matRed);
                ring3.rotateX(Math.PI / 2);

                targetGroup.add(ring1, ring2, ring3);
                targetGroup.position.set((Math.random() - 0.5) * 60, 1.5 + Math.random() * 2, (Math.random() - 0.5) * 60);
                targetGroup.userData = {
                    floatSpeed: 0.01 + Math.random() * 0.02,
                    floatOffset: Math.random() * Math.PI * 2
                };
                targets.push(targetGroup);
                scene.add(targetGroup);
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

                // Init History Buffers
                pHistory = Array(HISTORY_SIZE).fill(Number(data.P));
                yHistory = Array(HISTORY_SIZE).fill(Number(data.Y));

                lastRegisteredP = Number(data.P);
                lastRegisteredY = Number(data.Y);

                isCalibrated = true;
                console.log("Calibrated. Yaw Offset:", yawOffset);
            }

            // Update UI Status
            const indicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('connection-text');
            if (!indicator.classList.contains('bg-green-500')) {
                indicator.className = 'w-3 h-3 rounded-full bg-green-500 shadow-[0_0_10px_#22c55e]';
                statusText.innerText = "UPLINK SECURE";
                statusText.className = 'text-[10px] uppercase font-bold text-green-500';
            }

            // --- 2. UPDATE TARGETS (Low Latency Moving Average) ---

            if (hasPitch) {
                const rawP = Number(data.P);
                pHistory.push(rawP);
                if (pHistory.length > HISTORY_SIZE) pHistory.shift();

                // Weighted Average (Favors new data)
                let weightedSum = 0;
                let weightTotal = 0;
                for (let i = 0; i < pHistory.length; i++) {
                    weightedSum += pHistory[i] * WEIGHTS[i];
                    weightTotal += WEIGHTS[i];
                }
                const avgP = weightedSum / weightTotal;

                if (Math.abs(avgP - lastRegisteredP) > DRIFT_GATE_P) {
                    const calibratedP = avgP - pitchOffset;
                    targetPitch = -(calibratedP * (Math.PI / 180));
                    document.getElementById('tel-p').innerText = `PITCH: ${calibratedP.toFixed(1)}`;
                    lastRegisteredP = avgP;
                }
            }

            if (hasYaw) {
                const rawY = Number(data.Y);
                yHistory.push(rawY);
                if (yHistory.length > HISTORY_SIZE) yHistory.shift();

                let weightedSum = 0;
                let weightTotal = 0;
                for (let i = 0; i < yHistory.length; i++) {
                    weightedSum += yHistory[i] * WEIGHTS[i];
                    weightTotal += WEIGHTS[i];
                }
                const avgY = weightedSum / weightTotal;

                if (Math.abs(avgY - lastRegisteredY) > DRIFT_GATE_Y) {
                    const calibratedY = rawY - yawOffset;
                    targetYaw = (calibratedY * (Math.PI / 180));
                    document.getElementById('tel-y').innerText = `YAW: ${calibratedY.toFixed(1)}`;
                    lastRegisteredY = avgY;
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
                    telJ.classList.replace('text-gray-500', 'text-white');

                    const normX = dx / distance;
                    const normY = dy / distance;

                    const maxDist = 2048;
                    let speedRatio = (distance - JOY_DEADZONE) / (maxDist - JOY_DEADZONE);
                    if (speedRatio > 1) speedRatio = 1;

                    // Linear Curve for instant response (was Quadratic)
                    const finalSpeed = speedRatio * MAX_SPEED;

                    // INVERTED Joystick X here (Flip sign of normX)
                    targetMoveX = -normX * finalSpeed;
                    targetMoveZ = -normY * finalSpeed;

                    document.getElementById('tel-raw').innerText = `RAW: Mov | Dist:${distance.toFixed(0)}`;
                } else {
                    telJ.innerText = "MOVE: HOLD";
                    telJ.classList.replace('text-white', 'text-gray-500');
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
            gunContainer.position.z += 0.15;
            gunContainer.rotation.x += 0.1;
            muzzleLight.intensity = 2.0;

            document.getElementById('crosshair').classList.add('firing');

            fetch('/api/haptics/fire', {
                method: 'POST'
            }).catch(e => console.log('Haptic API Error', e));

            setTimeout(() => {
                gunContainer.position.z -= 0.15;
                gunContainer.rotation.x -= 0.1;
                muzzleLight.intensity = 0.0;

                document.getElementById('crosshair').classList.remove('firing');
            }, 80);

            raycaster.setFromCamera(new THREE.Vector2(0, 0), camera);
            const hits = raycaster.intersectObjects(targets, true);

            if (hits.length > 0) {
                let targetHit = hits[0].object;
                while (targetHit.parent && targetHit.parent.type !== 'Scene') {
                    targetHit = targetHit.parent;
                }
                scene.remove(targetHit);
                targets = targets.filter(t => t !== targetHit);
                score += 1;
                document.getElementById('score-val').innerText = score.toString().padStart(3, '0');
                if (targets.length < 3) spawnEnemies(5);
            }
        }

        function setupRealtime() {
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: ENV.key,
                wsHost: ENV.host,
                wsPort: ENV.port,
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.connector.pusher.connection.bind('connected', () => {
                const indicator = document.getElementById('status-indicator');
                const statusText = document.getElementById('connection-text');
                if (!statusText.innerText.includes('RX')) {
                    indicator.className = 'w-3 h-3 rounded-full bg-yellow-500 shadow-[0_0_10px_#eab308]';
                    statusText.innerText = "LINK ESTABLISHED (WAITING)";
                    statusText.className = 'text-[10px] uppercase font-bold text-yellow-500';
                }
            });

            window.Echo.channel('game-controls')
                .listen('.data.received', (e) => {
                    handleIoTData(e.data);
                });
        }

        function animate() {
            requestAnimationFrame(animate);

            // --- RENDER INTERPOLATION ---
            camera.rotation.x += (targetPitch - camera.rotation.x) * LERP_FACTOR;
            playerGroup.rotation.y += (targetYaw - playerGroup.rotation.y) * LERP_FACTOR;

            if (targetMoveX !== 0 || targetMoveZ !== 0) {
                playerGroup.translateX(targetMoveX);
                playerGroup.translateZ(targetMoveZ);
            }

            const time = Date.now() * 0.001;
            targets.forEach(t => {
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
