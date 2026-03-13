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

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $bio = sanitize($_POST['bio']);
    $experience = (int)$_POST['experience'];

    $stmt = $conn->prepare("UPDATE technicians SET bio = ?, experience = ? WHERE id = ?");
    $stmt->bind_param("sii", $bio, $experience, $technician_id);
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $technician = getTechnicianById($conn, $technician_id);
    } else {
        $error = "Error updating profile";
    }
}

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if (isset($_FILES['profile_photo'])) {
        $upload_dir = '../uploads/profile_photos/';
        $file_result = uploadFile($_FILES['profile_photo'], $upload_dir, $technician_id, 'profile');
        
        if ($file_result['success']) {
            $stmt = $conn->prepare("UPDATE technicians SET profile_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $file_result['filename'], $technician_id);
            if ($stmt->execute()) {
                $success = "Profile photo uploaded successfully!";
                $technician = getTechnicianById($conn, $technician_id);
            }
        } else {
            $error = $file_result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Anako Technician Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .navbar {
            background: linear-gradient(135deg, #02a75a 0%, #018a4a 100%);
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
        }
        .btn-custom:hover {
            color: white;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c3e6cb;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            border: 4px solid #02a75a;
            margin-bottom: 20px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #02a75a;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .container {
            max-width: 900px;
        }
    </style>
    <link rel="stylesheet" href="/anako-tech/assets/css/affiliate-theme.css">
    <link rel="icon" type="image/png" href="/anako-tech/assets/images/branding/anako-favicon.png">
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
                    <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                    <a href="edit_profile.php" class="sidebar-link active">✏️ Edit Profile</a>
                    <a href="upload_documents.php" class="sidebar-link">📄 Upload Documents</a>
                    <a href="manage_skills.php" class="sidebar-link">⭐ Manage Skills</a>
                </div>
            </div>

            <div class="col-md-9">
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Profile Photo Upload -->
                <div class="card">
                    <div class="card-header">📸 Profile Photo</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <?php if ($technician['profile_photo']): ?>
                                    <img src="../uploads/profile_photos/<?php echo $technician['profile_photo']; ?>" class="profile-photo" alt="Profile">
                                <?php else: ?>
                                    <div class="profile-photo" style="background: #02a75a; color: white; display: flex; align-items: center; justify-content: center; font-size: 50px;">📸</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload_photo">
                                    <div class="form-group">
                                        <label for="profile_photo">Upload Photo (JPG, PNG max 5MB)</label>
                                        <input type="file" id="profile_photo" name="profile_photo" class="form-control" accept=".jpg,.jpeg,.png" required>
                                    </div>
                                    <button type="submit" class="btn btn-custom">Upload Photo</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="card">
                    <div class="card-header">✏️ Edit Profile Information</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" class="form-control" value="<?php echo $technician['full_name']; ?>" disabled>
                                <small class="text-muted">Contact admin to change this</small>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control" value="<?php echo $technician['email']; ?>" disabled>
                                <small class="text-muted">Contact admin to change this</small>
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" id="category" class="form-control" value="<?php echo $technician['category']; ?>" disabled>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <input type="text" id="location" class="form-control" value="<?php echo $technician['location']; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="experience">Years of Experience</label>
                                        <input type="number" id="experience" name="experience" class="form-control" value="<?php echo $technician['experience']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bio">Biography</label>
                                <textarea id="bio" name="bio" class="form-control" rows="5" placeholder="Tell us about yourself..."><?php echo $technician['bio']; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-custom">Save Changes</button>
                            <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
