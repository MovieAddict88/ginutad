<?php
require_once 'db_config.php';

try {
    // Ensure accounts table has a test account
    $stmt = $pdo->prepare("INSERT INTO accounts (id, name, price) VALUES (1, 'Test Account', 10.00) ON DUPLICATE KEY UPDATE name='Test Account', price=10.00");
    $stmt->execute();
    $account_id = 1;

    // Delete existing reseller user to ensure a clean state
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'reseller'");
    $stmt->execute();

    // Create a new reseller user
    $username = 'reseller';
    $password = password_hash('reseller123', PASSWORD_DEFAULT);
    $first_name = 'Test';
    $last_name = 'Reseller';
    $address = '123 Test Street';
    $contact_number = '555-1234';
    $credits = 100.00;
    $role = 'reseller';
    $is_reseller = 1;
    $client_limit = 10;

    $stmt = $pdo->prepare(
        "INSERT INTO users (username, password, first_name, last_name, address, contact_number, credits, role, is_reseller, client_limit, account_id)
         VALUES (:username, :password, :first_name, :last_name, :address, :contact_number, :credits, :role, :is_reseller, :client_limit, :account_id)"
    );

    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':address' => $address,
        ':contact_number' => $contact_number,
        ':credits' => $credits,
        ':role' => $role,
        ':is_reseller' => $is_reseller,
        ':client_limit' => $client_limit,
        ':account_id' => $account_id
    ]);

    $user_id = $pdo->lastInsertId();

    // Insert into resellers table
    $stmt = $pdo->prepare("INSERT INTO resellers (user_id) VALUES (:user_id)");
    $stmt->execute([':user_id' => $user_id]);

    echo "Test reseller created successfully.";

} catch (PDOException $e) {
    die("Error setting up test user: " . $e->getMessage());
}
?>