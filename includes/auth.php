<?php
session_start();

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
        header("Location: /anako-tech/login.php");
        exit();
    }
}

function redirectIfNotTechnician() {
    redirectIfNotLoggedIn();
    if (!isTechnician()) {
        header("Location: /anako-tech/index.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: /anako-tech/index.php");
        exit();
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
