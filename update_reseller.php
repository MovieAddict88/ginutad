<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';

// Check if the user is an admin
if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reseller_management.php');
    exit;
}

// Validate input
$reseller_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
$password = $_POST['password']; // Don't sanitize password here, we'll hash it
$account_id = filter_input(INPUT_POST, 'account_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

if (empty($reseller_id) || empty($username) || empty($first_name) || empty($account_id) || empty($quantity)) {
    // Handle missing required fields
    header('Location: edit_reseller.php?id=' . $reseller_id . '&error=missing_fields');
    exit;
}

// Update reseller in the database
try {
    // Server-side calculation of credits for security
    $stmt_price = $pdo->prepare("SELECT price FROM accounts WHERE id = ?");
    $stmt_price->execute([$account_id]);
    $account = $stmt_price->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        header('Location: edit_reseller.php?id=' . $reseller_id . '&error=invalid_account_type');
        exit;
    }
    $credits = $account['price'] * $quantity;

    $params = [$username, $first_name, $address, $contact_number, $account_id, $quantity, $credits];
    $sql = "UPDATE users SET username = ?, first_name = ?, address = ?, contact_number = ?, account_id = ?, client_limit = ?, credits = ?";

    // Check if a new password was provided
    if (!empty($password)) {
        if (strlen($password) < 6) {
            header('Location: edit_reseller.php?id=' . $reseller_id . '&error=password_too_short');
            exit;
        }
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ? AND is_reseller = 1";
    $params[] = $reseller_id;

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);

    if ($success) {
        header('Location: reseller_management.php?success=reseller_updated');
        exit;
    } else {
        header('Location: reseller_management.php?error=update_failed');
        exit;
    }
} catch (PDOException $e) {
    // Log the error and redirect
    error_log("Database error updating reseller: " . $e->getMessage());
    header('Location: reseller_management.php?error=database_error');
    exit;
}
