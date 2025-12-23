<?php
// migrations/20251222_run_migration_fix.php
// This script is designed to be run by migrate.php.
// It executes the comprehensive SQL migration script to update the database schema.

try {
    // The $pdo variable is available from the calling script (migrate.php),
    // which includes db_config.php.

    // Read the SQL migration file from the same directory.
    $sql = file_get_contents(__DIR__ . '/20251222_migration_fix.sql');

    // Execute the entire SQL script.
    $pdo->exec($sql);

} catch (Exception $e) {
    // Throw a new exception to be caught by the migration runner (migrate.php).
    throw new Exception("Failed to execute migration script '20251222_migration_fix.sql': " . $e->getMessage());
}
?>
