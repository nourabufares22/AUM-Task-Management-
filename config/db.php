<?php
$host = 'localhost';
$db   = 'aum_task_system';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;color:#970000;text-align:center;">
        <h3>Database Connection Failed</h3>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure XAMPP MySQL is running and the database exists.</p>
    </div>');
}

$conn->set_charset('utf8mb4');
