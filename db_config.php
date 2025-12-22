<?php
/*
 * Database credentials
 */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'cornerst_vpn');
define('DB_PASSWORD', 'cornerst_vpn');
define('DB_NAME', 'cornerst_vpn');

/* Attempt to connect to MySQL database */
try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ];
    $pdo = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    die('ERROR: Could not connect. ' . $e->getMessage());
}
?>