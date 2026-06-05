<?php
require 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_requests.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    header('Location: manage_requests.php');
    exit;
}

// Delete uploaded image file if exists
$stmt = $conn->prepare('SELECT image FROM requests WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && !empty($row['image'])) {
    $img_path = __DIR__ . '/../uploads/' . $row['image'];
    if (file_exists($img_path)) {
        unlink($img_path);
    }
}

// Delete the request
$stmt = $conn->prepare('DELETE FROM requests WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: manage_requests.php?deleted=1');
exit;
