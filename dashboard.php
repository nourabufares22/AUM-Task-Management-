<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/functions.php';

$page_title = 'Dashboard';
$user_id    = $_SESSION['user_id'];

// Statistics
$stmt = $conn->prepare('
    SELECT
        COUNT(*) AS total,
        SUM(status = "Pending")     AS pending,
        SUM(status = "In Progress") AS in_progress,
        SUM(status = "Completed")   AS completed
    FROM requests
    WHERE user_id = ?
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Recent 5 requests
$stmt = $conn->prepare('
    SELECT id, request_title, building_name, status, created_at
    FROM requests
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$recent = $stmt->get_result();
$stmt->close();
?>
<?php require 'includes/header.php'; ?>

<div class="container py-4">

    <!-- Welcome Banner -->
    <div class="welcome-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4><i class="bi bi-hand-wave me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h4>
            <p>Manage and track your maintenance requests from here.</p>
        </div>
        <a href="building_requests.php" class="btn btn-light fw-semibold" style="color:var(--clr-primary);">
            <i class="bi bi-plus-circle me-2"></i>New Request
        </a>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <?php
        $stat_items = [
            ['label' => 'Total Requests', 'value' => $stats['total']      ?? 0, 'icon' => 'bi-clipboard-data',   'color' => '#970000'],
            ['label' => 'Pending',         'value' => $stats['pending']     ?? 0, 'icon' => 'bi-hourglass-split',  'color' => '#f59e0b'],
            ['label' => 'In Progress',     'value' => $stats['in_progress'] ?? 0, 'icon' => 'bi-arrow-repeat',     'color' => '#0ea5e9'],
            ['label' => 'Completed',       'value' => $stats['completed']   ?? 0, 'icon' => 'bi-check-circle',     'color' => '#22c55e'],
        ];
        foreach ($stat_items as $item): ?>
            <div class="col-6 col-lg-3">
                <div class="card stat-card h-100 p-3 p-lg-4">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="stat-number"><?= $item['value'] ?></div>
                            <div class="stat-label"><?= $item['label'] ?></div>
                        </div>
                        <i class="bi <?= $item['icon'] ?> stat-icon"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Requests -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-muted"></i>
                <span class="fw-semibold">Recent Requests</span>
            </div>
            <a href="my_requests.php" class="btn btn-sm btn-outline-primary">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <?php if ($recent->num_rows === 0): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">No requests submitted yet.</p>
                    <a href="building_requests.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Submit Your First Request
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Request Title</th>
                                <th>Building</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?= htmlspecialchars($row['request_title']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($row['building_name']) ?></td>
                                    <td><?= status_badge($row['status']) ?></td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="pe-4">
                                        <a href="request_details.php?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-secondary">
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

</div>

<?php require 'includes/footer.php'; ?>
