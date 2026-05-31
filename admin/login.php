<?php
session_start();
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === '123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = 'admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — AUM Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-login-wrap">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-4">

            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:62px;height:62px;background:rgba(255,255,255,0.15);border-radius:14px;">
                    <i class="bi bi-shield-lock text-white fs-3"></i>
                </div>
                <h5 class="text-white fw-bold mb-1">Admin Portal</h5>
                <p class="text-white-50 small mb-0">American University of Madaba</p>
            </div>

            <div class="card p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0"
                                   placeholder="admin"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   autocomplete="username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" id="pw"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="••••••"
                                   autocomplete="current-password" required>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="const i=document.getElementById('pw');const e=this.querySelector('i');i.type=i.type==='password'?'text':'password';e.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-shield-check me-2"></i>Sign In as Admin
                    </button>
                </form>
            </div>

            <p class="text-center mt-3 mb-0">
                <a href="../login.php" class="text-white-50 small">
                    <i class="bi bi-arrow-left me-1"></i>Back to User Login
                </a>
            </p>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
