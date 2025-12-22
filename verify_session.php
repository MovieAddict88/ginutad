<?php
// Include the database connection file
require_once 'db_config.php';

// Check if the login_code and device_id are set
if (isset($_GET['login_code']) && isset($_GET['device_id'])) {
    $login_code = $_GET['login_code'];
    $device_id = $_GET['device_id'];

    // Check if the user exists with the given login_code and device_id
    $sql = 'SELECT * FROM users WHERE login_code = :login_code AND device_id = :device_id AND role = "user"';

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
        $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                echo 'valid';
            } else {
                echo 'invalid';
            }
        } else {
            echo 'invalid';
        }
    }
} else {
    echo 'invalid';
}
?>