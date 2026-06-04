<?php
$url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';

if ($url && preg_match('#mysql://([^:]+):([^@]*)@([^:/]+):?(\d*)/([^?\s]+)#', $url, $m)) {
    // Regex finds mysql:// even if something is prepended (e.g. "railwaymysql://")
    $user = urldecode($m[1]);
    $pass = urldecode($m[2]);
    $host = $m[3];
    $port = (int)($m[4] ?: 3306);
    $db   = $m[5];
} else {
    // Local XAMPP fallback
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'aum_task_system';
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;color:#970000;text-align:center;">
        <h3>Database Connection Failed</h3>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Host: ' . htmlspecialchars($host) . ' | DB: ' . htmlspecialchars($db) . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');
