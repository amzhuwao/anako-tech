<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/functions.php';

redirectIfNotAdmin();

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : null;
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count
if ($status_filter) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM technicians WHERE status = ?");
    $count_stmt->bind_param("s", $status_filter);
} else {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM technicians");
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$pages = ceil($total / $per_page);

// Get technicians
if ($status_filter) {
    $stmt = $conn->prepare("SELECT * FROM technicians WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $status_filter, $per_page, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM technicians ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$technicians = $stmt->get_result();

// Handle actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action']);
    $technician_id = (int)$_POST['technician_id'];

    if ($action === 'approve') {
        $new_status = 'Approved';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✓ Technician approved successfully!";
        }
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✗ Technician rejected successfully!";
        }
    } elseif ($action === 'delete') {
        $delete_stmt = $conn->prepare("DELETE FROM technicians WHERE id = ?");
        $delete_stmt->bind_param("i", $technician_id);
        if ($delete_stmt->execute()) {
            $message = "✓ Technician deleted successfully!";
        }
    }
}

$page_title = $status_filter ? ucfirst($status_filter) . " Technicians" : "All Technicians";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .navbar {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
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
        .page-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background: #f5f7fa;
        }
        .table thead th {
            border: none;
            font-weight: bold;
            color: #333;
        }
        .table tbody tr:hover {
            background: #f5f7fa;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-sm {
            font-size: 12px;
            padding: 5px 10px;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .pagination {
            margin-top: 20px;
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
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="technicians.php" class="<?php echo !$status_filter ? 'active' : ''; ?>">👥 Technicians</a></li>
                    <li><a href="pending.php" class="<?php echo $status_filter === 'Pending' ? 'active' : ''; ?>">⏳ Pending Review</a></li>
                    <li><a href="approved.php" class="<?php echo $status_filter === 'Approved' ? 'active' : ''; ?>">✅ Approved</a></li>
                    <li><a href="rejected.php" class="<?php echo $status_filter === 'Rejected' ? 'active' : ''; ?>">❌ Rejected</a></li>
                    <li><a href="search.php">🔍 Search</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <div class="main-content">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Dashboard menu">
                    <div class="mobile-quick-links-list">
                        <a href="dashboard.php" class="mobile-quick-link">Dashboard</a>
                        <a href="technicians.php" class="mobile-quick-link <?php echo !$status_filter ? 'active' : ''; ?>">Technicians</a>
                        <a href="pending.php" class="mobile-quick-link <?php echo $status_filter === 'Pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="approved.php" class="mobile-quick-link <?php echo $status_filter === 'Approved' ? 'active' : ''; ?>">Approved</a>
                        <a href="rejected.php" class="mobile-quick-link <?php echo $status_filter === 'Rejected' ? 'active' : ''; ?>">Rejected</a>
                        <a href="search.php" class="mobile-quick-link">Search</a>
                    </div>
                </div>

                <h1 class="page-title"><?php echo $page_title; ?></h1>

                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tech = $technicians->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $tech['full_name']; ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?php echo $tech['id']; ?></small>
                                        </td>
                                        <td><?php echo $tech['email']; ?></td>
                                        <td><?php echo $tech['category']; ?></td>
                                        <td><?php echo $tech['location']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($tech['status']); ?>">
                                                <?php echo $tech['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_technician.php?id=<?php echo $tech['id']; ?>" class="btn btn-sm btn-outline-info">View</a>
                                            <?php if ($tech['status'] === 'Pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="technician_id" value="<?php echo $tech['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="technician_id" value="<?php echo $tech['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($technicians->num_rows === 0): ?>
                        <div style="padding: 30px; text-align: center; color: #666;">
                            No technicians found.
                        </div>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <?php if ($pages > 1): ?>
                        <nav aria-label="Page navigation" style="padding: 20px;">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?php echo $status_filter ? '&status='.$status_filter : ''; ?>">First</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>">Next</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pages; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>">Last</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
