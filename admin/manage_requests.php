<?php
require 'includes/admin_auth.php';
require_once '../includes/functions.php';

$page_title = 'Manage Requests';

$statuses   = ['Pending', 'In Progress', 'Completed', 'Rejected'];
$priorities = ['Low', 'Medium', 'High'];
$categories = ['Maintenance', 'IT Support', 'Cleaning', 'Electrical', 'Furniture', 'Plumbing', 'Other'];

$search          = trim($_GET['search']   ?? '');
$filter_status   = $_GET['status']        ?? '';
$filter_priority = $_GET['priority']      ?? '';
$filter_category = $_GET['category']      ?? '';

$where  = 'WHERE 1=1';
$params = [];
$types  = '';

if ($search !== '') {
    $where   .= ' AND (r.request_title LIKE ? OR r.building_name LIKE ? OR r.category LIKE ? OR u.full_name LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = [$like, $like, $like, $like];
    $types   .= 'ssss';
}
if ($filter_status !== '' && in_array($filter_status, $statuses, true)) {
    $where   .= ' AND r.`status` = ?';
    $params[] = $filter_status;
    $types   .= 's';
}
if ($filter_priority !== '' && in_array($filter_priority, $priorities, true)) {
    $where   .= ' AND r.`priority` = ?';
    $params[] = $filter_priority;
    $types   .= 's';
}
if ($filter_category !== '' && in_array($filter_category, $categories, true)) {
    $where   .= ' AND r.category = ?';
    $params[] = $filter_category;
    $types   .= 's';
}

$sql  = "SELECT r.id, r.request_title, r.building_name, r.category, r.`priority`, r.`status`,
                r.created_at, u.full_name
         FROM requests r
         JOIN users u ON u.id = r.user_id
         $where
         ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$requests = $stmt->get_result();
$stmt->close();

require 'includes/admin_header.php';
?>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-4">
                <label class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Title, building, user..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label small fw-semibold mb-1">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <?php foreach ($priorities as $p): ?>
                        <option value="<?= $p ?>" <?= $filter_priority === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label small fw-semibold mb-1">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c ?>" <?= $filter_category === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if ($search || $filter_status || $filter_priority || $filter_category): ?>
                    <a href="manage_requests.php" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-x"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold">
            <i class="bi bi-table me-2 text-muted"></i>All Requests
            <span class="badge ms-2" style="background:#970000;"><?= $requests->num_rows ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if ($requests->num_rows === 0): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search display-5 d-block mb-2 opacity-30"></i>
                No requests match your filters.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Request Title</th>
                            <th>User</th>
                            <th>Building</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="pe-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $requests->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted small">#<?= $r['id'] ?></td>
                                <td class="fw-medium" style="max-width:200px;">
                                    <span class="d-block text-truncate"><?= htmlspecialchars($r['request_title']) ?></span>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($r['full_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($r['building_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($r['category']) ?></td>
                                <td><?= priority_badge($r['priority']) ?></td>
                                <td><?= status_badge($r['status']) ?></td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td class="pe-4 text-center">
                                    <a href="request_details.php?id=<?= $r['id'] ?>"
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

<?php require 'includes/admin_footer.php'; ?>
