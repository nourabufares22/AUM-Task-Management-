<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require 'config/db.php';

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit;
}

// Validate token
$stmt = $conn->prepare('SELECT email, expires_at FROM password_resets WHERE token = ?');
$stmt->bind_param('s', $token);
$stmt->execute();
$reset = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reset) {
    $error = 'This reset link is invalid.';
} elseif (strtotime($reset['expires_at']) < time()) {
    $error = 'This reset link has expired. Please request a new one.';
    // Clean up expired token
    $del = $conn->prepare('DELETE FROM password_resets WHERE token = ?');
    $del->bind_param('s', $token);
    $del->execute();
    $del->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password  = $_POST['password']         ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must include at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must include at least one number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must include at least one special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
        $upd->bind_param('ss', $hashed, $reset['email']);
        $upd->execute();
        $upd->close();

        // Delete used token
        $del = $conn->prepare('DELETE FROM password_resets WHERE token = ?');
        $del->bind_param('s', $token);
        $del->execute();
        $del->close();

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — AUM Maintenance</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-wrapper">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">

            <div class="text-center mb-4">
                <div class="auth-logo">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h5 class="auth-title">Set New Password</h5>
                <p class="auth-sub">Choose a strong password for your account</p>
            </div>

            <div class="card auth-card">
                <?php if ($success): ?>
                    <div class="text-center py-2">
                        <div class="mb-3" style="font-size:3rem;">✅</div>
                        <h6 class="fw-bold mb-2">Password Updated!</h6>
                        <p class="text-muted small">Your password has been changed successfully.</p>
                        <a href="login.php" class="btn btn-primary w-100 py-2 mt-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Now
                        </a>
                    </div>

                <?php elseif ($error && !$reset): ?>
                    <div class="text-center py-2">
                        <div class="mb-3" style="font-size:3rem;">❌</div>
                        <p class="text-danger fw-semibold"><?= htmlspecialchars($error) ?></p>
                        <a href="forgot_password.php" class="btn btn-primary w-100 py-2 mt-2">
                            <i class="bi bi-arrow-repeat me-2"></i>Request New Link
                        </a>
                    </div>

                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="pw1"
                                       class="form-control" placeholder="Min. 8 characters"
                                       oninput="checkStrength(this.value)"
                                       autocomplete="new-password" required>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePw('pw1','eye1')">
                                    <i class="bi bi-eye" id="eye1"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="d-flex gap-1 mb-1">
                                    <div id="seg1" class="flex-fill rounded" style="height:4px;background:#ddd;transition:background .25s;"></div>
                                    <div id="seg2" class="flex-fill rounded" style="height:4px;background:#ddd;transition:background .25s;"></div>
                                    <div id="seg3" class="flex-fill rounded" style="height:4px;background:#ddd;transition:background .25s;"></div>
                                    <div id="seg4" class="flex-fill rounded" style="height:4px;background:#ddd;transition:background .25s;"></div>
                                </div>
                                <div id="strengthLabel" class="form-text" style="font-size:0.75rem;"></div>
                            </div>
                            <ul class="list-unstyled mt-2 mb-0" style="font-size:0.75rem;">
                                <li id="req-len"  class="text-muted"><i class="bi bi-x-circle me-1"></i>At least 8 characters</li>
                                <li id="req-up"   class="text-muted"><i class="bi bi-x-circle me-1"></i>At least one uppercase letter</li>
                                <li id="req-num"  class="text-muted"><i class="bi bi-x-circle me-1"></i>At least one number</li>
                                <li id="req-spec" class="text-muted"><i class="bi bi-x-circle me-1"></i>At least one special character</li>
                            </ul>
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
                            <i class="bi bi-check-circle me-2"></i>Update Password
                        </button>
                    </form>
                <?php endif; ?>

                <hr class="my-3">
                <p class="text-center mb-0 small text-muted">
                    <a href="login.php" style="color:var(--clr-primary);" class="fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
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
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
function checkStrength(pw) {
    const checks = [pw.length >= 8, /[A-Z]/.test(pw), /[0-9]/.test(pw), /[^A-Za-z0-9]/.test(pw)];
    const ids    = ['req-len','req-up','req-num','req-spec'];
    checks.forEach((met, i) => {
        const el = document.getElementById(ids[i]);
        el.className = met ? 'text-success' : 'text-muted';
        el.querySelector('i').className = met ? 'bi bi-check-circle-fill me-1' : 'bi bi-x-circle me-1';
    });
    const score  = checks.filter(Boolean).length;
    const colors = ['','#ef4444','#f59e0b','#3b82f6','#22c55e'];
    const labels = ['','<span style="color:#ef4444">Weak</span>','<span style="color:#f59e0b">Fair</span>',
                    '<span style="color:#3b82f6">Good</span>','<span style="color:#22c55e">Strong</span>'];
    for (let i = 1; i <= 4; i++)
        document.getElementById('seg'+i).style.background = i <= score ? colors[score] : '#ddd';
    document.getElementById('strengthLabel').innerHTML = pw.length ? 'Strength: ' + labels[score] : '';
}
</script>
</body>
</html>
