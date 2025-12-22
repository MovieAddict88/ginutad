<?php
// api_get_profiles.php

// Include the database configuration
require_once 'db_config.php';
require_once 'utils.php';

// Check if the login_code is set
if (!isset($_POST['login_code'])) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Login code is required.']);
    exit;
}

try {
    $login_code = $_POST['login_code'];

    // Validate the login_code
    $sql = 'SELECT id, banned FROM users WHERE login_code = :login_code';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        header('Content-Type: application/json');
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid login code.']);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user['banned']) {
        header('Content-Type: application/json');
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'User is banned.']);
        exit;
    }

    // Check if promo_id is set in the POST request
    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
        $promo_id = $_POST['promo_id'];

        // Prepare a select statement to retrieve profiles and their associated promo configurations
        $sql = "
            SELECT
                p.id,
                p.name AS profile_name,
                p.ovpn_config,
                pr.config_text,
                p.type as profile_type,
                p.icon_path
            FROM
                vpn_profiles p
            LEFT JOIN
                promos pr ON p.promo_id = pr.id
            WHERE
                p.promo_id = :promo_id
            ORDER BY
                p.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':promo_id', $promo_id, PDO::PARAM_INT);
        $stmt->execute();
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If no promo_id is provided, return an empty list of profiles.
        $profiles = [];
    }

    $base_url = get_base_url();
    foreach ($profiles as &$profile) {
        // Combine the base ovpn config with the promo's config text
        $profile['profile_content'] = $profile['ovpn_config'] . "\n" . $profile['config_text'];

        // Unset the original config fields to keep the response clean
        unset($profile['ovpn_config']);
        unset($profile['config_text']);

        if (!empty($profile['icon_path'])) {
            $profile['icon_path'] = $base_url . $profile['icon_path'];
        }
        // Simulate ping for each profile
        $profile['ping'] = rand(20, 200);
        $profile['signal_strength'] = rand(30, 100);
    }

    // Set the content type header to application/json
    header('Content-Type: application/json');

    // Output the profiles as a JSON encoded string
    echo json_encode(['status' => 'success', 'profiles' => $profiles]);

} catch (PDOException $e) {
    // Handle potential database errors
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
