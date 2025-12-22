<?php
// update_database_role.php - Run this once to update your database
require_once 'db_config.php';

try {
    // Add role column to users table
    $sql = "ALTER TABLE users ADD COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user'";
    $pdo->exec($sql);
    echo "Role column added successfully<br>";

    // Update existing admin user to have admin role
    $update_sql = "UPDATE users SET role = 'admin' WHERE username = 'admin'";
    $pdo->exec($update_sql);
    echo "Admin user role updated successfully<br>";

    echo "Database update completed successfully!<br>";
    echo "<a href='login.php'>Go to Login</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>