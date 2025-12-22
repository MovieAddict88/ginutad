<?php
require_once 'db_config.php';
require_once 'utils.php';

try {
    $stmt = $pdo->query("
        SELECT DISTINCT pr.id, pr.promo_name, pr.icon_promo_path
        FROM promos pr
        JOIN vpn_profiles vp ON pr.id = vp.promo_id
        ORDER BY pr.promo_name ASC
    ");
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $base_url = get_base_url();
    foreach ($promos as &$promo) {
        $promo['icon_promo_path'] = $base_url . $promo['icon_promo_path'];
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'promos' => $promos]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
