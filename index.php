<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Redirect to the dashboard
if (isset($_SESSION['is_reseller']) && $_SESSION['is_reseller']) {
    header('location: reseller_dashboard.php');
} else {
    header('location: dashboard.php');
}
exit;
