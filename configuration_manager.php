<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$promo_id = isset($_GET['promo_id']) ? (int)$_GET['promo_id'] : 0;

if ($promo_id > 0) {
    $stmt = $pdo->prepare("SELECT c.*, p.promo_name FROM configurations c LEFT JOIN promos p ON c.promo_id = p.id WHERE c.promo_id = :promo_id ORDER BY c.carrier, c.name");
    $stmt->execute(['promo_id' => $promo_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM configurations ORDER BY carrier, name");
    $stmt->execute();
}
$configurations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$promo_name = (!empty($configurations) && $promo_id > 0) ? $configurations[0]['promo_name'] : null;

include 'header.php';
?>

<div class="page-header">
    <h2>Configuration Management</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <?php 
            if ($promo_name) {
                echo 'Configurations for ' . htmlspecialchars($promo_name);
            } else {
                echo 'Configurations';
            }
            ?>
        </h3>
        <a href="add_configuration.php" class="btn btn-primary">Add New Configuration</a>
    </div>
    <div class="card-body">
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Carrier</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configurations as $config): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($config['carrier']); ?></td>
                        <td><?php echo htmlspecialchars($config['name']); ?></td>
                        <td><?php echo $config['is_active'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="edit_configuration.php?id=<?php echo $config['id']; ?>" class="btn btn-primary">Edit</a>
                            <form action="delete_configuration.php" method="post" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?php echo $config['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this configuration?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
