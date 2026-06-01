<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require 'config/db.php';

$error   = '';
$success = '';
$data    = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim(strtolower($_POST['email'] ?? ''));
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $data = ['full_name' => $full_name, 'email' => $email];

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!str_contains(strtolower($email), 'aum')) {
        $error = 'Email must contain "aum" (e.g. student@aum.edu.jo).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'This email is already registered. Please login.';
            $stmt->close();
        } else {
            $stmt->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $full_name, $email, $hashed);
            if ($stmt->execute()) {
                $success = 'Account created successfully!';
                $data    = ['full_name' => '', 'email' => ''];
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — AUM Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-wrapper">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">

            <div class="text-center mb-4">
                <div class="auth-logo">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h5 class="auth-title">Create Your Account</h5>
                <p class="auth-sub">Register with your AUM email to get started</p>
            </div>

            <div class="card auth-card">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                        <span>
                            <?= htmlspecialchars($success) ?>
                            <a href="login.php" class="alert-link ms-1">Sign in now &rarr;</a>
                        </span>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="full_name" class="form-control"
                                   placeholder="e.g. Ahmad Al-Rashidi"
                                   value="<?= htmlspecialchars($data['full_name']) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">AUM Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                   placeholder="student@aum.edu.jo"
                                   value="<?= htmlspecialchars($data['email']) ?>"
                                   autocomplete="email" required>
                        </div>
                        <div class="form-text">Must be an AUM email (must contain "aum").</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="pw1"
                                   class="form-control" placeholder="Min. 6 characters"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePw('pw1','eye1')">
                                <i class="bi bi-eye" id="eye1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="pw2"
                                   class="form-control" placeholder="Repeat your password"
                                   autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePw('pw2','eye2')">
                                <i class="bi bi-eye" id="eye2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-check me-2"></i>Create Account
                    </button>
                </form>

                <hr class="my-3">
                <p class="text-center mb-0 small text-muted">
                    Already have an account?
                    <a href="login.php" class="fw-semibold" style="color:var(--clr-primary);">Sign in here</a>
                </p>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
