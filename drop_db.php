<?php
// Database connection details
$host = 'localhost';
$dbname = 'cornerst_vpn';
$user = 'cornerst_vpn';
$pass = 'cornerst_vpn';

try {
    // Establish a connection to the MySQL server (without selecting a specific database)
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop the database
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    $pdo->exec("CREATE DATABASE `$dbname`");

    echo "Database '$dbname' dropped and recreated successfully.\n";

} catch (PDOException $e) {
    die("Database operation failed: " . $e->getMessage() . "\n");
}
