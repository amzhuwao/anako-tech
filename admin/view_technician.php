<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/functions.php';

redirectIfNotAdmin();

$technician_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$technician_id) {
    header("Location: technicians.php");
    exit();
}

$technician = getTechnicianById($conn, $technician_id);
if (!$technician) {
    header("Location: technicians.php");
    exit();
}

$skills = [];
$skills_result = getTechnicianSkills($conn, $technician_id);
while ($skill = $skills_result->fetch_assoc()) {
    $skills[] = $skill;
}

$documents = [];
$docs_result = getTechnicianDocuments($conn, $technician_id);
while ($doc = $docs_result->fetch_assoc()) {
    $documents[] = $doc;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action']);

    if ($action === 'approve') {
        $new_status = 'Approved';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✓ Technician approved successfully!";
            $technician = getTechnicianById($conn, $technician_id);
        }
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
        $update_stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $technician_id);
        if ($update_stmt->execute()) {
            $message = "✗ Technician rejected successfully!";
            $technician = getTechnicianById($conn, $technician_id);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Technician - Admin Dashboard</title>
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
        .card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 30px;
        }
        .card-header {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            color: white;
            border: none;
            font-weight: bold;
        }
        .profile-photo {
            width: 180px;
            height: 180px;
            border-radius: 10px;
            object-fit: cover;
            border: 4px solid #02a75a;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
            font-size: 12px;
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
        .skill-badge {
            display: inline-block;
            background: #02a75a;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            margin: 5px;
            font-size: 12px;
        }
        .info-row {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        .btn-custom {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            border: none;
            color: white;
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
                    <li><a href="technicians.php" class="active">👥 Technicians</a></li>
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
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="technicians.php">Technicians</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $technician['full_name']; ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Dashboard menu">
                    <div class="mobile-quick-links-list">
                        <a href="dashboard.php" class="mobile-quick-link">Dashboard</a>
                        <a href="technicians.php" class="mobile-quick-link active">Technicians</a>
                        <a href="pending.php" class="mobile-quick-link">Pending</a>
                        <a href="approved.php" class="mobile-quick-link">Approved</a>
                        <a href="rejected.php" class="mobile-quick-link">Rejected</a>
                        <a href="search.php" class="mobile-quick-link">Search</a>
                    </div>
                </div>

                <a href="technicians.php" class="btn btn-outline-secondary mb-3">← Back to Technicians</a>
                <h1 class="page-title"><?php echo $technician['full_name']; ?></h1>

                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Profile Info -->
                <div class="card">
                    <div class="card-header">👤 Profile Information</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <?php if ($technician['profile_photo']): ?>
                                    <img src="../uploads/profile_photos/<?php echo $technician['profile_photo']; ?>" class="profile-photo" alt="Profile">
                                <?php else: ?>
                                    <div class="profile-photo" style="background: #02a75a; color: white; display: flex; align-items: center; justify-content: center; font-size: 60px;">👤</div>
                                <?php endif; ?>
                                <span class="status-badge status-<?php echo strtolower($technician['status']); ?>">
                                    <?php echo $technician['status']; ?>
                                </span>
                            </div>
                            <div class="col-md-9">
                                <div class="info-row">
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value"><?php echo $technician['full_name']; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo $technician['email']; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Phone</div>
                                    <div class="info-value"><?php echo $technician['phone']; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Category</div>
                                    <div class="info-value"><?php echo $technician['category']; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Location</div>
                                    <div class="info-value"><?php echo $technician['location']; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Experience</div>
                                    <div class="info-value"><?php echo $technician['experience']; ?> years</div>
                                </div>
                                <div class="info-row" style="border-bottom: none;">
                                    <div class="info-label">Member Since</div>
                                    <div class="info-value"><?php echo date('F d, Y', strtotime($technician['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biography -->
                <?php if ($technician['bio']): ?>
                    <div class="card">
                        <div class="card-header">📝 Biography</div>
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($technician['bio'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                    <div class="card">
                        <div class="card-header">⭐ Skills</div>
                        <div class="card-body">
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-badge"><?php echo $skill['skill_name']; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Documents -->
                <?php if (!empty($documents)): ?>
                    <div class="card">
                        <div class="card-header">📄 Documents</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Uploaded</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $doc): ?>
                                            <tr>
                                                <td><?php echo ucfirst($doc['document_type']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                                <td>
                                                    <a href="../uploads/documents/<?php echo basename($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">🔧 Actions</div>
                    <div class="card-body">
                        <?php if ($technician['status'] === 'Pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-custom btn-success">✓ Approve Technician</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-custom btn-danger">✗ Reject Technician</button>
                            </form>
                        <?php else: ?>
                            <p><strong>Status:</strong> <?php echo $technician['status']; ?></p>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('This action cannot be undone. Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-outline-danger">🗑️ Delete Technician</button>
                            </form>
                        <?php endif; ?>
                    </div>
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
