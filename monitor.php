<?php
// monitor.php

$threshold = 50; // Threshold for requests per IP in the time window
$timeWindow = 10; // Time window in seconds to monitor recent requests

// Database connection
$host = 'localhost';
$db = 'siem';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch selected target log file path
$targetId = $_GET['target_id'];
$stmt = $pdo->prepare("SELECT log_path FROM monitored_targets WHERE id = ?");
$stmt->execute([$targetId]);
$target = $stmt->fetch(PDO::FETCH_ASSOC);

if ($target && file_exists($target['log_path'])) {
    $logs = file($target['log_path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $currentTime = time();
    $ipRequestCounts = [];

    foreach ($logs as $logEntry) {
        // Assuming each log entry has a format like "IP - - [timestamp] ... request details"
        preg_match('/^(\S+) - - \[(\d+\/\w+\/\d+:\d+:\d+:\d+)/', $logEntry, $matches);
        $ip = $matches[1];
        $logTimestamp = strtotime($matches[2]);

        if ($currentTime - $logTimestamp <= $timeWindow) {
            if (!isset($ipRequestCounts[$ip])) {
                $ipRequestCounts[$ip] = 0;
            }
            $ipRequestCounts[$ip]++;
        }
    }

    $suspiciousIps = [];
    foreach ($ipRequestCounts as $ip => $count) {
        if ($count > $threshold) {
            $suspiciousIps[] = $ip;
        }
    }

    echo json_encode([
        'alert' => !empty($suspiciousIps),
        'suspiciousIps' => $suspiciousIps,
    ]);
} else {
    echo json_encode([
        'alert' => false,
        'suspiciousIps' => []
    ]);
}
?>
