<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/functions.php';

redirectIfNotTechnician();

$technician_id = $_SESSION['user_id'];
$technician = getTechnicianById($conn, $technician_id);

if (!$technician) {
    header("Location: ../logout.php");
    exit();
}

// Get skills
$skills_result = getTechnicianSkills($conn, $technician_id);
$skills = [];
while ($skill = $skills_result->fetch_assoc()) {
    $skills[] = $skill;
}

// Get documents
$documents_result = getTechnicianDocuments($conn, $technician_id);
$documents = [];
while ($doc = $documents_result->fetch_assoc()) {
    $documents[] = $doc;
}

// Handle skill addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_skill') {
    $skill_name = sanitize($_POST['skill_name']);
    if (!empty($skill_name)) {
        addSkill($conn, $technician_id, $skill_name);
        header("Location: profile.php");
        exit();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $bio = sanitize($_POST['bio']);
    $experience = (int)$_POST['experience'];

    $stmt = $conn->prepare("UPDATE technicians SET bio = ?, experience = ? WHERE id = ?");
    $stmt->bind_param("sii", $bio, $experience, $technician_id);
    if ($stmt->execute()) {
        $_SESSION['status'] = 'Profile updated successfully';
        header("Location: profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Anako Technician Platform</title>
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
        }
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #02a75a;
        }
        .profile-info h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .profile-status {
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
        .btn-custom {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
            border: none;
            color: white;
            font-weight: bold;
        }
        .btn-custom:hover {
            color: white;
            opacity: 0.9;
        }
        .skill-badge {
            display: inline-block;
            background: #02a75a;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            font-size: 12px;
        }
        .container {
            max-width: 900px;
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
        @media (max-width: 767.98px) {
            .sidebar-nav {
                display: none;
            }
            .mobile-breadcrumb-wrap {
                display: block;
            }
            .mobile-quick-links {
                display: block;
            }
        }
        .sidebar-nav {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar-link {
            display: block;
            padding: 15px;
            text-decoration: none;
            color: #333;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .sidebar-link:hover {
            background: #f5f7fa;
            border-left-color: #02a75a;
            color: #02a75a;
        }
        .sidebar-link.active {
            background: #02a75a;
            color: white;
            border-left-color: #018a4a;
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
            <a class="navbar-brand" href="../index.php">Anako Technician</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo $technician['full_name']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-3">
                <div class="sidebar-nav">
                    <a href="profile.php" class="sidebar-link active">👤 My Profile</a>
                    <a href="edit_profile.php" class="sidebar-link">✏️ Edit Profile</a>
                    <a href="upload_documents.php" class="sidebar-link">📄 Upload Documents</a>
                    <a href="manage_skills.php" class="sidebar-link">⭐ Manage Skills</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Technician menu">
                    <div class="mobile-quick-links-list">
                        <a href="profile.php" class="mobile-quick-link active">My Profile</a>
                        <a href="edit_profile.php" class="mobile-quick-link">Edit Profile</a>
                        <a href="upload_documents.php" class="mobile-quick-link">Upload Documents</a>
                        <a href="manage_skills.php" class="mobile-quick-link">Manage Skills</a>
                    </div>
                </div>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <?php if ($technician['profile_photo']): ?>
                                <img src="../uploads/profile_photos/<?php echo $technician['profile_photo']; ?>" class="profile-photo" alt="Profile">
                            <?php else: ?>
                                <div class="profile-photo" style="background: #02a75a; color: white; display: flex; align-items: center; justify-content: center; font-size: 40px;">👤</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <div class="profile-info">
                                <h2><?php echo $technician['full_name']; ?></h2>
                                <p><strong>Category:</strong> <?php echo $technician['category']; ?></p>
                                <p><strong>Email:</strong> <?php echo $technician['email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $technician['phone']; ?></p>
                                <p><strong>Location:</strong> <?php echo $technician['location']; ?></p>
                                <p><strong>Experience:</strong> <?php echo $technician['experience']; ?> years</p>
                                <span class="profile-status status-<?php echo strtolower($technician['status']); ?>">
                                    <?php echo $technician['status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bio Section -->
                <div class="card">
                    <div class="card-header">
                        📝 Biography
                    </div>
                    <div class="card-body">
                        <p><?php echo $technician['bio'] ?? 'No biography added yet. <a href="edit_profile.php">Add one now</a>.'; ?></p>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="card">
                    <div class="card-header">
                        ⭐ Skills
                    </div>
                    <div class="card-body">
                        <?php if (!empty($skills)): ?>
                            <div>
                                <?php foreach ($skills as $skill): ?>
                                    <span class="skill-badge">
                                        <?php echo $skill['skill_name']; ?>
                                        <a href="manage_skills.php?delete=<?php echo $skill['id']; ?>" style="color: white; margin-left: 10px; text-decoration: none;">×</a>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No skills added yet. <a href="manage_skills.php">Add skills</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="card">
                    <div class="card-header">
                        📄 Documents
                    </div>
                    <div class="card-body">
                        <?php if (!empty($documents)): ?>
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
                                                <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                                <td>
                                                    <a href="../uploads/documents/<?php echo basename($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No documents uploaded yet. <a href="upload_documents.php">Upload documents</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <a href="edit_profile.php" class="btn btn-custom">Edit Profile</a>
                    <a href="upload_documents.php" class="btn btn-custom">Upload Documents</a>
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
