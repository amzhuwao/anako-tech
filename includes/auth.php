<?php
session_start();

// Compute app base path so URLs work whether deployed at domain root or a subfolder.
function appBasePath() {
    static $basePath = null;
    if ($basePath !== null) {
        return $basePath;
    }

    $appRoot = realpath(__DIR__ . '/..');
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

    if ($appRoot && $docRoot && strpos($appRoot, $docRoot) === 0) {
        $relative = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
        $relative = '/' . ltrim($relative, '/');
        $basePath = rtrim($relative, '/');
        if ($basePath === '/') {
            $basePath = '';
        }
        return $basePath;
    }

    // Safe fallback for local XAMPP layout.
    $basePath = '/anako-tech';
    return $basePath;
}

function appUrl($path = '') {
    $basePath = appBasePath();
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

function appRedirect($path) {
    header('Location: ' . appUrl($path));
    exit();
}

// Check if user is logged in and role
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isTechnician() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'technician';
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect functions
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        appRedirect('login.php');
    }
}

function redirectIfNotTechnician() {
    redirectIfNotLoggedIn();
    if (!isTechnician()) {
        appRedirect('index.php');
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        appRedirect('index.php');
    }
}

// Hash password using bcrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
