<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'accounts' created successfully." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR: Could not execute. " . $e->getMessage());
}
