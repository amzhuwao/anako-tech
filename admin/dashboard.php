<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/functions.php';

redirectIfNotAdmin();

$admin_id = $_SESSION['user_id'];
$stats = getDashboardStats($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anako Technician Platform</title>
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
        .sidebar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-height: calc(100vh - 60px);
            position: fixed;
            width: 250px;
            left: 0;
            top: 60px;
        }
        .sidebar-nav {
            list-style: none;
            padding: 0;
        }
        .sidebar-nav li {
            border-bottom: 1px solid #eee;
        }
        .sidebar-nav a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        .sidebar-nav a:hover {
            background: #f5f7fa;
            color: #02a75a;
            border-left: 4px solid #02a75a;
            padding-left: 16px;
        }
        .sidebar-nav a.active {
            background: #02a75a;
            color: white;
            border-left: 4px solid #018a4a;
            padding-left: 16px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .mobile-breadcrumb-wrap {
            display: none;
        }
        .mobile-breadcrumb {
            background: white;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .mobile-breadcrumb .breadcrumb {
            margin-bottom: 0;
            font-size: 14px;
        }
        .mobile-breadcrumb .breadcrumb-item a {
            text-decoration: none;
            color: #02a75a;
            font-weight: 600;
        }
        .mobile-breadcrumb .breadcrumb-item.active {
            color: #333;
            font-weight: 600;
        }
        .mobile-quick-links {
            display: none;
            margin-bottom: 16px;
        }
        .mobile-quick-links-list {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 2px 0;
            scrollbar-width: thin;
        }
        .mobile-quick-link {
            white-space: nowrap;
            background: #ffffff;
            border: 1px solid #dfe3e8;
            color: #333;
            border-radius: 999px;
            padding: 7px 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .mobile-quick-link.active {
            background: #02a75a;
            color: #fff;
            border-color: #02a75a;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-10px);
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #02a75a;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .stat-card.pending {
            border-top: 4px solid #ffc107;
        }
        .stat-card.approved {
            border-top: 4px solid #28a745;
        }
        .stat-card.rejected {
            border-top: 4px solid #dc3545;
        }
        .stat-card.total {
            border-top: 4px solid #02a75a;
        }
        .page-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .action-btn {
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        .action-btn:hover {
            border-color: #02a75a;
            background: #f5f7fa;
            color: #02a75a;
        }
        .action-btn-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding: 20px 14px;
            }
            .mobile-breadcrumb-wrap {
                display: block;
            }
            .mobile-quick-links {
                display: block;
            }
            .page-title {
                margin-bottom: 20px;
            }
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
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Anako Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Admin: <?php echo $_SESSION['name']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="sidebar">
                <ul class="sidebar-nav">
                    <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                    <li><a href="technicians.php">👥 Technicians</a></li>
                    <li><a href="pending.php">⏳ Pending Review</a></li>
                    <li><a href="approved.php">✅ Approved</a></li>
                    <li><a href="rejected.php">❌ Rejected</a></li>
                    <li><a href="search.php">🔍 Search</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <div class="main-content">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Dashboard menu">
                    <div class="mobile-quick-links-list">
                        <a href="dashboard.php" class="mobile-quick-link active">Dashboard</a>
                        <a href="technicians.php" class="mobile-quick-link">Technicians</a>
                        <a href="pending.php" class="mobile-quick-link">Pending</a>
                        <a href="approved.php" class="mobile-quick-link">Approved</a>
                        <a href="rejected.php" class="mobile-quick-link">Rejected</a>
                        <a href="search.php" class="mobile-quick-link">Search</a>
                    </div>
                </div>

                <h1 class="page-title">📊 Admin Dashboard</h1>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card total">
                            <div class="stat-number"><?php echo $stats['total']; ?></div>
                            <div class="stat-label">Total Technicians</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card pending">
                            <div class="stat-number"><?php echo $stats['pending']; ?></div>
                            <div class="stat-label">Pending Approval</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card approved">
                            <div class="stat-number"><?php echo $stats['approved']; ?></div>
                            <div class="stat-label">Approved</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card rejected">
                            <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                            <div class="stat-label">Rejected</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <h3 style="margin-top: 40px; margin-bottom: 20px;">Quick Actions</h3>
                <div class="quick-actions">
                    <a href="pending.php" class="action-btn">
                        <div style="font-size: 32px;">⏳</div>
                        <div class="action-btn-title">Review Pending</div>
                    </a>
                    <a href="technicians.php" class="action-btn">
                        <div style="font-size: 32px;">👥</div>
                        <div class="action-btn-title">View All Technicians</div>
                    </a>
                    <a href="search.php" class="action-btn">
                        <div style="font-size: 32px;">🔍</div>
                        <div class="action-btn-title">Search Technicians</div>
                    </a>
                    <a href="approved.php" class="action-btn">
                        <div style="font-size: 32px;">✅</div>
                        <div class="action-btn-title">Approved List</div>
                    </a>
                </div>

                <!-- Dashboard Info -->
                <div style="background: white; padding: 30px; border-radius: 10px; margin-top: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <h4>📋 System Overview</h4>
                    <p>Welcome to the Anako Technician Management System! Use the navigation menu to:</p>
                    <ul>
                        <li><strong>Review Technicians:</strong> View and approve/reject technician registrations</li>
                        <li><strong>Search Technicians:</strong> Find technicians by skill, location, category, or experience</li>
                        <li><strong>Manage Profiles:</strong> Edit or delete technician profiles</li>
                        <li><strong>View Documents:</strong> Review uploaded certificates and documents</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.ANAKO_APP_BASE = <?php echo json_encode(rtrim(appUrl(''), '/')); ?>;
    </script>
    <script src="<?php echo appUrl('assets/js/pwa-register.js'); ?>"></script>
    <script src="<?php echo appUrl('assets/js/pwa-ui.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
