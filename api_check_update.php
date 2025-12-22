<?php
require_once 'db_config.php';

try {
    $stmt = $pdo->query('SELECT version_code, version_name, apk_path FROM app_updates ORDER BY id DESC LIMIT 1');
    $latest_update = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest_update) {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        // Construct the full URL, ensuring no double slashes
        $apk_url = $base_url . '/' . ltrim($latest_update['apk_path'], '/');

        $response = [
            'success' => true,
            'versionCode' => $latest_update['version_code'],
            'version_name' => $latest_update['version_name'],
            'apkUrl' => $apk_url
        ];
    } else {
        $response = ['success' => false, 'message' => 'No updates found.'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
