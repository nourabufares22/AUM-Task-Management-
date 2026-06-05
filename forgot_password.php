<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require 'config/db.php';

// Ensure reset tokens table exists
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(100) NOT NULL,
    token       VARCHAR(100) NOT NULL,
    expires_at  DATETIME     NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
)");

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Remove old tokens for this email
            $del = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
            $del->bind_param('s', $email);
            $del->execute();
            $del->close();

            // Generate secure token
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $ins = $conn->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
            $ins->bind_param('sss', $email, $token, $expires);
            $ins->execute();
            $ins->close();

            // Build reset link
            require 'config/mailer.php';
            $reset_url = base_url() . '/reset_password.php?token=' . $token;

            // Send email
            try {
                $mail = create_mailer();
                $mail->addAddress($email, $user['full_name']);
                $mail->Subject = 'Reset Your Password — AUM Maintenance System';
                $mail->isHTML(true);
                $mail->Body = '
                <div style="font-family:Segoe UI,Arial,sans-serif;max-width:520px;margin:0 auto;">
                    <div style="background:#970000;padding:24px;text-align:center;border-radius:10px 10px 0 0;">
                        <h2 style="color:#fff;margin:0;font-size:20px;">Password Reset Request</h2>
                    </div>
                    <div style="background:#f9f9f9;padding:32px;border-radius:0 0 10px 10px;border:1px solid #eee;">
                        <p style="margin-top:0;">Hi <strong>' . htmlspecialchars($user['full_name']) . '</strong>,</p>
                        <p>We received a request to reset your password. Click the button below to set a new one:</p>
                        <div style="text-align:center;margin:30px 0;">
                            <a href="' . $reset_url . '"
                               style="background:#970000;color:#fff;padding:14px 32px;border-radius:7px;
                                      text-decoration:none;font-weight:bold;font-size:15px;display:inline-block;">
                                Reset My Password
                            </a>
                        </div>
                        <p style="color:#666;font-size:13px;">
                            This link expires in <strong>1 hour</strong>.<br>
                            If you did not request this, you can safely ignore this email.
                        </p>
                        <p style="color:#aaa;font-size:12px;word-break:break-all;">
                            Or copy this link: <a href="' . $reset_url . '" style="color:#970000;">' . $reset_url . '</a>
                        </p>
                    </div>
                </div>';
                $mail->AltBody = "Hi {$user['full_name']},\n\nReset your password here:\n{$reset_url}\n\nThis link expires in 1 hour.";
                $mail->send();
                $success = 'A password reset link has been sent to <strong>' . htmlspecialchars($email) . '</strong>. Check your inbox (and spam folder).';
            } catch (Exception $e) {
                $error = 'Could not send email. Please contact the system administrator.';
            }
        } else {
            // Don't reveal whether email exists
            $success = 'If that email is registered, a reset link has been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — AUM Maintenance</title>
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
                    <i class="bi bi-envelope-open"></i>
                </div>
                <h5 class="auth-title">Forgot Password?</h5>
                <p class="auth-sub">Enter your AUM email and we'll send a reset link</p>
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
                        <span><?= $success ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" novalidate>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                   placeholder="your@aum.edu.jo"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   autocomplete="email" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-send me-2"></i>Send Reset Link
                    </button>
                </form>
                <?php endif; ?>

                <hr class="my-3">
                <p class="text-center mb-0 small text-muted">
                    Remembered it?
                    <a href="login.php" class="fw-semibold" style="color:var(--clr-primary);">Sign in</a>
                </p>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
