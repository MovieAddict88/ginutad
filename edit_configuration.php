<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: configuration_manager.php');
    exit;
}
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM configurations WHERE id = :id");
$stmt->execute(['id' => $id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    header('Location: configuration_manager.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrier = $_POST['carrier'];
    $name = $_POST['name'];
    $config_text = $_POST['config_text'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE configurations SET carrier = :carrier, name = :name, config_text = :config_text, is_active = :is_active WHERE id = :id");
    $stmt->execute(['carrier' => $carrier, 'name' => $name, 'config_text' => $config_text, 'is_active' => $is_active, 'id' => $id]);

    header('Location: configuration_manager.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Edit Configuration</h2>
</div>

<div class="card">
    <div class="card-body">
        <form action="edit_configuration.php?id=<?php echo $config['id']; ?>" method="post">
            <div class="form-group">
                <label for="carrier">Carrier</label>
                <input type="text" name="carrier" id="carrier" class="form-control" value="<?php echo htmlspecialchars($config['carrier']); ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($config['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="config_text">Configuration Text</label>
                <textarea name="config_text" id="config_text" class="form-control" rows="10" required><?php echo htmlspecialchars($config['config_text']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $config['is_active'] ? 'checked' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-primary">Update Configuration</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
