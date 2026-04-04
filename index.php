<?php
include 'includes/auth.php';

$page_title = "Anako Technician Platform";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .navbar {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
        }
        .hero-section {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .hero-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .hero-subtitle {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .btn-primary-custom {
            background: white;
            color: #02a75a;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 5px;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            color: #02a75a;
        }
        .features-section {
            padding: 80px 0;
        }
        .feature-box {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: transform 0.2s;
        }
        .feature-box:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .feature-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .feature-description {
            color: #666;
            font-size: 14px;
        }
        .footer {
            background: #333;
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 80px;
        }
        .user-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .user-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .user-role {
            color: #02a75a;
            font-weight: bold;
            margin-top: 5px;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .quick-link-btn {
            padding: 15px;
            text-align: center;
            background: #f5f7fa;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .quick-link-btn:hover {
            background: #02a75a;
            color: white;
            border-color: #018a4a;
        }
    </style>
    <link rel="stylesheet" href="<?php echo appUrl('assets/css/affiliate-theme.css'); ?>">
    <link rel="icon" type="image/png" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
    <meta name="theme-color" content="#02a75a">
    <link rel="manifest" href="<?php echo appUrl('manifest.webmanifest'); ?>">
    <link rel="apple-touch-icon" href="<?php echo appUrl('assets/images/branding/anako-favicon.png'); ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Anako Technician</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo $_SESSION['name']; ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome to Anako Technician Platform</h1>
            <p class="hero-subtitle">Professional Technician Registration & Management System</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn-primary-custom">Register as Technician</a>
                <a href="login.php" class="btn-primary-custom">Login</a>
            <?php else: ?>
                <?php if (isTechnician()): ?>
                    <a href="technician/profile.php" class="btn-primary-custom">My Profile</a>
                <?php elseif (isAdmin()): ?>
                    <a href="admin/dashboard.php" class="btn-primary-custom">Admin Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; font-weight: bold;">Platform Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">📝</div>
                        <div class="feature-title">Easy Registration</div>
                        <div class="feature-description">Create a professional technician profile in minutes</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">🎓</div>
                        <div class="feature-title">Certifications</div>
                        <div class="feature-description">Upload and manage your professional certificates</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">⭐</div>
                        <div class="feature-title">Professional Profile</div>
                        <div class="feature-description">Showcase your skills and experience</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">🔒</div>
                        <div class="feature-title">Secure System</div>
                        <div class="feature-description">Your data is protected with modern security</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">🌍</div>
                        <div class="feature-title">Wide Reach</div>
                        <div class="feature-description">Connect with job opportunities</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">👨‍💼</div>
                        <div class="feature-title">Admin Management</div>
                        <div class="feature-description">Efficient technician management system</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Anako Smart Systems. All rights reserved.</p>
        </div>
    </footer>

    <script>
        window.ANAKO_APP_BASE = <?php echo json_encode(rtrim(appUrl(''), '/')); ?>;
    </script>
    <script src="<?php echo appUrl('assets/js/pwa-register.js'); ?>"></script>
    <script src="<?php echo appUrl('assets/js/pwa-ui.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
