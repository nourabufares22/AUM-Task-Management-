<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: 3306;
$db   = getenv('MYSQLDATABASE') ?: 'aum_task_system';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;color:#970000;text-align:center;">
        <h3>Database Connection Failed</h3>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure the database is running and configured correctly.</p>
    </div>');
}
$conn->set_charset('utf8mb4');
