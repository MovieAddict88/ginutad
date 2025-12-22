<?php
require_once 'db_config.php';

// Get all migration files
$migration_files = glob('migrations/*.php');

// Create migrations table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Get all migrations that have already been run
$stmt = $pdo->query("SELECT migration FROM migrations");
$run_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($migration_files as $file) {
    $migration_name = basename($file);

    if (!in_array($migration_name, $run_migrations)) {
        try {
            // Run the migration
            require_once $file;
            echo "Migration successful: $migration_name<br>";

            // Add the migration to the migrations table
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $migration_name]);
        } catch (Exception $e) {
            die("Migration failed: " . $e->getMessage());
        }
    }
}

echo "All migrations have been run.<br>";
