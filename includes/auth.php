<?php
// ============================================================
// Authentication & Session Helper
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verify user password supporting bcrypt and testing fallback
 */
function verify_password($input_password, $stored_password) {
    if (password_verify($input_password, $stored_password)) {
        return true;
    }
    if ($input_password === $stored_password) {
        return true;
    }
    if (md5($input_password) === $stored_password) {
        return true;
    }
    return false;
}

/**
 * Hash password securely
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Check if a farmer is currently logged in
 */
function isFarmerLoggedIn() {
    return isset($_SESSION['farmer_id']) && !empty($_SESSION['farmer_id']);
}

/**
 * Check if an admin is currently logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require Farmer Login Guard
 */
function requireFarmerLogin() {
    if (!isFarmerLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access your farmer portal.";
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Require Admin Login Guard
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['flash_error'] = "Admin access restricted. Please log in first.";
        header("Location: ../admin/login.php");
        exit();
    }
}

/**
 * Set flash alert message
 */
function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Render flash alert message HTML
 */
function displayFlash() {
    $output = '';
    if (isset($_SESSION['flash_success'])) {
        $output .= '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        $output .= '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    if (isset($_SESSION['flash_info'])) {
        $output .= '<div class="alert alert-info"><i class="fas fa-info-circle"></i> ' . htmlspecialchars($_SESSION['flash_info']) . '</div>';
        unset($_SESSION['flash_info']);
    }
    return $output;
}
?>
