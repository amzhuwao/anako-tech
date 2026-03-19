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

// Handle skill deletion
if (isset($_GET['delete'])) {
    $skill_id = (int)$_GET['delete'];
    deleteSkill($conn, $skill_id);
    header("Location: manage_skills.php");
    exit();
}

// Handle skill addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_name = sanitize($_POST['skill_name']);
    if (!empty($skill_name)) {
        addSkill($conn, $technician_id, $skill_name);
        $_POST = [];
        $success = "Skill added successfully!";
    } else {
        $error = "Skill name cannot be empty";
    }
}

// Get skills
$skills_result = getTechnicianSkills($conn, $technician_id);
$skills = [];
while ($skill = $skills_result->fetch_assoc()) {
    $skills[] = $skill;
}

$suggested_skills = [
    'Solar Installation',
    'Electrical Wiring',
    'CCTV Installation',
    'Network Configuration',
    'Smart Home Setup',
    'Plumbing',
    'Appliance Repair',
    'Maintenance',
    'Troubleshooting',
    'System Installation'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Anako Technician Platform</title>
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
        .form-group input {
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-group input:focus {
            border-color: #02a75a;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .skill-badge {
            display: inline-block;
            background: #02a75a;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            margin: 8px 5px 8px 0;
            font-size: 14px;
            position: relative;
        }
        .skill-badge a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
            cursor: pointer;
        }
        .skill-badge a:hover {
            opacity: 0.8;
        }
        .suggested-skill-btn {
            background: #f0f0f0;
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 12px;
        }
        .suggested-skill-btn:hover {
            background: #02a75a;
            color: white;
            border-color: #02a75a;
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
                    <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                    <a href="edit_profile.php" class="sidebar-link">✏️ Edit Profile</a>
                    <a href="upload_documents.php" class="sidebar-link">📄 Upload Documents</a>
                    <a href="manage_skills.php" class="sidebar-link active">⭐ Manage Skills</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="profile.php">My Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Skills</li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Technician menu">
                    <div class="mobile-quick-links-list">
                        <a href="profile.php" class="mobile-quick-link">My Profile</a>
                        <a href="edit_profile.php" class="mobile-quick-link">Edit Profile</a>
                        <a href="upload_documents.php" class="mobile-quick-link">Upload Documents</a>
                        <a href="manage_skills.php" class="mobile-quick-link active">Manage Skills</a>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Add Skill Form -->
                <div class="card">
                    <div class="card-header">⭐ Add New Skill</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="skill_name">Skill Name</label>
                                <input type="text" id="skill_name" name="skill_name" class="form-control" placeholder="e.g., Solar Installation, Electrical Wiring" required>
                            </div>
                            <button type="submit" class="btn btn-custom">Add Skill</button>
                        </form>

                        <hr>

                        <p><strong>Suggested Skills:</strong></p>
                        <div>
                            <?php foreach ($suggested_skills as $suggested): ?>
                                <button type="button" class="suggested-skill-btn" onclick="addSuggestedSkill('<?php echo $suggested; ?>')">
                                    + <?php echo $suggested; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Your Skills -->
                <div class="card">
                    <div class="card-header">Your Skills (<?php echo count($skills); ?>)</div>
                    <div class="card-body">
                        <?php if (!empty($skills)): ?>
                            <div>
                                <?php foreach ($skills as $skill): ?>
                                    <span class="skill-badge">
                                        <?php echo $skill['skill_name']; ?>
                                        <a href="manage_skills.php?delete=<?php echo $skill['id']; ?>" onclick="return confirm('Remove this skill?')">×</a>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>You haven't added any skills yet. Add one above!</p>
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
    <script>
        function addSuggestedSkill(skillName) {
            document.getElementById('skill_name').value = skillName;
        }
    </script>
</body>
</html>
