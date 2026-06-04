<?php
// Railway provides MYSQL_URL with full connection details
$url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';

if ($url) {
    $p    = parse_url($url);
    $host = $p['host']                              ?? '127.0.0.1';
    $port = (int)($p['port']                        ?? 3306);
    $user = isset($p['user']) ? urldecode($p['user']) : 'root';
    $pass = isset($p['pass']) ? urldecode($p['pass']) : '';
    $db   = ltrim($p['path']                        ?? 'aum_task_system', '/');
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
