<?php
// Database connection details
$host = 'localhost';
$dbname = 'cornerst_vpn';
$user = 'cornerst_vpn';
$pass = 'cornerst_vpn';

// SQL file path
$sql_file = 'setup.sql';

try {
    // Establish a connection to the MySQL server (without selecting a specific database)
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute the SQL file
    $sql = file_get_contents($sql_file);
    $pdo->exec($sql);

    echo "Database setup completed successfully.\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
