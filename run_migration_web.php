<?php
require_once 'db_config.php';

try {
    // Read the SQL migration file
    $sql = file_get_contents('migrations/20251222_migration_fix.sql');

    // Execute the SQL script
    $pdo->exec($sql);

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    // Handle any errors
    die("Migration failed: " . $e->getMessage());
}
?>
