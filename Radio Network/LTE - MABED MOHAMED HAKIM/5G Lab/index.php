<?php

$speed_map = [
    'motorway' => 110,
    'trunk' => 100,
    'primary' => 80,
    'secondary' => 60,
    'tertiary' => 45,
    'residential' => 30,
    'living_street' => 20,
    'unclassified' => 40
];

// Defaults (UMLP, Montbéliard, France)
$lat = $_POST['lat'] ?? 47.4953101;
$lon = $_POST['lon'] ?? 6.8044496;
$radius = $_POST['radius'] ?? 300;

$results = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $overpassUrl = "https://overpass-api.de/api/interpreter";
    $query = "[out:json][timeout:25];
    (
      way[\"highway\"](around:$radius,$lat,$lon);
      way[\"building\"](around:$radius,$lat,$lon);
    );
    out tags;";

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "User-Agent: 5G_Lab_Student_Project/1.0\r\n",
            'content' => "data=" . urlencode($query)
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($overpassUrl, false, $context);

    if ($response === FALSE) {
        $error = "Connection Lost: Cannot reach Overpass Server.";
    } else {
        $data = json_decode($response, true);

        $total_speed = 0;
        $road_count = 0;
        $building_count = 0;

        if (isset($data['elements'])) {
            foreach ($data['elements'] as $element) {
                $tags = $element['tags'];
                if (isset($tags['building'])) {
                    $building_count++;
                }
                if (isset($tags['highway']) && isset($speed_map[$tags['highway']])) {
                    $total_speed += $speed_map[$tags['highway']];
                    $road_count++;
                }
            }
        }

        $avg_speed = ($road_count > 0) ? round($total_speed / $road_count) : 0;

        if ($avg_speed > 90) {
            $scs = "120 kHz";
            $mobility_desc = "Elytra Flight";
        } elseif ($avg_speed > 50) {
            $scs = "60 kHz";
            $mobility_desc = "Minecart Speed";
        } else {
            $scs = "30 kHz";
            $mobility_desc = "Walking Speed";
        }

        if ($building_count > 150) {
            $band = "24 GHz";
            $scenario = "City"; // Dense
            $cp_mode = "Normal";
        } elseif ($building_count > 50) {
            $band = "3.5 GHz";
            $scenario = "Village"; // Moderate
            $cp_mode = "Normal";
        } else {
            $band = "700 MHz";
            $scenario = "Wilderness"; // Sparse
            $cp_mode = "Extended";
        }

        $results = [
            'buildings' => $building_count,
            'roads' => $road_count,
            'avg_speed' => $avg_speed,
            'scs' => $scs,
            'band' => $band,
            'cp' => $cp_mode,
            'scenario' => $scenario,
            'desc' => $mobility_desc
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crafting 5G Network</title>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* MINECRAFT / PIXEL THEME */
        body {
            background-color: #1a1a1a;
            background-image: repeating-linear-gradient(45deg, #2b2b2b 25%, transparent 25%, transparent 75%, #2b2b2b 75%, #2b2b2b), repeating-linear-gradient(45deg, #2b2b2b 25%, #1a1a1a 25%, #1a1a1a 75%, #2b2b2b 75%, #2b2b2b);
            background-position: 0 0, 10px 10px;
            background-size: 20px 20px;
            font-family: 'VT323', monospace;
            color: #fff;
            display: flex;
            justify-content: center;
            padding: 40px;
            margin: 0;
            min-height: 100vh;
        }

        /* Main Wrapper for 2-Column Layout */
        .wrapper {
            display: flex;
            gap: 20px;
            width: 1100px;
        }

        .container {
            flex: 1;
            /* Takes up remaining space */
            background-color: #c6c6c6;
            border: 4px solid #000;
            padding: 10px;
            box-shadow: 10px 10px 0px rgba(0, 0, 0, 0.5);
            image-rendering: pixelated;
        }

        .map-panel {
            flex: 1;
            /* Takes up equal space */
            background-color: #c6c6c6;
            border: 4px solid #000;
            padding: 5px;
            /* Inner padding like a picture frame */
            box-shadow: 10px 10px 0px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
        }

        h1 {
            text-align: center;
            color: #404040;
            font-size: 2.5rem;
            margin: 10px 0 20px 0;
            text-shadow: 2px 2px #fff;
        }

        /* Buttons & Inputs */
        .mc-btn {
            background-color: #7d7d7d;
            border-top: 4px solid #c6c6c6;
            border-left: 4px solid #c6c6c6;
            border-right: 4px solid #3b3b3b;
            border-bottom: 4px solid #3b3b3b;
            color: #fff;
            padding: 10px;
            width: 100%;
            font-family: 'VT323', monospace;
            font-size: 1.5rem;
            cursor: pointer;
            margin-top: 15px;
            text-shadow: 2px 2px #000;
        }

        .mc-btn:active {
            border-top: 4px solid #3b3b3b;
            border-left: 4px solid #3b3b3b;
            border-right: 4px solid #c6c6c6;
            border-bottom: 4px solid #c6c6c6;
            background-color: #595959;
        }

        .input-group label {
            color: #404040;
            font-size: 1.2rem;
            display: block;
            margin-bottom: 5px;
        }

        .mc-input {
            width: 100%;
            background-color: #000;
            border: 2px solid #808080;
            color: #fff;
            font-family: 'VT323', monospace;
            font-size: 1.3rem;
            padding: 8px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        /* Inventory Stats */
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
            background-color: #8b8b8b;
            padding: 10px;
            border: 2px solid #373737;
        }

        .slot {
            background-color: #8b8b8b;
            border-right: 2px solid #fff;
            border-bottom: 2px solid #fff;
            border-top: 2px solid #373737;
            border-left: 2px solid #373737;
            height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .slot:hover {
            background-color: #a0a0a0;
        }

        .slot-label {
            color: #e0e0e0;
            font-size: 1rem;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .slot-value {
            font-size: 1.6rem;
            color: #ffff55;
            text-shadow: 2px 2px #3f3f3f;
        }

        .advancement {
            background-color: #212121;
            border: 2px solid #fff;
            color: #fff;
            padding: 10px;
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border: 2px solid #000;
        }

        /* Map Styling */
        #map {
            width: 100%;
            height: 100%;
            border: 2px solid #373737;
            /* Dark border inside the frame */
            image-rendering: pixelated;
            /* Gives map tiles a slightly retro feel */
        }

        .map-title {
            text-align: center;
            color: #404040;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .loader {
            text-align: center;
            color: #404040;
            font-size: 1.5rem;
            margin-top: 10px;
            display: none;
        }

        .error-msg {
            background: #ff5555;
            color: #fff;
            padding: 10px;
            border: 2px solid #000;
            margin-top: 10px;
            text-align: center;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function craftNetwork() {
            document.getElementById('loader').style.display = 'block';
            document.getElementById('loader').innerText = "Generating chunks...";
        }
    </script>
</head>

<body>

    <div class="wrapper">
        <div class="container">
            <h1>Network Bench</h1>

            <form method="POST" onsubmit="craftNetwork()">
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <div class="input-group">
                            <label>Lat (X)</label>
                            <input type="text" name="lat" class="mc-input" value="<?php echo htmlspecialchars($lat); ?>">
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <div class="input-group">
                            <label>Lon (Z)</label>
                            <input type="text" name="lon" class="mc-input" value="<?php echo htmlspecialchars($lon); ?>">
                        </div>
                    </div>
                    <div style="flex: 0.8;">
                        <div class="input-group">
                            <label>Radius</label>
                            <input type="number" name="radius" class="mc-input" value="<?php echo htmlspecialchars($radius); ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="mc-btn">Craft Configuration</button>
                <div id="loader" class="loader"></div>
            </form>

            <?php if ($error): ?>
                <div class="error-msg">Error: <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($results): ?>
                <div style="margin-top: 20px;">
                    <div style="color: #404040; font-size: 1.5rem; margin-bottom: 5px;">Inventory:</div>

                    <div class="inventory-grid">
                        <div class="slot" title="<?php echo $results['desc']; ?>">
                            <div class="slot-label">Speed</div>
                            <div class="slot-value"><?php echo $results['avg_speed']; ?></div>
                        </div>
                        <div class="slot">
                            <div class="slot-label">Density</div>
                            <div class="slot-value"><?php echo $results['buildings']; ?></div>
                        </div>
                        <div class="slot" style="background-color: #555;">
                            <div class="slot-label">Biome</div>
                            <div style="font-size: 1rem; color: #fff; text-align:center;"><?php echo $results['scenario']; ?></div>
                        </div>
                        <div class="slot">
                            <div class="slot-label">Freq</div>
                            <div class="slot-value" style="color: #55ffff; font-size: 1.4rem;"><?php echo $results['band']; ?></div>
                        </div>
                        <div class="slot">
                            <div class="slot-label">SCS</div>
                            <div class="slot-value" style="color: #55ffff; font-size: 1.4rem;"><?php echo $results['scs']; ?></div>
                        </div>
                        <div class="slot">
                            <div class="slot-label">CP Mode</div>
                            <div class="slot-value" style="color: #55ffff; font-size: 1.2rem;"><?php echo $results['cp']; ?></div>
                        </div>
                    </div>

                    <div class="advancement">
                        <div class="icon-box" style="background-color: <?php echo ($results['band'] == '24 GHz') ? '#5c5c5c' : (($results['band'] == '3.5 GHz') ? '#795548' : '#4CAF50'); ?>;"></div>
                        <div>
                            <div style="color: #ffff55; margin-bottom: 2px;">Advancement Made!</div>
                            <div>Set: <?php echo $results['band']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="map-panel">
            <div class="map-title">World Map</div>
            <div id="map"></div>
        </div>
    </div>

    <script>
        // Initialize Map
        // We use PHP variables to set the center point
        var map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lon; ?>], 15);

        // Add Tile Layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Add Marker at Location
        L.marker([<?php echo $lat; ?>, <?php echo $lon; ?>]).addTo(map)
            .bindPopup("Selected Coordinates")
            .openPopup();

        // Add Circle for Radius (Analysis Area)
        var circle = L.circle([<?php echo $lat; ?>, <?php echo $lon; ?>], {
            color: '#ff0000', // Red outline
            fillColor: '#f03', // Red fill
            fillOpacity: 0.2,
            radius: <?php echo $radius; ?> // Radius in meters
        }).addTo(map);

        // Fit map bounds to show the whole circle
        map.fitBounds(circle.getBounds());
    </script>

</body>

</html>