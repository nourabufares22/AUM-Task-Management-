<?php
require 'includes/admin_auth.php';
require_once '../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: manage_requests.php');
    exit;
}

$stmt = $conn->prepare('
    SELECT r.id, r.request_title, r.building_name, r.category, r.`priority`, r.`status`,
           r.location, r.description, r.image, r.created_at,
           r.assigned_to, r.admin_notes,
           u.full_name, u.email
    FROM   requests r
    JOIN   users u ON u.id = r.user_id
    WHERE  r.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$req = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$req) {
    header('Location: manage_requests.php');
    exit;
}

$page_title  = 'Request #' . $id;
$valid_statuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];
$teams          = ['Maintenance Team', 'IT Team', 'Cleaning Team', 'Admin Staff'];
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status  = $_POST['status']       ?? '';
    $assigned_to = trim($_POST['assigned_to'] ?? '');
    $admin_notes = trim($_POST['admin_notes']  ?? '');

    if (!in_array($new_status, $valid_statuses, true)) {
        $error_msg = 'Invalid status selected.';
    } elseif ($assigned_to !== '' && !in_array($assigned_to, $teams, true)) {
        $error_msg = 'Invalid team selected.';
    } else {
        $upd = $conn->prepare('
            UPDATE requests
            SET `status` = ?, assigned_to = ?, admin_notes = ?
            WHERE id = ?
        ');
        $upd->bind_param('sssi', $new_status, $assigned_to, $admin_notes, $id);
        if ($upd->execute()) {
            $success_msg      = 'Request updated successfully.';
            $req['status']     = $new_status;
            $req['assigned_to'] = $assigned_to;
            $req['admin_notes'] = $admin_notes;
        } else {
            $error_msg = 'Update failed: ' . $conn->error;
        }
        $upd->close();
    }
}

require 'includes/admin_header.php';
?>

<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="manage_requests.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Requests
    </a>
    <button type="button" class="btn btn-danger btn-sm px-3"
            onclick="document.getElementById('deleteModal').querySelector('.modal').dispatchEvent(new Event('x'))"
            data-bs-toggle="modal" data-bs-target="#deleteModal">
        <i class="bi bi-trash me-1"></i>Delete Request
    </button>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#fff0f0;">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0">Delete Request</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-1">You are about to permanently delete:</p>
                <p class="fw-semibold mb-0" style="color:#970000;">
                    <?= htmlspecialchars($req['request_title']) ?>
                </p>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="delete_request.php" class="d-inline">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-trash me-1"></i>Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- Details -->
    <div class="col-lg-7">
        <div class="card p-4">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($req['request_title']) ?></h5>
                <?= status_badge($req['status']) ?>
            </div>

            <?php
            $details = [
                ['bi-person',    'Submitted By', htmlspecialchars($req['full_name']) . ' <span class="text-muted small">(' . htmlspecialchars($req['email']) . ')</span>'],
                ['bi-buildings', 'Building',     htmlspecialchars($req['building_name'])],
                ['bi-geo-alt',   'Location',     htmlspecialchars($req['location'])],
                ['bi-tag',       'Category',     '<span class="badge" style="background:#970000;">' . htmlspecialchars($req['category']) . '</span>'],
                ['bi-flag',      'Priority',     priority_badge($req['priority'])],
                ['bi-people',    'Assigned To',  !empty($req['assigned_to']) ? '<span class="badge bg-secondary">' . htmlspecialchars($req['assigned_to']) . '</span>' : '<span class="text-muted small">Not assigned</span>'],
                ['bi-calendar',  'Date',         date('D, d M Y — g:i A', strtotime($req['created_at']))],
            ];
            foreach ($details as [$icon, $label, $value]): ?>
                <div class="detail-item">
                    <span class="detail-label"><?= $label ?></span>
                    <span class="detail-value">
                        <i class="bi <?= $icon ?> me-1 text-muted"></i><?= $value ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <div class="mt-3">
                <div class="detail-label mb-2">Description</div>
                <div class="p-3 rounded" style="background:#f8f8f8;font-size:0.875rem;line-height:1.7;">
                    <?= nl2br(htmlspecialchars($req['description'])) ?>
                </div>
            </div>

            <?php if (!empty($req['admin_notes'])): ?>
                <div class="mt-3">
                    <div class="detail-label mb-2">Admin Notes</div>
                    <div class="p-3 rounded" style="background:#fff9f9;border:1px solid #f0d0d0;font-size:0.875rem;line-height:1.7;">
                        <?= nl2br(htmlspecialchars($req['admin_notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
        $img_path = '../uploads/' . $req['image'];
        if (!empty($req['image']) && file_exists($img_path)): ?>
            <div class="card p-3 mt-3">
                <div class="small fw-semibold text-muted text-uppercase mb-2">Attached Image</div>
                <a href="<?= htmlspecialchars($img_path) ?>" target="_blank">
                    <img src="<?= htmlspecialchars($img_path) ?>" alt="Attachment"
                         class="w-100 rounded" style="max-height:280px;object-fit:cover;">
                </a>
                <div class="text-center mt-2">
                    <a href="<?= htmlspecialchars($img_path) ?>" target="_blank"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View Full Image
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Admin Actions -->
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="fw-bold mb-3" style="color:#970000;">
                <i class="bi bi-pencil-square me-2"></i>Update Request
            </h6>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <?php foreach ($valid_statuses as $s): ?>
                            <option value="<?= $s ?>" <?= $req['status'] === $s ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Assign To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team ?>"
                                <?= ($req['assigned_to'] ?? '') === $team ? 'selected' : '' ?>>
                                <?= $team ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small">Admin Notes</label>
                    <textarea name="admin_notes" class="form-control" rows="4"
                              placeholder="Internal notes..."><?= htmlspecialchars($req['admin_notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
            </form>
        </div>

        <div class="card p-3 mt-3">
            <div class="small fw-semibold text-muted text-uppercase mb-3">Current Status</div>
            <?php
            $info = [
                'Pending'     => ['bi-hourglass-split', 'text-warning', 'Awaiting review by maintenance team'],
                'In Progress' => ['bi-arrow-repeat',    'text-info',    'Currently being worked on'],
                'Completed'   => ['bi-check-circle',    'text-success', 'Issue has been resolved'],
                'Rejected'    => ['bi-x-circle',        'text-danger',  'Request was not accepted'],
            ];
            [$icon, $cls, $desc] = $info[$req['status']] ?? ['bi-question-circle', 'text-secondary', ''];
            ?>
            <div class="d-flex align-items-center gap-3">
                <i class="bi <?= $icon ?> <?= $cls ?> fs-3"></i>
                <div>
                    <?= status_badge($req['status']) ?>
                    <div class="text-muted small mt-1"><?= $desc ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
