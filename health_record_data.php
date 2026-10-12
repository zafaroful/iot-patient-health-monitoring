<?php
// Backend for the dashboard. It keeps the Blynk token on the server,
// reads the latest sensor values from Blynk and logs them.
header('Content-Type: application/json');
header('Cache-Control: no-store');

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'config.php missing (copy config.example.php)']);
    exit;
}
$cfg     = require $configFile;
$logFile = __DIR__ . '/health_data.log';

// Optional: Blynk webhook can still POST data here. It is logged as before.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents($logFile, date('c') . ' ' . json_encode($data) . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo json_encode(['ok' => true, 'message' => 'Data received successfully']);
    exit;
}

// GET: fetch the latest values from Blynk
$query = 'token=' . urlencode($cfg['blynk_token']);
foreach ($cfg['pins'] as $pin) {
    $query .= '&' . urlencode($pin);
}
$url = 'https://' . $cfg['blynk_host'] . '/external/api/get?' . $query;

$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
$body   = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false || $status !== 200) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Could not read from Blynk']);
    exit;
}

$raw = json_decode($body, true) ?: [];
$out = ['ok' => true, 'time' => date('c')];
foreach ($cfg['pins'] as $name => $pin) {
    $out[$name] = $raw[$pin] ?? null;
}

// Log at most once per log_interval seconds
$due = !file_exists($logFile) || (time() - filemtime($logFile)) >= $cfg['log_interval'];
if ($due) {
    file_put_contents($logFile, json_encode($out) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

echo json_encode($out);
