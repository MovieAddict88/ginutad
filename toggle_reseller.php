<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty(trim($_POST['id']))) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }
    // CSRF token is single-use. Unset it after validation.
    unset($_SESSION['csrf_token']);

    $user_id = trim($_POST['id']);

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT id, is_reseller FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $new_status = $user['is_reseller'] ? 0 : 1;

        try {
            $pdo->beginTransaction();

            // Step 1: Update the user's reseller status
            $update_user_stmt = $pdo->prepare("UPDATE users SET is_reseller = :is_reseller WHERE id = :id");
            $update_user_stmt->bindParam(':is_reseller', $new_status, PDO::PARAM_INT);
            $update_user_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $update_user_stmt->execute();

            if ($new_status == 1) {
                // Step 2: If user is now a reseller, add them to the resellers table
                $insert_reseller_stmt = $pdo->prepare("INSERT INTO resellers (user_id) VALUES (:user_id)");
                $insert_reseller_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $insert_reseller_stmt->execute();
            } else {
                // Step 2: If user is no longer a reseller, remove their data
                // First, get the reseller ID from the user ID
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

                    // Finally, delete the reseller record
                    $delete_reseller_stmt = $pdo->prepare("DELETE FROM resellers WHERE id = :reseller_id");
                    $delete_reseller_stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                    $delete_reseller_stmt->execute();
                }
            }

            // If all queries were successful, commit the transaction
            $pdo->commit();

            // Redirect back to reseller management page
            header('Location: reseller_management.php');
            exit;

        } catch (Exception $e) {
            // If any query fails, roll back the transaction
            $pdo->rollBack();
            die("An error occurred: " . $e->getMessage());
        }

    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>
