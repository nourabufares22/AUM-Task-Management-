<?php
require 'includes/auth.php';
require_once 'config/db.php';

$building = trim($_GET['building'] ?? '');
if (empty($building)) {
    header('Location: building_requests.php');
    exit;
}

$page_title = 'New Request — ' . htmlspecialchars($building);
$error      = '';
$success    = false;

$categories = ['Maintenance','IT Support','Cleaning','Electrical','Furniture','Plumbing','Other'];
$priorities  = ['Low','Medium','High'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['request_title'] ?? '');
    $category    = $_POST['category']    ?? '';
    $priority    = $_POST['priority']    ?? '';
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id     = $_SESSION['user_id'];

    // Validation
    if (empty($title) || empty($category) || empty($priority) || empty($location) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($category, $categories)) {
        $error = 'Invalid category selected.';
    } elseif (!in_array($priority, $priorities)) {
        $error = 'Invalid priority selected.';
    } else {
        // Handle image upload
        $image_name = null;
        if (!empty($_FILES['image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $allowed_exts  = ['jpg', 'jpeg', 'png'];
            $file_type     = mime_content_type($_FILES['image']['tmp_name']);
            $file_ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $file_size     = $_FILES['image']['size'];
            $upload_error  = $_FILES['image']['error'];

            if ($upload_error !== UPLOAD_ERR_OK) {
                $error = 'Image upload error. Please try again.';
            } elseif (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
                $error = 'Only JPG and PNG images are allowed.';
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                $image_name = uniqid('req_', true) . '.' . $file_ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name)) {
                    $error      = 'Failed to save the image. Check uploads folder permissions.';
                    $image_name = null;
                }
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare('
                INSERT INTO requests
                    (user_id, building_name, request_title, category, priority, location, description, image, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, "Pending")
            ');
            $stmt->bind_param('isssssss',
                $user_id, $building, $title, $category, $priority,
                $location, $description, $image_name
            );
            if ($stmt->execute()) {
                header('Location: dashboard.php?submitted=1');
                exit;
            } else {
                $error = 'Failed to save the request. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<?php require 'includes/header.php'; ?>

<div class="container py-4">

    <div class="page-header">
        <i class="bi bi-pencil-square"></i>
        <div>
            <h5>New Maintenance Request</h5>
            <small class="text-muted">Fill in the form below and submit your request</small>
        </div>
    </div>

    <!-- Building indicator -->
    <div class="alert d-flex align-items-center gap-2 mb-4"
         style="background:rgba(151,0,0,0.07);border-left:4px solid #970000;color:#970000;border-radius:10px;">
        <i class="bi bi-buildings-fill fs-5"></i>
        <div>
            <strong>Selected Building:</strong>
            <span class="ms-1"><?= htmlspecialchars($building) ?></span>
        </div>
        <a href="building_requests.php" class="ms-auto btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Change
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" enctype="multipart/form-data" novalidate>

            <!-- Request Title -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Request Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="request_title" class="form-control"
                       placeholder="e.g. Broken AC in Room 204"
                       value="<?= htmlspecialchars($_POST['request_title'] ?? '') ?>"
                       maxlength="200" required>
            </div>

            <div class="row g-3 mb-3">
                <!-- Category -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Category <span class="text-danger">*</span>
                    </label>
                    <select name="category" class="form-select" required>
                        <option value="">— Select Category —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Priority -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Priority <span class="text-danger">*</span>
                    </label>
                    <select name="priority" class="form-select" required>
                        <option value="">— Select Priority —</option>
                        <?php foreach ($priorities as $pri): ?>
                            <option value="<?= $pri ?>"
                                <?= (($_POST['priority'] ?? '') === $pri) ? 'selected' : '' ?>>
                                <?= $pri ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Location -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Specific Location <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="location" class="form-control"
                           placeholder="e.g. Room 01, Floor 2, Lab B"
                           value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                           maxlength="100" required>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Description <span class="text-danger">*</span>
                </label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="Describe the issue in detail..."
                          required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Image Upload -->
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Attach Image <span class="text-muted small fw-normal">(JPG / PNG, max 5MB — optional)</span>
                </label>
                <input type="file" name="image" id="imageInput" class="form-control"
                       accept=".jpg,.jpeg,.png"
                       onchange="previewImage(this)">
                <div id="previewWrapper" class="mt-2 d-none">
                    <img id="imagePreview" src="#" alt="Preview" class="img-preview">
                </div>
            </div>

            <hr class="mb-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-send me-2"></i>Submit Request
                </button>
                <a href="building_requests.php" class="btn btn-outline-secondary px-4 py-2">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>

<script>
function previewImage(input) {
    const wrapper = document.getElementById('previewWrapper');
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            wrapper.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        wrapper.classList.add('d-none');
    }
}
</script>

<?php require 'includes/footer.php'; ?>
