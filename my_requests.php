<?php
require 'includes/auth.php';
require_once 'config/db.php';
require 'includes/functions.php';

$page_title = 'My Requests';
$user_id    = $_SESSION['user_id'];

$search        = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$statuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];

// Build query dynamically
$where  = 'WHERE user_id = ?';
$params = [$user_id];
$types  = 'i';

if ($search !== '') {
    $where   .= ' AND (request_title LIKE ? OR building_name LIKE ? OR category LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

if ($status_filter !== '' && in_array($status_filter, $statuses)) {
    $where   .= ' AND status = ?';
    $params[] = $status_filter;
    $types   .= 's';
}

$sql  = 'SELECT id, request_title, building_name, category, priority, status, created_at
         FROM requests ' . $where . ' ORDER BY created_at DESC';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$requests = $stmt->get_result();
$stmt->close();
?>
<?php require 'includes/header.php'; ?>

<div class="container py-4">

    <div class="page-header">
        <i class="bi bi-list-check"></i>
        <div>
            <h5>My Requests</h5>
            <small class="text-muted">All maintenance requests you have submitted</small>
        </div>
        <a href="building_requests.php" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-circle me-1"></i>New Request
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-5">
                <label class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Title, building, category..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>"
                            <?= $status_filter === $s ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if ($search !== '' || $status_filter !== ''): ?>
                    <a href="my_requests.php" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-x"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if ($requests->num_rows === 0): ?>
                <div class="text-center py-5">
                    <i class="bi bi-search display-4 text-muted d-block mb-3"></i>
                    <p class="text-muted">
                        <?= ($search || $status_filter) ? 'No requests match your filters.' : 'You have no requests yet.' ?>
                    </p>
                    <?php if (!$search && !$status_filter): ?>
                        <a href="building_requests.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Submit First Request
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Title</th>
                                <th>Building</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="pe-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-muted small"><?= $i++ ?></td>
                                    <td class="fw-medium" style="max-width:200px;">
                                        <span class="d-block text-truncate">
                                            <?= htmlspecialchars($row['request_title']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="bi bi-buildings me-1"></i>
                                        <?= htmlspecialchars($row['building_name']) ?>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= priority_badge($row['priority']) ?></td>
                                    <td><?= status_badge($row['status']) ?></td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="request_details.php?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
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
