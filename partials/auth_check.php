<?php
// partials/auth_check.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: /library/login.php");
    exit();
}

// Function to check if current user is an admin
function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Redirect non-admins to the dashboard or show an error
        header("Location: /library/index.php?error=access_denied");
        exit();
    }
}
?>
