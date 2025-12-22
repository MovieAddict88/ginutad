<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrier = $_POST['carrier'];
    $name = $_POST['name'];
    $config_text = $_POST['config_text'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO configurations (carrier, name, config_text, is_active) VALUES (:carrier, :name, :config_text, :is_active)");
    $stmt->execute(['carrier' => $carrier, 'name' => $name, 'config_text' => $config_text, 'is_active' => $is_active]);

    header('Location: configuration_manager.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Add New Configuration</h2>
</div>

<div class="card">
    <div class="card-body">
        <form action="add_configuration.php" method="post">
            <div class="form-group">
                <label for="carrier">Carrier</label>
                <input type="text" name="carrier" id="carrier" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="config_text">Configuration Text</label>
                <textarea name="config_text" id="config_text" class="form-control" rows="10" required></textarea>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" name="is_active" id="is_active" value="1" checked>
            </div>
            <button type="submit" class="btn btn-primary">Add Configuration</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
