<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: promo_manager.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_configuration'])) {
    $carrier = $_POST['carrier'];
    $name = $_POST['promo_name'];
    $config_text = $_POST['config_text'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $icon_promo_path = $_POST['icon_promo_path'];

    $stmt = $pdo->prepare("UPDATE promos SET carrier = :carrier, promo_name = :promo_name, config_text = :config_text, is_active = :is_active, icon_promo_path = :icon_promo_path WHERE id = :id");
    $stmt->execute(['carrier' => $carrier, 'promo_name' => $name, 'config_text' => $config_text, 'is_active' => $is_active, 'icon_promo_path' => $icon_promo_path, 'id' => $id]);

    header('Location: promo_manager.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM promos WHERE id = :id");
$stmt->execute(['id' => $id]);
$promo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promo) {
    header('Location: promo_manager.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Edit Promo/Configuration</h2>
</div>

<div class="card">
    <div class="card-body">
        <form action="edit_promo.php" method="post">
            <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
            <div class="form-group">
                <label for="carrier">Carrier</label>
                <input type="text" name="carrier" id="carrier" class="form-control" value="<?php echo htmlspecialchars($promo['carrier']); ?>" required>
            </div>
            <div class="form-group">
                <label for="promo_name">Name</label>
                <input type="text" name="promo_name" id="promo_name" class="form-control" value="<?php echo htmlspecialchars($promo['promo_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="config_text">Configuration Text</label>
                <textarea name="config_text" id="config_text" class="form-control" rows="10" required><?php echo htmlspecialchars($promo['config_text']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $promo['is_active'] ? 'checked' : ''; ?>>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo translate('icon'); ?></label>
                <select name="icon_promo_path" class="form-control" required>
                    <?php
                    $promo_icons = glob('assets/promo/*.png');
                    foreach ($promo_icons as $icon) {
                        $selected = ($icon == $promo['icon_promo_path']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($icon) . "' " . $selected . ">" . htmlspecialchars(basename($icon)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="update_configuration" class="btn btn-primary">Update Configuration</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
