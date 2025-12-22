<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Check if the user is logged in, has the 'admin' role, and the role is explicitly 'admin'
if ($current_page !== 'login.php' && (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !is_admin())) {
    // If not, redirect to the login page and exit
    header('location: login.php');
    exit;
}
