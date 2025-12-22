<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Get the profile ID from the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $profile_id = $_GET['id'];

    // Prepare a delete statement
    $sql = 'DELETE FROM vpn_profiles WHERE id = :id';

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('location: profiles.php');
            exit;
        } else {
            echo 'Something went wrong. Please try again later.';
        }
    }
} else {
    header('location: profiles.php');
    exit;
}
?>
