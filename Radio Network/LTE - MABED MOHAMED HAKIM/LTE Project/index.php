<?php

// Map Bandwidth (MHz) to Resource Blocks (RBs)
$bw_to_rb = [
    "1.4" => 6,
    "3"   => 15,
    "5"   => 25,
    "10"  => 50,
    "15"  => 75,
    "20"  => 100
];

// CQI Table
$cqi_table = [
    0  => ['snr' => 0,    'eff' => 0.67, 'mod' => 'QPSK',   'rate' => '1/3'],
    1  => ['snr' => 1.5,  'eff' => 1.00, 'mod' => 'QPSK',   'rate' => '1/2'],
    2  => ['snr' => 4.0,  'eff' => 1.30, 'mod' => 'QPSK',   'rate' => '2/3'],
    3  => ['snr' => 5.0,  'eff' => 1.50, 'mod' => 'QPSK',   'rate' => '3/4'],
    4  => ['snr' => 5.5,  'eff' => 1.60, 'mod' => 'QPSK',   'rate' => '4/5'],
    5  => ['snr' => 7.0,  'eff' => 2.00, 'mod' => '16QAM',  'rate' => '1/2'],
    6  => ['snr' => 10.0, 'eff' => 2.67, 'mod' => '16QAM',  'rate' => '2/3'],
    7  => ['snr' => 11.5, 'eff' => 3.00, 'mod' => '16QAM',  'rate' => '3/4'],
    8  => ['snr' => 13.0, 'eff' => 3.20, 'mod' => '16QAM',  'rate' => '4/5'],
    9  => ['snr' => 15.0, 'eff' => 4.00, 'mod' => '64QAM',  'rate' => '2/3'],
    10 => ['snr' => 17.0, 'eff' => 4.50, 'mod' => '64QAM',  'rate' => '3/4'],
    11 => ['snr' => 18.5, 'eff' => 4.80, 'mod' => '64QAM',  'rate' => '4/5'],
    12 => ['snr' => 20.0, 'eff' => 5.33, 'mod' => '256QAM', 'rate' => '2/3'],
    13 => ['snr' => 22.0, 'eff' => 6.00, 'mod' => '256QAM', 'rate' => '3/4'],
    14 => ['snr' => 24.0, 'eff' => 6.40, 'mod' => '256QAM', 'rate' => '4/5'],
    15 => ['snr' => 27.0, 'eff' => 7.00, 'mod' => '256QAM', 'rate' => '7/8'],
];

$form_bw        = $_POST['bandwidth'] ?? "10";
$form_freq      = $_POST['frequency'] ?? 2600;
$form_power     = $_POST['power'] ?? 43;
$form_dist      = $_POST['distance'] ?? 0.3;
$form_cp        = $_POST['cp_mode'] ?? 'extended';

$results = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Inputs
    $bw_key = $_POST['bandwidth'];
    $freq_mhz = floatval($_POST['frequency']);
    $distance_km = floatval($_POST['distance']);
    $power_dbm = floatval($_POST['power']);
    $cp_mode = $_POST['cp_mode'];

    $noise_interference = -100;
    $quality_threshold = -90;

    if ($distance_km > 0 && $freq_mhz > 0) {
        $path_loss = 20 * log10($distance_km) + 20 * log10($freq_mhz) + 32.45;
        $rx_power = $power_dbm - $path_loss;

        if ($rx_power < $quality_threshold) {
            $error = "CONNECTION ERROR: Signal too weak (" . round($rx_power, 2) . " dBm)";
        } else {
            $snr = $rx_power - $noise_interference;

            $selected_cqi = -1;
            $efficiency = 0;
            $modulation = "N/A";

            foreach ($cqi_table as $index => $data) {
                if ($snr >= $data['snr']) {
                    $selected_cqi = $index;
                    $efficiency = $data['eff'];
                    $modulation = $data['mod'];
                }
            }

            if ($selected_cqi == -1) {
                $error = "LINK FAILURE: SNR insufficient.";
            } else {
                $rbs = isset($bw_to_rb[$bw_key]) ? $bw_to_rb[$bw_key] : 50;
                $symbols_per_slot = ($cp_mode == 'extended') ? 6 : 7;
                $res_per_subframe = $rbs * 12 * ($symbols_per_slot * 2);
                $throughput_bps = ($res_per_subframe * 1000) * $efficiency;
                $throughput_mbps = $throughput_bps / 1000000;

                $results = [
                    'loss' => number_format($path_loss, 2),
                    'rx_power' => number_format($rx_power, 2),
                    'snr' => number_format($snr, 2),
                    'cqi' => $selected_cqi,
                    'mod' => $modulation,
                    'eff' => $efficiency,
                    'throughput' => number_format($throughput_mbps, 2)
                ];
            }
        }
    } else {
        $error = "INPUT ERROR: Check numeric values.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LTE Calculator v1.0</title>
    <style>
        /* 2000s "Windows 98/2000" Aesthetic */
        body {
            background-color: #008080;
            font-family: "Verdana", sans-serif;
            font-size: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .window {
            background-color: #c0c0c0;
            /* Standard Window Gray */
            width: 550px;
            border-top: 2px solid #dfdfdf;
            border-left: 2px solid #dfdfdf;
            border-right: 2px solid #000000;
            border-bottom: 2px solid #000000;
            box-shadow: 4px 4px 0px rgba(0, 0, 0, 0.5);
            padding: 2px;
        }

        .title-bar {
            background: linear-gradient(to right, #000080, #1084d0);
            /* Classic Blue Gradient */
            padding: 4px;
            color: white;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: "Tahoma", sans-serif;
            letter-spacing: 1px;
        }

        .title-bar-text {
            margin-left: 4px;
        }

        .title-bar-controls button {
            background-color: #c0c0c0;
            border-top: 1px solid #dfdfdf;
            border-left: 1px solid #dfdfdf;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
            font-weight: bold;
            font-size: 10px;
            width: 16px;
            height: 14px;
            line-height: 10px;
            margin-left: 2px;
        }

        .content {
            padding: 15px;
            border: 1px solid #808080;
            /* Inset look */
            margin-top: 2px;
        }

        h1 {
            font-family: "Times New Roman", serif;
            text-align: center;
            font-size: 24px;
            margin-top: 0;
            text-decoration: underline;
        }

        fieldset {
            border: 2px groove #dfdfdf;
            padding: 10px;
            margin-bottom: 15px;
        }

        legend {
            font-weight: bold;
            padding: 0 5px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            align-items: center;
        }

        label {
            width: 40%;
        }

        input,
        select {
            font-family: "Courier New", monospace;
            width: 55%;
            background-color: #ffffff;
            border-top: 2px solid #000000;
            border-left: 2px solid #000000;
            border-right: 2px solid #dfdfdf;
            border-bottom: 2px solid #dfdfdf;
            padding: 2px;
        }

        button.calc-btn {
            width: 100%;
            padding: 5px;
            background-color: #c0c0c0;
            border-top: 2px solid #dfdfdf;
            border-left: 2px solid #dfdfdf;
            border-right: 2px solid #000000;
            border-bottom: 2px solid #000000;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        button.calc-btn:active {
            border-top: 2px solid #000000;
            border-left: 2px solid #000000;
            border-right: 2px solid #dfdfdf;
            border-bottom: 2px solid #dfdfdf;
            padding-top: 6px;
            /* Shift text down */
            padding-left: 6px;
            /* Shift text right */
        }

        .result-box,
        .error-box {
            border: 2px inset #ffffff;
            background-color: #ffffff;
            padding: 10px;
            margin-top: 10px;
            font-family: "Courier New", monospace;
        }

        .error-box {
            color: red;
            background-color: #ffe0e0;
        }

        table.results-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.results-table td {
            padding: 2px;
        }

        .highlight {
            font-weight: bold;
            color: blue;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>

<body>

    <div class="window">
        <div class="title-bar">
            <span class="title-bar-text">LTE_Calculator.exe</span>
            <div class="title-bar-controls">
                <button>_</button>
                <button>X</button>
            </div>
        </div>

        <div class="content">
            <h1>LTE Throughput Wizard</h1>

            <form method="POST">
                <fieldset>
                    <legend>Network Parameters</legend>

                    <div class="form-row">
                        <label>Bandwidth (MHz):</label>
                        <select name="bandwidth">
                            <option value="1.4" <?php if ($form_bw == "1.4") echo 'selected'; ?>>1.4 MHz (6 RBs)</option>
                            <option value="3" <?php if ($form_bw == "3") echo 'selected'; ?>>3 MHz (15 RBs)</option>
                            <option value="5" <?php if ($form_bw == "5") echo 'selected'; ?>>5 MHz (25 RBs)</option>
                            <option value="10" <?php if ($form_bw == "10") echo 'selected'; ?>>10 MHz (50 RBs)</option>
                            <option value="15" <?php if ($form_bw == "15") echo 'selected'; ?>>15 MHz (75 RBs)</option>
                            <option value="20" <?php if ($form_bw == "20") echo 'selected'; ?>>20 MHz (100 RBs)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Frequency (MHz):</label>
                        <input type="number" name="frequency" value="<?php echo htmlspecialchars($form_freq); ?>" required>
                    </div>

                    <div class="form-row">
                        <label>Tx Power (dBm):</label>
                        <input type="number" name="power" value="<?php echo htmlspecialchars($form_power); ?>" step="0.1" required>
                    </div>

                    <div class="form-row">
                        <label>Distance (km):</label>
                        <input type="number" name="distance" value="<?php echo htmlspecialchars($form_dist); ?>" step="0.01" required>
                    </div>

                    <div class="form-row">
                        <label>Cyclic Prefix:</label>
                        <select name="cp_mode">
                            <option value="normal" <?php if ($form_cp == "normal") echo 'selected'; ?>>Normal</option>
                            <option value="extended" <?php if ($form_cp == "extended") echo 'selected'; ?>>Extended</option>
                        </select>
                    </div>
                </fieldset>

                <button type="submit" class="calc-btn"> COMPUTE THROUGHPUT </button>
            </form>

            <?php if ($error): ?>
                <div class="error-box">
                    [!] <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($results): ?>
                <div class="result-box">
                    <p style="text-align:center; border-bottom: 1px dashed black; padding-bottom: 5px; margin-top:0;">CALCULATION REPORT</p>
                    <table class="results-table">
                        <tr>
                            <td>Path Loss:</td>
                            <td><?php echo $results['loss']; ?> dB</td>
                        </tr>
                        <tr>
                            <td>Rx Power:</td>
                            <td><?php echo $results['rx_power']; ?> dBm</td>
                        </tr>
                        <tr>
                            <td>SINR:</td>
                            <td><?php echo $results['snr']; ?> dB</td>
                        </tr>
                        <tr>
                            <td>CQI Index:</td>
                            <td><?php echo $results['cqi']; ?></td>
                        </tr>
                        <tr>
                            <td>Modulation:</td>
                            <td><?php echo $results['mod']; ?></td>
                        </tr>
                        <tr>
                            <td>Efficiency:</td>
                            <td><?php echo $results['eff']; ?> bits/sym</td>
                        </tr>
                    </table>
                    <div style="margin-top: 10px; border: 2px solid black; padding: 5px; text-align: center; background: #e0e0e0;">
                        TP: <span class="highlight"><?php echo $results['throughput']; ?> Mbps</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="footer">
                &copy; 2026 University Project | Best viewed in Netscape 4.0
            </div>
        </div>
    </div>

</body>

</html>