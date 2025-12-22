<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';
require_once 'utils.php';

// Check if the configurations table exists and run the migration if it does
$stmt = $pdo->query("SHOW TABLES LIKE 'configurations'");
if ($stmt->rowCount() > 0) {
    require_once 'migrations/20240728_migrate_configurations_to_promos.php';
}

// Handle Add/Edit Configuration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_configuration'])) {
        $carrier = trim($_POST['carrier']);
        $name = trim($_POST['name']);
        $config_text = trim($_POST['config_text']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $icon_promo_path = trim($_POST['icon_promo_path']);

        $sql = 'INSERT INTO promos (carrier, promo_name, config_text, is_active, icon_promo_path) VALUES (:carrier, :promo_name, :config_text, :is_active, :icon_promo_path)';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':carrier', $carrier, PDO::PARAM_STR);
            $stmt->bindParam(':promo_name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':config_text', $config_text, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
            $stmt->bindParam(':icon_promo_path', $icon_promo_path, PDO::PARAM_STR);
            $stmt->execute();
        }
    }
    header('location: promo_manager.php');
    exit;
}

// Handle Delete Promo
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = 'DELETE FROM promos WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: promo_manager.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2><?php echo translate('promo_manager'); ?></h2>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('add_new_promo'); ?></h3>
    </div>
    <div class="card-body">
        <form action="promo_manager.php" method="post">
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
            <div class="form-group">
                <label class="form-label"><?php echo translate('icon'); ?></label>
                <select name="icon_promo_path" class="form-control" required>
                    <?php
                    $promo_icons = glob('assets/promo/*.png');
                    foreach ($promo_icons as $icon) {
                        echo "<option value='" . htmlspecialchars($icon) . "'>" . htmlspecialchars(basename($icon)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" name="add_configuration" class="btn btn-primary" value="<?php echo translate('add_configuration'); ?>">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('existing_promos'); ?></h3>
    </div>
    <div class="card-body">
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Carrier</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Icon</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT * FROM promos ORDER BY carrier, promo_name';
                $promos = $pdo->query($sql)->fetchAll();
                $base_url = get_base_url();
                foreach ($promos as $promo) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($promo['carrier']) . "</td>";
                    echo "<td>" . htmlspecialchars($promo['promo_name']) . "</td>";
                    echo "<td>" . ($promo['is_active'] ? 'Active' : 'Inactive') . "</td>";
                    echo "<td><img src='" . htmlspecialchars($base_url . $promo['icon_promo_path']) . "' alt='icon' width='30'></td>";
                    echo "<td>";
                    echo "<a href='edit_promo.php?id=" . $promo['id'] . "' class='btn btn-primary'>Edit</a>";
                    echo "<a href='promo_manager.php?delete=" . $promo['id'] . "' class='btn btn-danger' onclick='return confirm(\"" . htmlspecialchars(translate('are_you_sure'), ENT_QUOTES) . "\")'>" . translate('delete') . "</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
