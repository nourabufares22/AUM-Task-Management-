<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/functions.php';

$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT r.*, u.full_name, u.email
    FROM requests r
    JOIN users u ON u.id = r.user_id
    WHERE r.id = ? AND r.user_id = ?
');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$req = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$req) {
    header('Location: my_requests.php');
    exit;
}

$page_title = 'Request #' . $req['id'];
?>
<?php require 'includes/header.php'; ?>

<div class="container py-4">

    <div class="page-header">
        <i class="bi bi-file-text"></i>
        <div>
            <h5>Request Details</h5>
            <small class="text-muted">Request #<?= $req['id'] ?> — submitted
                <?= date('d M Y, g:i A', strtotime($req['created_at'])) ?>
            </small>
        </div>
        <a href="my_requests.php" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row g-4">

        <!-- Main details -->
        <div class="col-lg-8">
            <div class="card p-4">

                <!-- Title & status row -->
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <h5 class="fw-bold mb-0" style="color:#1a1a1a;">
                        <?= htmlspecialchars($req['request_title']) ?>
                    </h5>
                    <?= status_badge($req['status']) ?>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Building</span>
                    <span class="detail-value">
                        <i class="bi bi-buildings me-1 text-muted"></i>
                        <?= htmlspecialchars($req['building_name']) ?>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Location</span>
                    <span class="detail-value">
                        <i class="bi bi-geo-alt me-1 text-muted"></i>
                        <?= htmlspecialchars($req['location']) ?>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">
                        <span class="badge" style="background:#970000;"><?= htmlspecialchars($req['category']) ?></span>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Priority</span>
                    <span class="detail-value"><?= priority_badge($req['priority']) ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Submitted By</span>
                    <span class="detail-value">
                        <i class="bi bi-person me-1 text-muted"></i>
                        <?= htmlspecialchars($req['full_name']) ?>
                        <span class="text-muted ms-1 small">(<?= htmlspecialchars($req['email']) ?>)</span>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">
                        <i class="bi bi-calendar me-1 text-muted"></i>
                        <?= date('D, d M Y — g:i A', strtotime($req['created_at'])) ?>
                    </span>
                </div>

                <!-- Description -->
                <div class="mt-3">
                    <div class="detail-label mb-2">Description</div>
                    <div class="p-3 rounded" style="background:#f8f8f8;line-height:1.7;font-size:0.9rem;color:#333;">
                        <?= nl2br(htmlspecialchars($req['description'])) ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Status Card -->
            <div class="card p-3 mb-3">
                <div class="small fw-semibold text-muted text-uppercase mb-2">Current Status</div>
                <div class="d-flex align-items-center gap-2">
                    <?php
                    $icons = [
                        'Pending'     => ['bi-hourglass-split', 'text-warning'],
                        'In Progress' => ['bi-arrow-repeat',    'text-info'],
                        'Completed'   => ['bi-check-circle',    'text-success'],
                        'Rejected'    => ['bi-x-circle',        'text-danger'],
                    ];
                    [$icon, $cls] = $icons[$req['status']] ?? ['bi-question-circle', 'text-secondary'];
                    ?>
                    <i class="bi <?= $icon ?> <?= $cls ?> fs-4"></i>
                    <div>
                        <?= status_badge($req['status']) ?>
                        <div class="text-muted small mt-1">
                            <?php
                            $desc = [
                                'Pending'     => 'Awaiting review by maintenance team.',
                                'In Progress' => 'Your request is being worked on.',
                                'Completed'   => 'This request has been resolved.',
                                'Rejected'    => 'This request was not accepted.',
                            ];
                            echo $desc[$req['status']] ?? '';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attached Image -->
            <?php if (!empty($req['image']) && file_exists('uploads/' . $req['image'])): ?>
                <div class="card p-3">
                    <div class="small fw-semibold text-muted text-uppercase mb-2">Attached Image</div>
                    <a href="uploads/<?= htmlspecialchars($req['image']) ?>" target="_blank">
                        <img src="uploads/<?= htmlspecialchars($req['image']) ?>"
                             alt="Request Image" class="img-preview">
                    </a>
                    <div class="text-center mt-2">
                        <a href="uploads/<?= htmlspecialchars($req['image']) ?>"
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>View Full Image
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card p-3 text-center">
                    <div class="small fw-semibold text-muted text-uppercase mb-2">Attached Image</div>
                    <div class="py-3 text-muted">
                        <i class="bi bi-image d-block fs-2 mb-1 opacity-30"></i>
                        <small>No image attached</small>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="d-grid gap-2 mt-3">
                <a href="my_requests.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list-check me-2"></i>All My Requests
                </a>
                <a href="building_requests.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Request
                </a>
            </div>

        </div>
    </div>

</div>

<?php require 'includes/footer.php'; ?>
