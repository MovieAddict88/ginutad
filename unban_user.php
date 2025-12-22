<?php
// Start session
session_start();

// Check if the user is logged in and is admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Process unban operation
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    // Prepare an update statement to set banned = 0
    $sql = 'UPDATE users SET banned = 0 WHERE id = :id';

    if ($stmt = $pdo->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

        // Set parameters
        $param_id = trim($_GET['id']);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // User unbanned successfully. Redirect to monitoring page
            header('location: monitoring.php');
            exit();
        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }
    }

    // Close statement
    unset($stmt);

    // Close connection
    unset($pdo);
} else {
    // Redirect to monitoring page if id is not provided
    header('location: monitoring.php');
    exit();
}
?>
