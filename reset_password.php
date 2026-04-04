<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';

$error = '';
$success = '';

$token = isset($_GET['token']) ? trim($_GET['token']) : trim($_POST['token'] ?? '');
$role = sanitize($_GET['role'] ?? ($_POST['role'] ?? ''));

$isRoleValid = in_array($role, ['technician', 'admin'], true);
$isTokenFormatValid = preg_match('/^[a-f0-9]{64}$/', $token) === 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$isRoleValid || !$isTokenFormatValid) {
        $error = 'Invalid or expired password reset link.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $tokenHash = hash('sha256', $token);

        $find = $conn->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = ? AND user_type = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        if (!$find) {
            $error = 'Password reset is not configured yet. Please run includes/create_schema.php once.';
        } else {
            $find->bind_param('ss', $tokenHash, $role);
            $find->execute();
            $result = $find->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $newHash = hashPassword($password);

                if ($role === 'technician') {
                    $update = $conn->prepare('UPDATE technicians SET password = ? WHERE id = ? LIMIT 1');
                } else {
                    $update = $conn->prepare('UPDATE admins SET password = ? WHERE id = ? LIMIT 1');
                }

                $userId = (int)$row['user_id'];
                $update->bind_param('si', $newHash, $userId);

                if ($update->execute()) {
                    $markUsed = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ? LIMIT 1');
                    $resetId = (int)$row['id'];
                    if ($markUsed) {
                        $markUsed->bind_param('i', $resetId);
                        $markUsed->execute();
                    }

                    // Invalidate any other outstanding tokens for this user.
                    $invalidateOthers = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_type = ? AND user_id = ? AND used_at IS NULL');
                    if ($invalidateOthers) {
                        $invalidateOthers->bind_param('si', $role, $userId);
                        $invalidateOthers->execute();
                    }

                    $success = 'Password updated successfully. You can now log in with your new password.';
                } else {
                    $error = 'Unable to update password. Please try again.';
                }
            } else {
                $error = 'Invalid or expired password reset link.';
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
    <title>Set New Password - Anako Technician Platform</title>
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
        <h2 class="title">Set new password</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="<?php echo appUrl('login.php'); ?>" class="btn btn-success w-100">Go to login</a>
        <?php else: ?>
            <?php if (!$isRoleValid || !$isTokenFormatValid): ?>
                <div class="alert alert-danger">Invalid or expired password reset link.</div>
                <a href="<?php echo appUrl('forgot_password.php'); ?>" class="btn btn-outline-success w-100">Request another link</a>
            <?php else: ?>
                <form method="POST" novalidate>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Update password</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="<?php echo appUrl('login.php'); ?>">Back to login</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        window.ANAKO_APP_BASE = <?php echo json_encode(rtrim(appUrl(''), '/')); ?>;
    </script>
    <script src="<?php echo appUrl('assets/js/pwa-register.js'); ?>"></script>
    <script src="<?php echo appUrl('assets/js/pwa-ui.js'); ?>"></script>
</body>
</html>
