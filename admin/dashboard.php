<?php
require 'includes/admin_auth.php';
require_once '../includes/functions.php';

$page_title = 'Dashboard';

$stats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN `status` = 'Pending'     THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN `status` = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN `status` = 'Completed'   THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN `priority` = 'High'      THEN 1 ELSE 0 END) AS high_count
    FROM requests
")->fetch_assoc();

$recent = $conn->query("
    SELECT r.id, r.request_title, r.building_name, r.category, r.priority, r.status, r.created_at,
           u.full_name
    FROM   requests r
    JOIN   users u ON u.id = r.user_id
    ORDER  BY r.created_at DESC
    LIMIT  10
");

require 'includes/admin_header.php';
?>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total Requests', $stats['total'],        'bi-clipboard-data'],
        ['Pending',        $stats['pending'],       'bi-hourglass-split'],
        ['In Progress',    $stats['in_progress'],   'bi-arrow-repeat'],
        ['Completed',      $stats['completed'],     'bi-check-circle'],
        ['High Priority',  $stats['high_count'],    'bi-exclamation-triangle'],
    ];
    foreach ($cards as [$label, $val, $icon]): ?>
        <div class="col-6 col-xl" style="flex:1 1 160px;">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-number"><?= (int)$val ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                    <i class="bi <?= $icon ?> stat-icon"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-muted"></i>
            <span class="fw-semibold">Recent Requests</span>
        </div>
        <a href="manage_requests.php" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-grid me-1"></i>Manage All
        </a>
    </div>
    <div class="card-body p-0">
        <?php if ($recent->num_rows === 0): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-5 d-block mb-2 opacity-30"></i>
                No requests yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Title</th>
                            <th>User</th>
                            <th>Building</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $recent->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted small">#<?= $r['id'] ?></td>
                                <td class="fw-medium" style="max-width:180px;">
                                    <span class="d-block text-truncate"><?= htmlspecialchars($r['request_title']) ?></span>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($r['full_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($r['building_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($r['category']) ?></td>
                                <td><?= priority_badge($r['priority']) ?></td>
                                <td><?= status_badge($r['status']) ?></td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td class="pe-4">
                                    <a href="request_details.php?id=<?= $r['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
