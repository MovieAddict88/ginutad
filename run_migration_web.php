<?php
require_once 'db_config.php';

try {
    // Create migrations table if it doesn't exist
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Check if the migration has already been run
    $migration_name = '20251222_migration_fix.sql';
    $stmt = $pdo->prepare('SELECT * FROM migrations WHERE migration = :migration');
    $stmt->execute(['migration' => $migration_name]);

    if ($stmt->rowCount() > 0) {
        echo "Migration already completed.";
        exit;
    }

    // Read the SQL migration file
    $sql = file_get_contents('migrations/20251222_migration_fix.sql');

    // Execute the SQL script
    $pdo->exec($sql);

    // Log the migration
    $stmt = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
    $stmt->execute(['migration' => $migration_name]);

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    // Handle any errors
    die("Migration failed: " . $e->getMessage());
}
?>
