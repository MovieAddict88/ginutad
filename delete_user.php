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

// Check if the user exists and is not admin before banning
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id = trim($_GET['id']);
    
    // Check if user is admin
    $check_sql = 'SELECT role FROM users WHERE id = :id';
    if ($check_stmt = $pdo->prepare($check_sql)) {
        $check_stmt->bindParam(':id', $param_id, PDO::PARAM_INT);
        $param_id = $id;
        
        if ($check_stmt->execute()) {
            if ($check_stmt->rowCount() == 1) {
                $user = $check_stmt->fetch();
                if ($user['role'] === 'admin') {
                    header('location: index.php?message=Admin user cannot be deleted.');
                    exit();
                }
            }
        }
        unset($check_stmt);
    }
}

// Process ban operation after confirmation
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $user_id = trim($_POST['id']);

    // Check if user is admin before deleting
    $check_sql = 'SELECT role FROM users WHERE id = :id';
    if ($check_stmt = $pdo->prepare($check_sql)) {
        $check_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        
        if ($check_stmt->execute()) {
            if ($check_stmt->rowCount() == 1) {
                $user = $check_stmt->fetch();
                if ($user['role'] === 'admin') {
                    header('location: index.php?message=Admin user cannot be deleted.');
                    exit();
                }
            } else {
                header('location: index.php?message=User not found.');
                exit();
            }
        }
        unset($check_stmt);
    }

    // Start a transaction
    $pdo->beginTransaction();

    try {
        // First, delete any active VPN sessions for the user
        $sql_terminate = 'DELETE FROM vpn_sessions WHERE user_id = :user_id';
        $stmt_terminate = $pdo->prepare($sql_terminate);
        $stmt_terminate->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_terminate->execute();

        // Then, delete the user from the users table
        $sql_delete = 'DELETE FROM users WHERE id = :id';
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt_delete->execute();

        // Commit the transaction
        $pdo->commit();

        // User deleted successfully. Redirect to landing page
        header('location: index.php');
        exit();

    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();
        echo 'Oops! Something went wrong. Please try again later.';
    }

    // Close statements
    unset($stmt_delete);
    unset($stmt_terminate);

    // Close connection
    unset($pdo);
} else {
    // Check existence of id parameter
    if (empty(trim($_GET['id']))) {
        // URL doesn't contain id parameter. Redirect to error page
        header('location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Delete User</title>
    <link rel='stylesheet' href='style.css'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class='container'>
        <div class="page-header">
            <h2>Delete User</h2>
            <div class="page-actions">
                <a class='btn btn-secondary' href='index.php'>
                    <span class="material-icons">arrow_back</span>
                    Back to Users
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
            <form action='delete_user.php' method='post'>
                    <div class='alert alert-danger'>
                         <input type='hidden' name='id' value='<?php echo htmlspecialchars(trim($_GET['id'])); ?>'/>
                    <p>Are you sure you want to delete this user? This action is irreversible and will permanently remove all their data.</p>
                        <div class="form-group" style="margin-top: 20px;">
                        <input type='submit' value='Yes, Delete User' class='btn btn-danger'>
                            <a class='btn btn-link' href='index.php'>Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>