<?php
// Include the database connection file
require_once 'db_config.php';

$status = isset($_POST['status']) ? $_POST['status'] : 'active';

if ($status === 'active') {
    // Handle periodic active status updates (with delta values)
    if (!isset($_POST['login_code'], $_POST['bytes_in'], $_POST['bytes_out'])) {
        echo 'failure: Missing parameters for active status';
        exit;
    }

    $login_code = $_POST['login_code'];
    $bytes_in_delta = (int)$_POST['bytes_in'];
    $bytes_out_delta = (int)$_POST['bytes_out'];
    $total_data_delta = $bytes_in_delta + $bytes_out_delta;

    // Get user ID, daily limit, and banned status
    $userSql = 'SELECT id, daily_limit, banned FROM users WHERE login_code = :login_code';
    $userStmt = $pdo->prepare($userSql);
    $userStmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
    $userStmt->execute();
    $user = $userStmt->fetch();

    if ($user) {
        if ($user['banned']) {
            echo 'disconnect';
            exit;
        }

        // Atomically update user's total data usage
        $updateUserSql = 'UPDATE users SET data_usage = data_usage + :total_data WHERE id = :user_id';
        $updateUserStmt = $pdo->prepare($updateUserSql);
        $updateUserStmt->bindParam(':total_data', $total_data_delta, PDO::PARAM_INT);
        $updateUserStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
        $updateUserStmt->execute();

        // Atomically update the current session's data usage
        $updateSessionSql = 'UPDATE vpn_sessions SET bytes_in = bytes_in + :bytes_in, bytes_out = bytes_out + :bytes_out WHERE user_id = :user_id AND end_time IS NULL ORDER BY start_time DESC LIMIT 1';
        $updateSessionStmt = $pdo->prepare($updateSessionSql);
        $updateSessionStmt->bindParam(':bytes_in', $bytes_in_delta, PDO::PARAM_INT);
        $updateSessionStmt->bindParam(':bytes_out', $bytes_out_delta, PDO::PARAM_INT);
        $updateSessionStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
        $updateSessionStmt->execute();

        // Re-fetch the user's data usage to perform an accurate check against the daily limit
        $checkSql = 'SELECT data_usage FROM users WHERE id = :user_id';
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
        $checkStmt->execute();
        $updated_user = $checkStmt->fetch();

        if ($user['daily_limit'] > 0 && $updated_user['data_usage'] >= $user['daily_limit']) {
            echo 'disconnect';
        } else {
            echo 'continue';
        }
    } else {
        echo 'failure: User not found';
    }

} elseif ($status === 'disconnected') {
    // If session_id is not provided, try to find the active session by login_code
    if (empty($_POST['session_id']) && isset($_POST['login_code'])) {
        $userSql = 'SELECT id FROM users WHERE login_code = :login_code';
        $userStmt = $pdo->prepare($userSql);
        $userStmt->bindParam(':login_code', $_POST['login_code'], PDO::PARAM_STR);
        $userStmt->execute();
        $user = $userStmt->fetch();

        if ($user) {
            // Close the most recent active session for this user
            $updateSessionSql = 'UPDATE vpn_sessions SET end_time = NOW(), session_status = "closed" WHERE user_id = :user_id AND end_time IS NULL ORDER BY start_time DESC LIMIT 1';
            $updateSessionStmt = $pdo->prepare($updateSessionSql);
            $updateSessionStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            if ($updateSessionStmt->execute()) {
                echo 'success';
            } else {
                echo 'failure: Could not close session by login_code';
            }
        } else {
            echo 'failure: User not found for session closure';
        }
    } elseif (isset($_POST['session_id'], $_POST['bytes_in'], $_POST['bytes_out'])) {
        // Handle final disconnect update (with total values)
        $session_id = $_POST['session_id'];
        $bytes_in_total = (int)$_POST['bytes_in'];
        $bytes_out_total = (int)$_POST['bytes_out'];

        // Get current session data to calculate final delta
        $sessionSql = 'SELECT user_id, bytes_in, bytes_out FROM vpn_sessions WHERE id = :session_id AND end_time IS NULL';
        $sessionStmt = $pdo->prepare($sessionSql);
        $sessionStmt->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $sessionStmt->execute();
        $session = $sessionStmt->fetch();

        if ($session) {
            $user_id = $session['user_id'];
            $bytes_in_db = (int)$session['bytes_in'];
            $bytes_out_db = (int)$session['bytes_out'];

            // Calculate the final amount of data to add to the user's total usage
            $final_delta_in = $bytes_in_total - $bytes_in_db;
            $final_delta_out = $bytes_out_total - $bytes_out_db;
            $final_total_delta = $final_delta_in + $final_delta_out;

            if ($final_total_delta > 0) {
                $updateUserSql = 'UPDATE users SET data_usage = data_usage + :total_data WHERE id = :user_id';
                $updateUserStmt = $pdo->prepare($updateUserSql);
                $updateUserStmt->bindParam(':total_data', $final_total_delta, PDO::PARAM_INT);
                $updateUserStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $updateUserStmt->execute();
            }

            // Close the session and set the final total bytes
            $updateSessionSql = 'UPDATE vpn_sessions SET end_time = NOW(), bytes_in = :bytes_in, bytes_out = :bytes_out, session_status = "inactive" WHERE id = :session_id';
            $updateSessionStmt = $pdo->prepare($updateSessionSql);
            $updateSessionStmt->bindParam(':bytes_in', $bytes_in_total, PDO::PARAM_INT);
            $updateSessionStmt->bindParam(':bytes_out', $bytes_out_total, PDO::PARAM_INT);
            $updateSessionStmt->bindParam(':session_id', $session_id, PDO::PARAM_INT);

            if ($updateSessionStmt->execute()) {
                echo 'success';
            } else {
                echo 'failure: Could not update session';
            }
        } else {
            // This can happen if the disconnect signal is sent twice, which is not a critical error.
            echo 'success';
        }
    } else {
        echo 'failure: Missing parameters for disconnected status';
        exit;
    }
} else {
    echo 'failure: Invalid status parameter';
}
