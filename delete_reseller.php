<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty(trim($_POST['id'])) && isset($_POST['admin_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }
    // CSRF token is single-use. Unset it after validation.
    unset($_SESSION['csrf_token']);

    $user_id = trim($_POST['id']);
    $admin_password = $_POST['admin_password'];

    // Verify admin password
    $admin_id = $_SESSION['id'];
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :admin_id");
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($admin_password, $admin['password'])) {
        try {
            $pdo->beginTransaction();

            // Get the reseller ID from the user ID
            $get_reseller_id_stmt = $pdo->prepare("SELECT id FROM resellers WHERE user_id = :user_id");
            $get_reseller_id_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $get_reseller_id_stmt->execute();
            $reseller = $get_reseller_id_stmt->fetch(PDO::FETCH_ASSOC);

            if ($reseller) {
                $reseller_id = $reseller['id'];

                // Delete associated commissions
                $delete_commissions_stmt = $pdo->prepare("DELETE FROM commissions WHERE reseller_id = :reseller_id");
                $delete_commissions_stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                $delete_commissions_stmt->execute();

                // Delete associated clients
                $delete_clients_stmt = $pdo->prepare("DELETE FROM reseller_clients WHERE reseller_id = :reseller_id");
                $delete_clients_stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                $delete_clients_stmt->execute();

                // Delete the reseller record
                $delete_reseller_stmt = $pdo->prepare("DELETE FROM resellers WHERE id = :reseller_id");
                $delete_reseller_stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                $delete_reseller_stmt->execute();
            }

            // Finally, delete the user record
            $delete_user_stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $delete_user_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $delete_user_stmt->execute();

            $pdo->commit();

            header('Location: reseller_management.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("An error occurred: " . $e->getMessage());
            header('Location: reseller_management.php?error=delete_failed');
            exit;
        }
    } else {
        // Redirect with error
        header('Location: reseller_management.php?error=invalid_password');
        exit;
    }
} else {
    echo "Invalid request.";
}
?>
