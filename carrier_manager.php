<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';
require_once 'utils.php';

// Handle Add/Edit Carrier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['carrier_name'])) {
    $carrier_name = trim($_POST['carrier_name']);
    $icon_path = trim($_POST['icon_path']);
    $carrier_id = isset($_POST['carrier_id']) ? $_POST['carrier_id'] : null;

    if (!empty($carrier_name) && !empty($icon_path)) {
        if ($carrier_id) {
            // Update existing carrier
            $sql = 'UPDATE carriers SET name = :name, icon_path = :icon_path WHERE id = :id';
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(':name', $carrier_name, PDO::PARAM_STR);
                $stmt->bindParam(':icon_path', $icon_path, PDO::PARAM_STR);
                $stmt->bindParam(':id', $carrier_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            // Add new carrier
            $sql = 'INSERT INTO carriers (name, icon_path) VALUES (:name, :icon_path)';
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(':name', $carrier_name, PDO::PARAM_STR);
                $stmt->bindParam(':icon_path', $icon_path, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
    header('location: carrier_manager.php');
    exit;
}

// Handle Delete Carrier
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = 'DELETE FROM carriers WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: carrier_manager.php');
    exit;
}

// Fetch carrier for editing
$edit_carrier = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = 'SELECT * FROM carriers WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $edit_carrier = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


include 'header.php';
?>

<div class="page-header">
    <h2>Manage Carriers</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo $edit_carrier ? 'Edit Carrier' : 'Add New Carrier'; ?></h3>
    </div>
    <div class="card-body">
        <form action="carrier_manager.php" method="post">
            <input type="hidden" name="carrier_id" value="<?php echo $edit_carrier ? $edit_carrier['id'] : ''; ?>">
            <div class="form-group">
                <label class="form-label">Carrier Name</label>
                <input type="text" name="carrier_name" class="form-control" value="<?php echo $edit_carrier ? htmlspecialchars($edit_carrier['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="icon_path" class="form-control" required>
                    <?php
                    $icons = glob('assets/promo/*.png');
                    foreach ($icons as $icon) {
                        $selected = ($edit_carrier && $edit_carrier['icon_path'] == $icon) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($icon) . "' $selected>" . htmlspecialchars(basename($icon)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="<?php echo $edit_carrier ? 'Update Carrier' : 'Add Carrier'; ?>">
                <?php if ($edit_carrier): ?>
                    <a href="carrier_manager.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Existing Carriers</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Icon</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT * FROM carriers ORDER BY id DESC';
                $carriers = $pdo->query($sql)->fetchAll();
                $base_url = get_base_url();
                foreach ($carriers as $carrier) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($carrier['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($carrier['name']) . "</td>";
                    echo "<td><img src='" . htmlspecialchars($base_url . $carrier['icon_path']) . "' alt='icon' width='30'></td>";
                    echo "<td>
                            <a href='carrier_manager.php?edit=" . $carrier['id'] . "' class='btn btn-secondary'>Edit</a>
                            <a href='carrier_manager.php?delete=" . $carrier['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
