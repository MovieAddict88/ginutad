<?php
require_once 'db_config.php';

try {
    $sql = "ALTER TABLE users
            ADD COLUMN status VARCHAR(255) NOT NULL DEFAULT 'unpaid',
            ADD COLUMN pay_amount DECIMAL(10, 2),
            ADD COLUMN payment_date DATETIME,
            ADD COLUMN payment_method VARCHAR(255),
            ADD COLUMN reference_number VARCHAR(255)";
    $pdo->exec($sql);
    echo "Table 'users' modified successfully.";
} catch (PDOException $e) {
    die("ERROR: Could not able to execute $sql. " . $e->getMessage());
}
?>