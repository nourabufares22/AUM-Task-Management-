<?php
require 'includes/admin_auth.php';

// ── Stats ──────────────────────────────────────────────────
$stats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN `status` = 'Pending'     THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN `status` = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN `status` = 'Completed'   THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN `status` = 'Rejected'    THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN `priority` = 'High'      THEN 1 ELSE 0 END) AS high_count,
        SUM(CASE WHEN `priority` = 'Medium'    THEN 1 ELSE 0 END) AS medium_count,
        SUM(CASE WHEN `priority` = 'Low'       THEN 1 ELSE 0 END) AS low_count
    FROM requests
")->fetch_assoc();

$total = max((int)$stats['total'], 1);

function pct(int $count, int $total): string {
    return round(($count / $total) * 100) . '%';
}

// ── Category breakdown ─────────────────────────────────────
$categories = $conn->query("
    SELECT category, COUNT(*) AS cnt
    FROM requests GROUP BY category ORDER BY cnt DESC
");

// ── Building breakdown ─────────────────────────────────────
$buildings = $conn->query("
    SELECT building_name, COUNT(*) AS cnt
    FROM requests GROUP BY building_name ORDER BY cnt DESC
");

// ── All requests ───────────────────────────────────────────
$requests = $conn->query("
    SELECT r.id, r.request_title, u.full_name AS submitted_by, u.email,
           r.building_name, r.category, r.`priority`, r.`status`,
           r.location, r.description, r.assigned_to, r.admin_notes, r.created_at
    FROM requests r
    JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
");

// ── Output ─────────────────────────────────────────────────
$filename = 'AUM_Report_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM — makes Excel open it correctly
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

// ── Title ──────────────────────────────────────────────────
fputcsv($out, ['AUM MAINTENANCE REQUEST SYSTEM — FULL REPORT']);
fputcsv($out, ['Generated:', date('d M Y, g:i A')]);
fputcsv($out, []);

// ── Section 1: Summary ─────────────────────────────────────
fputcsv($out, ['=== SUMMARY ===']);
fputcsv($out, ['Metric', 'Count', 'Percentage']);
fputcsv($out, ['Total Requests',  $stats['total'],       '100%']);
fputcsv($out, ['Pending',         $stats['pending'],     pct((int)$stats['pending'],     $total)]);
fputcsv($out, ['In Progress',     $stats['in_progress'], pct((int)$stats['in_progress'], $total)]);
fputcsv($out, ['Completed',       $stats['completed'],   pct((int)$stats['completed'],   $total)]);
fputcsv($out, ['Rejected',        $stats['rejected'],    pct((int)$stats['rejected'],    $total)]);
fputcsv($out, []);

// ── Section 2: By Category ─────────────────────────────────
fputcsv($out, ['=== REQUESTS BY CATEGORY ===']);
fputcsv($out, ['Category', 'Count', 'Percentage']);
while ($row = $categories->fetch_assoc()) {
    fputcsv($out, [
        $row['category'],
        $row['cnt'],
        pct((int)$row['cnt'], $total),
    ]);
}
fputcsv($out, []);

// ── Section 3: By Priority ─────────────────────────────────
fputcsv($out, ['=== REQUESTS BY PRIORITY ===']);
fputcsv($out, ['Priority', 'Count', 'Percentage']);
fputcsv($out, ['High',   $stats['high_count'],   pct((int)$stats['high_count'],   $total)]);
fputcsv($out, ['Medium', $stats['medium_count'], pct((int)$stats['medium_count'], $total)]);
fputcsv($out, ['Low',    $stats['low_count'],    pct((int)$stats['low_count'],    $total)]);
fputcsv($out, []);

// ── Section 4: By Status ───────────────────────────────────
fputcsv($out, ['=== REQUESTS BY STATUS ===']);
fputcsv($out, ['Status', 'Count', 'Percentage']);
fputcsv($out, ['Pending',     $stats['pending'],     pct((int)$stats['pending'],     $total)]);
fputcsv($out, ['In Progress', $stats['in_progress'], pct((int)$stats['in_progress'], $total)]);
fputcsv($out, ['Completed',   $stats['completed'],   pct((int)$stats['completed'],   $total)]);
fputcsv($out, ['Rejected',    $stats['rejected'],    pct((int)$stats['rejected'],    $total)]);
fputcsv($out, []);

// ── Section 5: By Building ─────────────────────────────────
fputcsv($out, ['=== REQUESTS BY BUILDING ===']);
fputcsv($out, ['Building', 'Count', 'Percentage']);
while ($row = $buildings->fetch_assoc()) {
    fputcsv($out, [
        $row['building_name'],
        $row['cnt'],
        pct((int)$row['cnt'], $total),
    ]);
}
fputcsv($out, []);

// ── Section 6: All Requests ────────────────────────────────
fputcsv($out, ['=== ALL REQUESTS ===']);
fputcsv($out, [
    'ID', 'Request Title', 'Submitted By', 'Email',
    'Building', 'Category', 'Priority', 'Status',
    'Location', 'Description', 'Assigned To', 'Admin Notes', 'Date Submitted',
]);

while ($row = $requests->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        $row['request_title'],
        $row['submitted_by'],
        $row['email'],
        $row['building_name'],
        $row['category'],
        $row['priority'],
        $row['status'],
        $row['location'],
        $row['description'],
        $row['assigned_to'] ?? '',
        $row['admin_notes'] ?? '',
        date('d M Y, g:i A', strtotime($row['created_at'])),
    ]);
}

fclose($out);
exit;
