<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';

$error = '';
$success = '';
$devLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role'] ?? 'technician');
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (!in_array($role, ['technician', 'admin'], true)) {
        $error = 'Invalid account type selected.';
    } else {
        $user = null;

        if ($role === 'technician') {
            $stmt = $conn->prepare('SELECT id, email FROM technicians WHERE email = ? LIMIT 1');
        } else {
            $stmt = $conn->prepare('SELECT id, email FROM admins WHERE email = ? LIMIT 1');
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
        }

        // Always return generic success to avoid exposing whether an email exists.
        $success = 'If an account exists for that email, a password reset link has been sent.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $requestedIp = $_SERVER['REMOTE_ADDR'] ?? null;

            // Invalidate previous unused tokens for this account.
            $invalidate = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_type = ? AND user_id = ? AND used_at IS NULL");
            if ($invalidate) {
                $invalidate->bind_param('si', $role, $user['id']);
                $invalidate->execute();
            }

            $insert = $conn->prepare('INSERT INTO password_resets (user_type, user_id, email, token_hash, expires_at, requested_ip) VALUES (?, ?, ?, ?, ?, ?)');
            if ($insert) {
                $insert->bind_param('sissss', $role, $user['id'], $user['email'], $tokenHash, $expiresAt, $requestedIp);

                if ($insert->execute()) {
                    $resetLink = appAbsoluteUrl('reset_password.php?token=' . urlencode($token) . '&role=' . urlencode($role));
                    $subject = 'Anako Password Reset';
                    $message = "Hello,\n\nA password reset request was made for your account.\n" .
                        "Use this link to set a new password (valid for 1 hour):\n\n" .
                        $resetLink . "\n\n" .
                        "If you did not request this, you can ignore this email.\n";
                    $headers = 'From: no-reply@' . preg_replace('/:.*/', '', ($_SERVER['HTTP_HOST'] ?? 'localhost')) . "\r\n";

                    @mail($user['email'], $subject, $message, $headers);

                    $host = $_SERVER['HTTP_HOST'] ?? '';
                    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                        // Local development helper when mail transport is unavailable.
                        $devLink = $resetLink;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Anako Technician Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo appUrl('assets/css/affiliate-theme.css'); ?>">
    <link rel="icon" type="image/png" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
    <meta name="theme-color" content="#02a75a">
    <link rel="manifest" href="<?php echo appUrl('manifest.webmanifest'); ?>">
    <link rel="apple-touch-icon" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-wrap {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 480px;
        }
        .title {
            color: #02a75a;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="card-wrap">
        <h2 class="title">Reset your password</h2>
        <p class="text-muted mb-4">Enter your email and account type to receive a reset link.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($devLink)): ?>
            <div class="alert alert-warning">
                <strong>Local dev link:</strong><br>
                <a href="<?php echo htmlspecialchars($devLink); ?>"><?php echo htmlspecialchars($devLink); ?></a>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="role" class="form-label">Account Type</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="technician">Technician</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <button class="btn btn-success w-100" type="submit">Send reset link</button>
        </form>

        <div class="mt-3 text-center">
            <a href="<?php echo appUrl('login.php'); ?>">Back to login</a>
        </div>
    </div>

    <script>
        window.ANAKO_APP_BASE = <?php echo json_encode(rtrim(appUrl(''), '/')); ?>;
    </script>
    <script src="<?php echo appUrl('assets/js/pwa-register.js'); ?>"></script>
    <script src="<?php echo appUrl('assets/js/pwa-ui.js'); ?>"></script>
</body>
</html>
