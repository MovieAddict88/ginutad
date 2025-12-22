<?php
// get_password.php
header('Content-Type: text/plain');

// Include the database connection file
require_once 'db_config.php';

try {
    // SQL to get the password
    $sql = "SELECT password FROM zip_password ORDER BY id DESC LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            $password = $row['password'];
            
            $profiles_zip_path = 'uploads/profiles.zip';
            $timestamp = 0;
            if (file_exists($profiles_zip_path)) {
                $timestamp = filemtime($profiles_zip_path);
            }
            
            echo $password . ':' . $timestamp;
        } else {
            error_log("No password found in database");
            echo "failure";
        }
    } else {
        error_log("Failed to execute SQL query");
        echo "failure";
    }
} catch (PDOException $e) {
    error_log("Database error in get_password.php: " . $e->getMessage());
    echo "failure";
} catch (Exception $e) {
    error_log("General error in get_password.php: " . $e->getMessage());
    echo "failure";
}
?>
