<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'migrations' created successfully." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR: Could not execute. " . $e->getMessage());
}
