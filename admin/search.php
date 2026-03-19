<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/functions.php';

redirectIfNotAdmin();

$skill = isset($_GET['skill']) ? sanitize($_GET['skill']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$experience = isset($_GET['experience']) ? (int)$_GET['experience'] : null;

$results = null;
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (!empty($skill) || !empty($location) || !empty($category) || $experience)) {
    $results = searchTechnicians($conn, $skill, $location, $category, $experience);
    $search_performed = true;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $technician_id = (int)($_POST['technician_id'] ?? 0);

    if ($action === 'approve' && $technician_id) {
        $new_status = 'Approved';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✓ Technician approved successfully!";
        }
    } elseif ($action === 'reject' && $technician_id) {
        $new_status = 'Rejected';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✗ Technician rejected successfully!";
        }
    }
}

$categories = ['Solar Technician', 'Electrician', 'CCTV Installer', 'Network Technician', 'Smart Home Installer', 'Plumber', 'Appliance Repair Technician'];

// Get unique locations and skills for suggesting
$locations_stmt = $conn->query("SELECT DISTINCT location FROM technicians WHERE location IS NOT NULL ORDER BY location");
$locations = [];
while ($loc = $locations_stmt->fetch_assoc()) {
    if (!empty($loc['location'])) {
        $locations[] = $loc['location'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Technicians - Admin Dashboard</title>
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
        .search-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group select {
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #02a75a;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-search {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            border: none;
            color: white;
            font-weight: bold;
        }
        .btn-search:hover {
            color: white;
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
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
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
                    <li><a href="technicians.php">👥 Technicians</a></li>
                    <li><a href="pending.php">⏳ Pending Review</a></li>
                    <li><a href="approved.php">✅ Approved</a></li>
                    <li><a href="rejected.php">❌ Rejected</a></li>
                    <li><a href="search.php" class="active">🔍 Search</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <div class="main-content">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Search</li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Dashboard menu">
                    <div class="mobile-quick-links-list">
                        <a href="dashboard.php" class="mobile-quick-link">Dashboard</a>
                        <a href="technicians.php" class="mobile-quick-link">Technicians</a>
                        <a href="pending.php" class="mobile-quick-link">Pending</a>
                        <a href="approved.php" class="mobile-quick-link">Approved</a>
                        <a href="rejected.php" class="mobile-quick-link">Rejected</a>
                        <a href="search.php" class="mobile-quick-link active">Search</a>
                    </div>
                </div>

                <h1 class="page-title">🔍 Search Technicians</h1>

                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="search-card">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="skill">Skill</label>
                                    <input type="text" id="skill" name="skill" class="form-control" value="<?php echo $skill; ?>" placeholder="e.g., Solar Installation">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <select id="location" name="location" class="form-control">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?php echo $loc; ?>" <?php echo $location === $loc ? 'selected' : ''; ?>><?php echo $loc; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="experience">Minimum Experience (years)</label>
                                    <input type="number" id="experience" name="experience" class="form-control" value="<?php echo $experience ?? ''; ?>" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-search">🔍 Search</button>
                                <a href="search.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results -->
                <?php if ($search_performed): ?>
                    <h3>Search Results</h3>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Category</th>
                                            <th>Location</th>
                                            <th>Experience</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($tech = $results->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $tech['full_name']; ?></strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?php echo $tech['id']; ?></small>
                                                </td>
                                                <td><?php echo $tech['email']; ?></td>
                                                <td><?php echo $tech['category']; ?></td>
                                                <td><?php echo $tech['location']; ?></td>
                                                <td><?php echo $tech['experience']; ?> yrs</td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower($tech['status']); ?>">
                                                        <?php echo $tech['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_technician.php?id=<?php echo $tech['id']; ?>" class="btn btn-sm btn-outline-info">View</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; color: #666;">
                            <p>No technicians found matching your search criteria.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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
