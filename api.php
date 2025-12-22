<?php
// Include the database connection file
require_once 'db_config.php';

// Check if the login_code and device_id are set
if (isset($_POST['login_code']) && isset($_POST['device_id'])) {
    $login_code = $_POST['login_code'];
    $device_id = $_POST['device_id'];

    // Handle session validation
    if (isset($_POST['check_session'])) {
        $sql = 'SELECT * FROM users WHERE login_code = :login_code AND device_id = :device_id AND role = "user"';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            if ($stmt->execute() && $stmt->rowCount() == 1) {
                echo 'active';
            } else {
                echo 'inactive';
            }
        } else {
            echo 'inactive';
        }
        exit;
    }

    // Check if the user exists and the device ID is empty (only for regular users)
    $sql = 'SELECT * FROM users WHERE login_code = :login_code AND (device_id = :device_id OR device_id IS NULL) AND role = "user"';

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
        $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();

                if ($user['daily_limit'] > 0 && $user['data_usage'] >= $user['daily_limit']) {
                    echo 'limit_exceeded';
                    exit;
                }

                if (empty($user['device_id'])) {
                    // Device ID is not set, so update it
                    $updateSql = 'UPDATE users SET device_id = :device_id WHERE login_code = :login_code';
                    if ($updateStmt = $pdo->prepare($updateSql)) {
                        $updateStmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
                        $updateStmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
                        $updateStmt->execute();
                    }
                }

                if (isset($_POST['do_login'])) {
                    echo 'success';
                } else {
                    // Close any existing sessions for this user
                    $closeSessionsSql = 'UPDATE vpn_sessions SET end_time = NOW(), session_status = "closed" WHERE user_id = :user_id AND end_time IS NULL';
                    if ($closeStmt = $pdo->prepare($closeSessionsSql)) {
                        $closeStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                        $closeStmt->execute();
                    }

                    // Log the VPN session
                    $insertSessionSql = 'INSERT INTO vpn_sessions (user_id, profile_id, start_time, ip_address, session_status, bytes_in, bytes_out) VALUES (:user_id, :profile_id, NOW(), :ip_address, "active", 0, 0)';
                    if ($insertStmt = $pdo->prepare($insertSessionSql)) {
                        $insertStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                        $insertStmt->bindParam(':profile_id', $_POST['profile_id'], PDO::PARAM_INT);
                        $insertStmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
                        if ($insertStmt->execute()) {
                            $session_id = $pdo->lastInsertId();
                            echo 'success:' . $session_id;
                        } else {
                            echo 'failure';
                        }
                    }
                }
            } else {
                echo 'failure';
            }
        } else {
            echo 'failure';
        }
    }
} else {
    echo 'failure';
}
?>