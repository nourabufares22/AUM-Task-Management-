<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verify the session user still exists in the database
require_once __DIR__ . '/../config/db.php';
$_auth_stmt = $conn->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
$_auth_stmt->bind_param('i', $_SESSION['user_id']);
$_auth_stmt->execute();
$_auth_stmt->store_result();
if ($_auth_stmt->num_rows === 0) {
    // User no longer exists — clear stale session and redirect
    $_auth_stmt->close();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_auth_stmt->close();
