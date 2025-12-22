<?php
header('Content-Type: application/json');
require_once 'db_config.php';

try {
    $stmt = $pdo->query('SELECT ad_unit_name, ad_unit_id FROM admob_settings WHERE is_enabled = 1');
    $admob_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'admob_settings' => $admob_settings]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>