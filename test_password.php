<?php
// test_password.php
require_once 'db_config.php';

echo "Testing database connection...<br>";

try {
    // Test connection
    $pdo->query("SELECT 1");
    echo "✓ Database connection successful<br>";
    
    // Test table exists
    $result = $pdo->query("SHOW TABLES LIKE 'zip_password'");
    if ($result->rowCount() > 0) {
        echo "✓ zip_password table exists<br>";
        
        // Test data exists
        $sql = "SELECT password FROM zip_password ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            echo "✓ Password found: " . htmlspecialchars($row['password']) . "<br>";
        } else {
            echo "✗ No password found in table<br>";
        }
    } else {
        echo "✗ zip_password table does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<br>Testing get_password.php directly...<br>";
$url = "https://web.cornerstone-its-mobiledata.com/get_password.php";
$response = file_get_contents($url);
echo "Response: " . htmlspecialchars($response);
?>