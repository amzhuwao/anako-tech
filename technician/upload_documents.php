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

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && isset($_POST['document_type'])) {
        $doc_type = sanitize($_POST['document_type']);
        $upload_dir = '../uploads/documents/';
        
        $file_result = uploadFile($_FILES['document'], $upload_dir, $technician_id, $doc_type);
        
        if ($file_result['success']) {
            $stmt = $conn->prepare("INSERT INTO documents (technician_id, document_type, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $technician_id, $doc_type, $file_result['path']);
            if ($stmt->execute()) {
                $success = "Document uploaded successfully!";
            } else {
                $error = "Error saving document to database";
            }
        } else {
            $error = $file_result['message'];
        }
    }
}

// Get documents
$documents_result = getTechnicianDocuments($conn, $technician_id);
$documents = [];
while ($doc = $documents_result->fetch_assoc()) {
    $documents[] = $doc;
}

$doc_types = ['National ID', 'Certificate', 'Training Proof', 'Portfolio Image'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - Anako Technician Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }
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
        .document-item {
            background: #f5f7fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .document-item > div:first-child {
            min-width: 0;
        }
        .document-item > div:last-child {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .document-type {
            font-weight: bold;
            color: #02a75a;
            overflow-wrap: anywhere;
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
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            .document-item {
                align-items: flex-start;
            }
            .document-item > div:last-child {
                width: 100%;
                justify-content: stretch;
            }
            .document-item > div:last-child .btn {
                flex: 1 1 140px;
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
                    <a href="upload_documents.php" class="sidebar-link active">📄 Upload Documents</a>
                    <a href="manage_skills.php" class="sidebar-link">⭐ Manage Skills</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="mobile-breadcrumb-wrap">
                    <nav class="mobile-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo appUrl('index.php'); ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="profile.php">My Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Upload Documents</li>
                        </ol>
                    </nav>
                </div>
                <div class="mobile-quick-links" aria-label="Technician menu">
                    <div class="mobile-quick-links-list">
                        <a href="profile.php" class="mobile-quick-link">My Profile</a>
                        <a href="edit_profile.php" class="mobile-quick-link">Edit Profile</a>
                        <a href="upload_documents.php" class="mobile-quick-link active">Upload Documents</a>
                        <a href="manage_skills.php" class="mobile-quick-link">Manage Skills</a>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="card">
                    <div class="card-header">📄 Upload Documents</div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="document_type">Document Type</label>
                                <select id="document_type" name="document_type" class="form-control" required>
                                    <option value="">Select document type</option>
                                    <?php foreach ($doc_types as $type): ?>
                                        <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="document">File (PDF, JPG, PNG - max 5MB)</label>
                                <input type="file" id="document" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Supported formats: PDF, JPG, PNG</small>
                            </div>

                            <button type="submit" class="btn btn-custom">Upload Document</button>
                        </form>
                    </div>
                </div>

                <!-- Documents List -->
                <div class="card">
                    <div class="card-header">📋 Your Documents</div>
                    <div class="card-body">
                        <?php if (!empty($documents)): ?>
                            <div>
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-item">
                                        <div>
                                            <div class="document-type"><?php echo ucfirst($doc['document_type']); ?></div>
                                            <small class="text-muted">Uploaded: <?php echo date('M d, Y H:i', strtotime($doc['uploaded_at'])); ?></small>
                                        </div>
                                        <div>
                                            <a href="../uploads/documents/<?php echo basename($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="upload_documents.php?delete=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this document?')">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No documents uploaded yet. Upload your first document above.</p>
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
