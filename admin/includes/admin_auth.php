<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// DB connection shared across all admin pages
require_once __DIR__ . '/../../config/db.php';

// Auto-add admin columns if missing — MySQL compatible (no IF NOT EXISTS)
$db_name = $conn->query("SELECT DATABASE()")->fetch_row()[0];
$stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='requests' AND COLUMN_NAME=?");
$stmt->bind_param('ss', $db_name, $col);

$col = 'assigned_to';
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] == 0) {
    $conn->query("ALTER TABLE requests ADD COLUMN assigned_to VARCHAR(50) DEFAULT NULL");
}

$col = 'admin_notes';
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] == 0) {
    $conn->query("ALTER TABLE requests ADD COLUMN admin_notes TEXT DEFAULT NULL");
}
$stmt->close();
