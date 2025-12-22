<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM configurations WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}

header('Location: configuration_manager.php');
exit;
?>

