<?php
$host = "0.0.0.0";
$port = 9000;

$server = stream_socket_server("tcp://$host:$port", $errno, $errstr);
if (!$server) {
    die("Error: $errstr ($errno)\n");
}

echo "TCP Server running on port $port...\n";

while ($client = @stream_socket_accept($server)) {
    $data = fread($client, 1024);
    if ($data) {
        echo "Received: $data\n";

        // Clean the data to fix common JSON issues (like trailing commas)
        $cleanData = trim($data);
        $cleanData = preg_replace('/,\s*}$/', '}', $cleanData); // Remove trailing comma before }
        $cleanData = preg_replace('/,\s*]$/', ']', $cleanData); // Remove trailing comma before ]

        // Decode JSON
        $decoded = json_decode($cleanData, true);
        if ($decoded) {
            file_put_contents("storage/logs/esp32.log", json_encode($decoded) . "\n", FILE_APPEND);
        } else {
            // Log failed parsing for debugging
            echo "JSON Error: " . json_last_error_msg() . " for data: $cleanData\n";
        }
    }
    fclose($client);
}
