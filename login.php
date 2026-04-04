<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);

    if (empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } else {
        if ($role === 'technician') {
            $stmt = $conn->prepare("SELECT id, full_name, password, status FROM technicians WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = 'technician';
                    $_SESSION['name'] = $user['full_name'];
                    $_SESSION['status'] = $user['status'];
                    header("Location: technician/profile.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "User not found";
            }
        } elseif ($role === 'admin') {
            $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE (username = ? OR email = ?)");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (verifyPassword($password, $admin['password'])) {
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['name'] = $admin['username'];
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "Admin not found";
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
    <title>Login - Anako Technician Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #02a75a;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .form-group input,
        .form-group select {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #02a75a;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .success-message {
            color: #155724;
            background: #d4edda;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-footer a {
            color: #02a75a;
            text-decoration: none;
            font-weight: bold;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .role-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .role-btn.active {
            border-color: #02a75a;
            background: #02a75a;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="<?php echo appUrl('assets/css/affiliate-theme.css'); ?>">
    <link rel="icon" type="image/png" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
    <meta name="theme-color" content="#02a75a">
    <link rel="manifest" href="<?php echo appUrl('manifest.webmanifest'); ?>">
    <link rel="apple-touch-icon" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><img src="<?php echo appUrl('assets/images/branding/anako-logo.png'); ?>" alt="Anako Logo" class="auth-logo"> Anako Technician</h1>
            <p>Professional Technician Registration Platform</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="role">Login As</label>
                <div class="role-selector">
                    <input type="radio" name="role" id="role_tech" value="technician" checked hidden>
                    <label class="role-btn active" for="role_tech">Technician</label>
                    <input type="radio" name="role" id="role_admin" value="admin" hidden>
                    <label class="role-btn" for="role_admin">Admin</label>
                </div>
            </div>

            <div class="form-group">
                <label for="email">
                    <span id="field-label">Email Address</span>
                </label>
                <input type="text" id="email" name="email" class="form-control" placeholder="" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <a href="<?php echo appUrl('forgot_password.php'); ?>">Forgot password?</a><br>
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        const techRadio = document.getElementById('role_tech');
        const adminRadio = document.getElementById('role_admin');
        const techLabel = document.querySelector('label[for="role_tech"]');
        const adminLabel = document.querySelector('label[for="role_admin"]');
        const emailField = document.getElementById('email');
        const fieldLabel = document.getElementById('field-label');

        techRadio.addEventListener('change', () => {
            techLabel.classList.add('active');
            adminLabel.classList.remove('active');
            emailField.placeholder = 'your.email@example.com';
            fieldLabel.textContent = 'Email Address';
            emailField.type = 'email';
        });

        adminRadio.addEventListener('change', () => {
            adminLabel.classList.add('active');
            techLabel.classList.remove('active');
            emailField.placeholder = 'admin / admin@email.com';
            fieldLabel.textContent = 'Username or Email';
            emailField.type = 'text';
        });
    </script>
    <script>
        window.ANAKO_APP_BASE = <?php echo json_encode(rtrim(appUrl(''), '/')); ?>;
    </script>
    <script src="<?php echo appUrl('assets/js/pwa-register.js'); ?>"></script>
    <script src="<?php echo appUrl('assets/js/pwa-ui.js'); ?>"></script>
</body>
</html>
