<?php
// Common functions used throughout the application

// Get technician by ID
function getTechnicianById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM technicians WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get admin by ID
function getAdminById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, username FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get all technicians (with optional filters)
function getAllTechnicians($conn, $status = null) {
    if ($status) {
        $stmt = $conn->prepare("SELECT * FROM technicians WHERE status = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare("SELECT * FROM technicians ORDER BY created_at DESC");
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Get technician skills
function getTechnicianSkills($conn, $technician_id) {
    $stmt = $conn->prepare("SELECT * FROM skills WHERE technician_id = ?");
    $stmt->bind_param("i", $technician_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get technician documents
function getTechnicianDocuments($conn, $technician_id) {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE technician_id = ?");
    $stmt->bind_param("i", $technician_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Add skill for technician
function addSkill($conn, $technician_id, $skill_name) {
    $stmt = $conn->prepare("INSERT INTO skills (technician_id, skill_name) VALUES (?, ?)");
    $stmt->bind_param("is", $technician_id, $skill_name);
    return $stmt->execute();
}

// Delete skill
function deleteSkill($conn, $skill_id) {
    $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?");
    $stmt->bind_param("i", $skill_id);
    return $stmt->execute();
}

// Upload file safely
function uploadFile($file, $upload_dir, $technician_id, $doc_type) {
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid upload request'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension'
        ];
        $message = $error_messages[$file['error']] ?? 'File upload failed';
        return ['success' => false, 'message' => $message];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions, true)) {
        return ['success' => false, 'message' => 'Invalid file extension'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Invalid uploaded file'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected_mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!$detected_mime || !in_array($detected_mime, $allowed_mime_types, true)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        return ['success' => false, 'message' => 'Upload directory is not available'];
    }

    if (!is_writable($upload_dir)) {
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }

    $safe_doc_type = preg_replace('/[^a-z0-9_\-]/i', '_', (string)$doc_type);
    $filename = $technician_id . '_' . $safe_doc_type . '_' . time() . '.' . $file_extension;
    $file_path = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => $file_path];
    }

    return ['success' => false, 'message' => 'File upload failed'];
}

// Get dashboard statistics
function getDashboardStats($conn) {
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM technicians");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as pending FROM technicians WHERE status = 'Pending'");
    $stats['pending'] = $result->fetch_assoc()['pending'];
    
    $result = $conn->query("SELECT COUNT(*) as approved FROM technicians WHERE status = 'Approved'");
    $stats['approved'] = $result->fetch_assoc()['approved'];
    
    $result = $conn->query("SELECT COUNT(*) as rejected FROM technicians WHERE status = 'Rejected'");
    $stats['rejected'] = $result->fetch_assoc()['rejected'];
    
    return $stats;
}

// Search technicians
function searchTechnicians($conn, $skill = null, $location = null, $category = null, $experience = null) {
    $query = "SELECT DISTINCT t.* FROM technicians t LEFT JOIN skills s ON t.id = s.technician_id WHERE 1=1";
    $params = [];
    $types = '';

    if ($skill) {
        $query .= " AND s.skill_name LIKE ?";
        $skill_param = "%$skill%";
        $params[] = $skill_param;
        $types .= 's';
    }

    if ($location) {
        $query .= " AND t.location LIKE ?";
        $location_param = "%$location%";
        $params[] = $location_param;
        $types .= 's';
    }

    if ($category) {
        $query .= " AND t.category LIKE ?";
        $category_param = "%$category%";
        $params[] = $category_param;
        $types .= 's';
    }

    if ($experience) {
        $query .= " AND t.experience >= ?";
        $params[] = $experience;
        $types .= 'i';
    }

    $query .= " ORDER BY t.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}
?>
