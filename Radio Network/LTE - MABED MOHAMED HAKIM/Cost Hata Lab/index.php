<?php

$freq       = $_POST['frequency'] ?? 2600;
$tx_power   = $_POST['power'] ?? 23;
$distance   = $_POST['distance'] ?? 1.0;
$ant_height = $_POST['height'] ?? 30;
$env        = $_POST['environment'] ?? 'urban_dense';

$results = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $f = floatval($_POST['frequency']);
    $p_tx = floatval($_POST['power']);
    $d = floatval($_POST['distance']);
    $h_b = floatval($_POST['height']);
    $env_type = $_POST['environment'];
    $h_m = 1.5;

    if ($f < 2550 || $f > 2650) {
        $error = "Frequency must be between 2550 and 2650 MHz.";
    } elseif ($h_b < 10 || $h_b > 40) {
        $error = "Antenna height must be between 10m and 40m.";
    } elseif ($d <= 0) {
        $error = "Distance must be greater than 0.";
    } else {
        $a_hm = (1.1 * log10($f) - 0.7) * $h_m - (1.56 * log10($f) - 0.8);

        $term1 = 46.3;
        $term2 = 33.9 * log10($f);
        $term3 = 13.82 * log10($h_b);
        $term4 = $a_hm;
        $term5 = (44.9 - 6.55 * log10($h_b)) * log10($d);

        $path_loss_basic = $term1 + $term2 - $term3 - $term4 + $term5;

        $cm_val = 0;
        $env_display = "";

        if ($env_type == 'urban_dense') {
            $cm_val = 3;
            $path_loss_final = $path_loss_basic + $cm_val;
            $env_display = "Dense Urban (+3dB)";
        } else {
            $cm_val = 0;
            $path_loss_final = $path_loss_basic + $cm_val;
            $env_display = "Suburban (Standard)";
        }

        $rx_power = $p_tx - $path_loss_final;

        $results = [
            'p_tx' => $p_tx,
            'loss' => number_format($path_loss_final, 2),
            'rx_power' => number_format($rx_power, 2),
            'env_display' => $env_display,
            'raw_formula' => "Cost-231 Hata"
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 2: Uplink Simulator</title>
    <style>
        body {
            background-color: #0d1117;
            /* Dark Terminal Background */
            font-family: "Courier New", Courier, monospace;
            color: #00ff41;
            /* Hacker Green */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .terminal-window {
            width: 600px;
            border: 2px solid #00ff41;
            background-color: #000000;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
            padding: 5px;
        }

        .header {
            border-bottom: 1px solid #00ff41;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .content {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            border-left: 2px solid #003b00;
            padding-left: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            opacity: 0.8;
        }

        input,
        select {
            width: 100%;
            background-color: #0d1117;
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 8px;
            font-family: "Courier New", monospace;
            box-sizing: border-box;
        }

        input:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 5px #00ff41;
        }

        button {
            width: 100%;
            background-color: #003b00;
            color: #00ff41;
            border: 1px solid #00ff41;
            padding: 10px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        button:hover {
            background-color: #00ff41;
            color: black;
        }

        .output-screen {
            margin-top: 20px;
            border: 1px dashed #00ff41;
            padding: 15px;
            background-color: #050505;
        }

        .data-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .value {
            font-weight: bold;
        }

        .error-msg {
            color: #ff3333;
            border: 1px solid #ff3333;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
            opacity: 0.5;
        }
    </style>
</head>

<body>

    <div class="terminal-window">
        <div class="header">
            SYSTEM: LAB_02_UPLINK_SIM
        </div>

        <div class="content">
            <form method="POST">
                <div class="form-group">
                    <label>> SENSOR DISTANCE (km):</label>
                    <input type="number" name="distance" step="0.01" value="<?php echo $distance; ?>" required>
                </div>

                <div class="form-group">
                    <label>> FREQUENCY (2550 - 2650 MHz):</label>
                    <input type="number" name="frequency" step="1" min="2550" max="2650" value="<?php echo $freq; ?>" required>
                </div>

                <div class="form-group">
                    <label>> ANTENNA HEIGHT (10 - 40 m):</label>
                    <input type="number" name="height" step="1" min="10" max="40" value="<?php echo $ant_height; ?>" required>
                </div>

                <div class="form-group">
                    <label>> SENSOR TX POWER (dBm):</label>
                    <select name="power">
                        <option value="20" <?php if ($tx_power == 20) echo 'selected'; ?>>20 dBm (Standard)</option>
                        <option value="23" <?php if ($tx_power == 23) echo 'selected'; ?>>23 dBm (High Power)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>> ENVIRONMENT TYPE:</label>
                    <select name="environment">
                        <option value="urban_dense" <?php if ($env == 'urban_dense') echo 'selected'; ?>>Dense Urban (High Loss)</option>
                        <option value="suburban" <?php if ($env == 'suburban') echo 'selected'; ?>>Suburban (Open Area)</option>
                    </select>
                </div>

                <button type="submit">[ EXECUTE SIMULATION ]</button>
            </form>

            <?php if ($error): ?>
                <div class="error-msg">
                    ERROR: <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($results): ?>
                <div class="output-screen">
                    <div style="text-align:center; margin-bottom:10px; border-bottom:1px solid #00ff41;">>> SIMULATION REPORT </div>

                    <div class="data-line">
                        <span>MODEL USED:</span>
                        <span class="value"><?php echo $results['raw_formula']; ?></span>
                    </div>
                    <div class="data-line">
                        <span>ENVIRONMENT:</span>
                        <span class="value"><?php echo $results['env_display']; ?></span>
                    </div>
                    <div class="data-line">
                        <span>PATH LOSS:</span>
                        <span class="value"><?php echo $results['loss']; ?> dB</span>
                    </div>
                    <div class="data-line">
                        <span>TX POWER (Sensor):</span>
                        <span class="value"><?php echo $results['p_tx']; ?> dBm</span>
                    </div>

                    <br>
                    <div style="text-align: center; border: 1px solid #00ff41; padding: 10px;">
                        RECEIVED POWER (Uplink):<br>
                        <span style="font-size: 24px; font-weight: bold;"><?php echo $results['rx_power']; ?> dBm</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="footer">
                Module: Cost_Hata_v2.1 | Secure Connection
            </div>
        </div>
    </div>

</body>

</html>