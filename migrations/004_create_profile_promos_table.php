<?php
// 004_create_profile_promos_table.php

// Include the database configuration
require_once 'db_config.php';

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Create the profile_promos table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS profile_promos (
            profile_id INT NOT NULL,
            promo_id INT NOT NULL,
            PRIMARY KEY (profile_id, promo_id),
            FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
        )
    ");

    // Check if promo_id column exists before trying to transfer data
    $stmt = $pdo->query("SHOW COLUMNS FROM vpn_profiles LIKE 'promo_id'");
    $column_exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($column_exists) {
        // Transfer existing data from vpn_profiles to profile_promos
        $pdo->exec("
            INSERT INTO profile_promos (profile_id, promo_id)
            SELECT id, promo_id FROM vpn_profiles WHERE promo_id IS NOT NULL
        ");

        // Find the foreign key constraint name
        $stmt = $pdo->prepare("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vpn_profiles'
              AND COLUMN_NAME = 'promo_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute();
        $constraint = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($constraint) {
            $constraint_name = $constraint['CONSTRAINT_NAME'];
            // Drop the foreign key constraint before dropping the column
            $pdo->exec("ALTER TABLE vpn_profiles DROP FOREIGN KEY `$constraint_name`");
        }

        // Drop the old promo_id column
        $pdo->exec("ALTER TABLE vpn_profiles DROP COLUMN promo_id");
    }

    // Commit transaction
    $pdo->commit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration 004_create_profile_promos_table completed successfully.\n";
?>
