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

        /* New Styles for Game Overlays */
        .overlay-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .input-cyber {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #4b5563;
            color: #f59e0b;
            font-family: 'Roboto Mono', monospace;
            padding: 0.5rem 1rem;
            outline: none;
            text-align: center;
            transition: all 0.3s;
        }

        .input-cyber:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.3);
        }

        .btn-cyber {
            background: #f59e0b;
            color: black;
            font-family: 'Allerta Stencil', sans-serif;
            border: none;
            padding: 0.75rem 2rem;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 2px;
            transition: all 0.2s;
            text-transform: uppercase;
        }

        .btn-cyber:hover {
            background: #ffffff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }
    </style>
</head>

<body class="antialiased">

    <!-- START SCREEN -->
    <div id="start-screen" class="overlay-screen">
        <h1 class="text-6xl ui-font text-white mb-2 tracking-tighter">TACTICAL <span class="text-amber-500">LINK</span>
        </h1>
        <p class="text-gray-400 mb-8 font-mono text-sm">IoT SHOOTER</p>

        <div class="flex flex-col gap-4 items-center w-64">
            <input type="text" id="player-name-input" class="input-cyber w-full uppercase"
                placeholder="OPERATOR NAME" maxlength="15">
            <button onclick="startGame()" class="btn-cyber w-full">INITIATE MISSION</button>
        </div>

        <div class="mt-12 text-center">
            <h3 class="ui-font text-gray-500 text-xs tracking-widest mb-4">TOP OPERATORS</h3>
            <div id="leaderboard-start" class="text-xs font-mono text-gray-400 space-y-2">
                <!-- Leaderboard populated via JS -->
                <p>LOADING DATABASE...</p>
            </div>
        </div>
    </div>

    <!-- GAME OVER SCREEN -->
    <div id="game-over-screen" class="overlay-screen hidden">
        <h1 class="text-5xl ui-font text-white mb-2">MISSION COMPLETE</h1>
        <div class="text-6xl font-mono text-amber-500 font-bold mb-8" id="final-time-display">00.00s</div>

        <button onclick="resetGame()" class="btn-cyber mb-12">REDEPLOY</button>

        <div class="w-64">
            <h3 class="ui-font text-gray-500 text-xs tracking-widest mb-4 text-center">LEADERBOARD</h3>
            <div id="leaderboard-end" class="text-xs font-mono text-gray-400 space-y-2 text-center">
                <!-- Leaderboard populated via JS -->
            </div>
        </div>
    </div>

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

            <!-- TIMER & TARGETS -->
            <div class="flex gap-4">
                <div class="hud-panel p-4 rounded-sm text-center w-32">
                    <h1 class="ui-font hud-text-amber text-xs tracking-widest uppercase">TIME</h1>
                    <p id="timer-val" class="text-3xl text-white font-bold tracking-tighter">00.00</p>
                </div>
                <div class="hud-panel p-4 rounded-sm text-right w-32">
                    <h1 class="ui-font hud-text-amber text-xs tracking-widest uppercase">TARGETS</h1>
                    <p id="score-val" class="text-3xl text-white font-bold tracking-tighter">10</p>
                </div>
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

        const JOY_DEADZONE = 500;
        const MAX_SPEED = 0.25;

        // --- LOW LATENCY SMOOTHING CONFIG ---
        const HISTORY_SIZE = 2;
        let pHistory = [];
        let yHistory = [];
        const WEIGHTS = [0.3, 0.7];
        const DRIFT_GATE_P = 0.02;
        const DRIFT_GATE_Y = 0.1;
        const LERP_FACTOR = 0.25;

        /**
         * 2. THREE.JS ENGINE SETUP
         */
        let scene, camera, renderer, raycaster, gunContainer, muzzleLight;
        let targets = [];

        // Collision Arrays
        let obstacles = [];
        let obstacleBoxes = [];
        let playerBox = new THREE.Box3();

        let score = 0;
        let lastF = 0;
        let lastC = 0; // Last Recenter State

        // --- SMOOTHING STATE VARIABLES ---
        let targetPitch = 0;
        let targetYaw = 0;
        let targetMoveX = 0;
        let targetMoveZ = 0;

        // Sensor State
        let lastRegisteredP = 0;
        let lastRegisteredY = 0;

        // --- GAME STATE VARIABLES ---
        let gameActive = false;
        let playerName = "UNKNOWN";
        let startTime = 0;
        let elapsedTime = 0;
        const TOTAL_TARGETS = 10;
        let targetsRemaining = TOTAL_TARGETS;

        // --- API FUNCTIONS ---
        async function fetchLeaderboard() {
            try {
                const res = await fetch('/api/scores');
                const scores = await res.json();

                const html = scores.map((s, i) =>
                    `<div class="flex justify-between w-full"><span>#${i+1} ${s.player_name}</span><span>${Number(s.time_taken).toFixed(2)}s</span></div>`
                ).join('');

                document.getElementById('leaderboard-start').innerHTML = html || "<p>NO RECORDS FOUND</p>";
                document.getElementById('leaderboard-end').innerHTML = html || "<p>NO RECORDS FOUND</p>";
            } catch (e) {
                console.error("Leaderboard error", e);
            }
        }

        async function submitScore(name, time) {
            try {
                const response = await fetch('/api/scores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        player_name: name,
                        time_taken: time
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error("Server validation error", errorData);
                }

                await fetchLeaderboard();
            } catch (e) {
                console.error("Score submit error", e);
            }
        }

        // --- GAME FLOW CONTROL ---
        function startGame() {
            const input = document.getElementById('player-name-input');
            if (!input.value.trim()) {
                input.style.borderColor = "red";
                return;
            }
            playerName = input.value.trim().toUpperCase();

            // Generate targets
            targets.forEach(t => scene.remove(t));
            targets = [];
            spawnEnemies(TOTAL_TARGETS);

            // Reset UI
            targetsRemaining = TOTAL_TARGETS;
            document.getElementById('score-val').innerText = targetsRemaining;
            document.getElementById('start-screen').classList.add('hidden');
            document.getElementById('game-over-screen').classList.add('hidden');

            // Start Timer
            startTime = Date.now();
            gameActive = true;
        }

        function endGame() {
            gameActive = false;
            elapsedTime = (Date.now() - startTime) / 1000;

            document.getElementById('final-time-display').innerText = elapsedTime.toFixed(2) + "s";
            document.getElementById('game-over-screen').classList.remove('hidden');

            submitScore(playerName, elapsedTime);
        }

        function resetGame() {
            document.getElementById('game-over-screen').classList.add('hidden');
            document.getElementById('start-screen').classList.remove('hidden');
            fetchLeaderboard();
        }

        function initScene() {
            scene = new THREE.Scene();
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

            const sunLight = new THREE.DirectionalLight(0xfffaed, 1.2);
            sunLight.position.set(50, 100, 50);
            sunLight.castShadow = true;
            sunLight.shadow.mapSize.width = 2048;
            sunLight.shadow.mapSize.height = 2048;
            scene.add(sunLight);

            muzzleLight = new THREE.PointLight(0xffaa33, 0, 8);
            muzzleLight.position.set(0.3, -0.2, -1.5);
            camera.add(muzzleLight);

            // -- ENVIRONMENT --
            createEnvironment();
            createGunModel();

            raycaster = new THREE.Raycaster();
            // spawnEnemies(10); // Moved to startGame()
            animate();
        }

        function createEnvironment() {
            const planeGeo = new THREE.PlaneGeometry(200, 200);
            const planeMat = new THREE.MeshStandardMaterial({
                color: 0x5d5b50,
                roughness: 1.0,
                metalness: 0.0
            });
            const floor = new THREE.Mesh(planeGeo, planeMat);
            floor.rotation.x = -Math.PI / 2;
            floor.receiveShadow = true;
            scene.add(floor);

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

                mesh.updateMatrixWorld();
                const box = new THREE.Box3().setFromObject(mesh);
                scene.add(mesh);
                obstacles.push(mesh);
                obstacleBoxes.push(box);
            }
        }

        function createGunModel() {
            gunContainer = new THREE.Group();

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

            const receiver = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.12, 0.4), matGunmetal);
            const stock = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.14, 0.35), matWood);
            stock.position.set(0, -0.05, 0.35);
            const barrel = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.015, 0.6), matSteel);
            barrel.rotation.x = Math.PI / 2;
            barrel.position.set(0, 0.03, -0.4);
            const handguard = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.07, 0.3), matWood);
            handguard.position.set(0, 0, -0.3);
            const mag = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.25, 0.08), matGunmetal);
            mag.rotation.x = 0.3;
            mag.position.set(0, -0.15, -0.05);
            const sight = new THREE.Mesh(new THREE.BoxGeometry(0.01, 0.04, 0.01), matSteel);
            sight.position.set(0, 0.06, -0.68);

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
                    isTarget: true,
                    floatSpeed: 0.01 + Math.random() * 0.02,
                    floatOffset: Math.random() * Math.PI * 2
                };

                targets.push(targetGroup);
                scene.add(targetGroup);
            }
        }

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

            // New Buttons
            const hasRecenter = data.C !== undefined;
            const hasFire = data.F !== undefined;

            // --- 1. One-Time Calibration (On Connect) ---
            if (!isCalibrated && hasJoy && hasYaw) {
                JOY_CENTER_X = Number(data.JX);
                JOY_CENTER_Y = Number(data.JY);
                yawOffset = Number(data.Y);
                pitchOffset = Number(data.P);

                pHistory = Array(HISTORY_SIZE).fill(Number(data.P));
                yHistory = Array(HISTORY_SIZE).fill(Number(data.Y));

                lastRegisteredP = Number(data.P);
                lastRegisteredY = Number(data.Y);

                isCalibrated = true;
                console.log("Calibrated. Yaw Offset:", yawOffset);
            }

            const indicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('connection-text');
            if (!indicator.classList.contains('bg-green-500')) {
                indicator.className = 'w-3 h-3 rounded-full bg-green-500 shadow-[0_0_10px_#22c55e]';
                statusText.innerText = "UPLINK SECURE";
                statusText.className = 'text-[10px] uppercase font-bold text-green-500';
            }

            // --- 2. UPDATE TARGETS (Weighted Moving Average) ---

            if (hasPitch) {
                const rawP = Number(data.P);
                pHistory.push(rawP);
                if (pHistory.length > HISTORY_SIZE) pHistory.shift();

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

                    // INVERTED Joystick X here (Added negative sign)
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

            // --- 4. BUTTONS ---

            // Recenter (C)
            if (hasRecenter) {
                const C = Number(data.C);
                if (C === 1 && lastC === 0) {
                    // Update offsets to match current raw values
                    if (data.P !== undefined) pitchOffset = Number(data.P);
                    if (data.Y !== undefined) yawOffset = Number(data.Y);

                    // Prevent jump by resetting history
                    pHistory.fill(pitchOffset);
                    yHistory.fill(yawOffset);

                    document.getElementById('tel-raw').innerText = "SYSTEM: RECALIBRATED";
                }
                lastC = C;
            }

            // Fire (F)
            if (hasFire) {
                const F = Number(data.F);
                if (F === 1 && lastF === 0) {
                    triggerFire();
                }
                lastF = F;
            }
        }

        function triggerFire() {
            if (!gameActive) return;

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
            const allIntersectables = [...targets, ...obstacles];
            const hits = raycaster.intersectObjects(allIntersectables, true);

            if (hits.length > 0) {
                let hitObj = hits[0].object;
                let rootObj = hitObj;
                while (rootObj.parent && rootObj.parent.type !== 'Scene') {
                    if (rootObj.userData.isTarget) break;
                    rootObj = rootObj.parent;
                }

                if (rootObj.userData.isTarget) {
                    scene.remove(rootObj);
                    targets = targets.filter(t => t !== rootObj);

                    // Game Logic Update
                    if (gameActive) {
                        targetsRemaining--;
                        document.getElementById('score-val').innerText = targetsRemaining;

                        if (targetsRemaining <= 0) {
                            endGame();
                        }
                    }
                } else {
                    const sparkGeo = new THREE.BoxGeometry(0.1, 0.1, 0.1);
                    const sparkMat = new THREE.MeshBasicMaterial({
                        color: 0xffff00
                    });
                    const spark = new THREE.Mesh(sparkGeo, sparkMat);
                    spark.position.copy(hits[0].point);
                    scene.add(spark);
                    setTimeout(() => scene.remove(spark), 200);
                }
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

            // --- TIMER LOGIC ---
            if (gameActive) {
                const now = Date.now();
                const delta = (now - startTime) / 1000;
                document.getElementById('timer-val').innerText = delta.toFixed(2);
            }

            // --- RENDER INTERPOLATION ---
            if (gameActive) {
                camera.rotation.x += (targetPitch - camera.rotation.x) * LERP_FACTOR;
                playerGroup.rotation.y += (targetYaw - playerGroup.rotation.y) * LERP_FACTOR;
            }

            if (gameActive && (targetMoveX !== 0 || targetMoveZ !== 0)) {
                const startPos = playerGroup.position.clone();
                playerGroup.translateX(targetMoveX);
                playerGroup.translateZ(targetMoveZ);

                const min = new THREE.Vector3(playerGroup.position.x - 0.5, playerGroup.position.y - 1, playerGroup.position
                    .z - 0.5);
                const max = new THREE.Vector3(playerGroup.position.x + 0.5, playerGroup.position.y + 1, playerGroup.position
                    .z + 0.5);
                playerBox.set(min, max);

                let collided = false;
                for (let box of obstacleBoxes) {
                    if (playerBox.intersectsBox(box)) {
                        collided = true;
                        break;
                    }
                }
                if (collided) {
                    playerGroup.position.copy(startPos);
                }
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
            fetchLeaderboard();
        };

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>

</html>
