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

// Auto-add admin columns if they don't exist yet (safe to run every time)
$conn->query("ALTER TABLE requests ADD COLUMN IF NOT EXISTS assigned_to VARCHAR(50) DEFAULT NULL");
$conn->query("ALTER TABLE requests ADD COLUMN IF NOT EXISTS admin_notes TEXT DEFAULT NULL");
