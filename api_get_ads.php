<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare('SELECT name, ad_unit_id FROM admob_ads WHERE is_enabled = 1');
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode(['success' => true, 'ads' => $ads]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>