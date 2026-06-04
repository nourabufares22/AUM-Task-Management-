<?php
// Individual Railway variables — try these first
$host = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: '';
$port = (int)(getenv('MYSQLPORT')    ?: getenv('MYSQL_PORT')     ?: 3306);
$user = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: '';

// If host not found, fall back to parsing the connection URL
if (!$host) {
    $url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: getenv('MYSQL_PRIVATE_URL') ?: '';
    if ($url) {
        $p    = parse_url($url);
        $host = $p['host']  ?? '';
        $port = (int)($p['port'] ?? 3306);
        $user = isset($p['user']) ? urldecode($p['user']) : 'root';
        $pass = isset($p['pass']) ? urldecode($p['pass']) : '';
        $db   = ltrim($p['path'] ?? '', '/');
    }
}

// Local fallback
if (!$host) $host = '127.0.0.1';
if (!$db)   $db   = 'aum_task_system';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;color:#970000;text-align:center;">
        <h3>Database Connection Failed</h3>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Host: ' . htmlspecialchars($host) . ' | DB: ' . htmlspecialchars($db) . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');
