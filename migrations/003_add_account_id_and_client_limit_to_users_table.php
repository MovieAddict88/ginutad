<?php
require_once __DIR__ . '/../db_config.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'account_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN account_id INT NULL AFTER is_reseller");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'client_limit'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN client_limit INT NOT NULL DEFAULT 0 AFTER account_id");
    }

    echo "Columns 'account_id' and 'client_limit' added to users table successfully." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR: Could not execute. " . $e->getMessage());
}
