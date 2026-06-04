<?php
// Try full connection URL first (Railway provides MYSQL_URL or DATABASE_URL)
$mysql_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';

if ($mysql_url) {
    $p    = parse_url($mysql_url);
    $host = $p['host'] ?? '127.0.0.1';
    $port = (int)($p['port'] ?? 3306);
    $user = $p['user'] ?? 'root';
    $pass = $p['pass'] ?? '';
    $db   = ltrim($p['path'] ?? 'aum_task_system', '/');
} else {
    // Individual variables — try both naming conventions Railway uses
    $host = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: '127.0.0.1';
    $port = (int)(getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT')     ?: 3306);
    $user = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
    $db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'aum_task_system';
}

// 'localhost' uses a Unix socket which doesn't exist on Railway — force TCP
if ($host === 'localhost') {
    $host = '127.0.0.1';
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;color:#970000;text-align:center;">
        <h3>Database Connection Failed</h3>
        <p>' . $conn->connect_error . '</p>
        <p>Host: ' . htmlspecialchars($host) . ' | Port: ' . $port . ' | DB: ' . htmlspecialchars($db) . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');
