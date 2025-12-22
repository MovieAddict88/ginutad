<?php
require_once 'db_config.php';

try {
    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'testreseller'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "Test reseller already exists.\n";
        exit;
    }

    // Insert the test reseller
    $sql = "INSERT INTO users (username, password, first_name, last_name, address, contact_number, is_reseller, role, account_id, client_limit, credits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'testreseller',
        password_hash('password123', PASSWORD_DEFAULT),
        'Test Reseller',
        'User',
        '123 Test St',
        '123-456-7890',
        1,
        'reseller',
        1,
        10,
        1000
    ]);
    echo "Test reseller created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
