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
                    header('location: index.php?message=Admin user cannot be banned.');
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

    // Start a transaction
    $pdo->beginTransaction();

    try {
        // Prepare an update statement to set banned = 1 and device_id = NULL
        $sql_ban = 'UPDATE users SET banned = 1, device_id = NULL WHERE id = :id';
        $stmt_ban = $pdo->prepare($sql_ban);
        $stmt_ban->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt_ban->execute();

        // Terminate any active VPN sessions
        $sql_terminate = 'UPDATE vpn_sessions SET end_time = NOW() WHERE user_id = :user_id AND end_time IS NULL';
        $stmt_terminate = $pdo->prepare($sql_terminate);
        $stmt_terminate->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_terminate->execute();

        // Commit the transaction
        $pdo->commit();

        // User banned successfully. Redirect to landing page
        header('location: index.php');
        exit();

    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();
        echo 'Oops! Something went wrong. Please try again later.';
    }

    // Close statements
    unset($stmt_ban);
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
    <title>Ban User</title>
    <link rel='stylesheet' href='style.css'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class='container'>
        <div class="page-header">
            <h2>Ban User</h2>
            <div class="page-actions">
                <a class='btn btn-secondary' href='index.php'>
                    <span class="material-icons">arrow_back</span>
                    Back to Users
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action='ban_user.php' method='post'>
                    <div class='alert alert-danger'>
                        <input type='hidden' name='id' value='<?php echo trim($_GET['id']); ?>'/>
                        <p>Are you sure you want to ban this user? This will immediately terminate any active sessions.</p>
                        <div class="form-group" style="margin-top: 20px;">
                            <input type='submit' value='Yes, Ban User' class='btn btn-danger'>
                            <a class='btn btn-link' href='index.php'>Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>