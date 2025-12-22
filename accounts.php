<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle form submissions for adding/editing accounts
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_account'])) {
        $name = trim($_POST['name']);
        $price = trim($_POST['price']);

        if (!empty($name) && !empty($price)) {
            $sql = 'INSERT INTO accounts (name, price) VALUES (:name, :price)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name, 'price' => $price]);
        }
    } elseif (isset($_POST['edit_account'])) {
        $id = trim($_POST['id']);
        $name = trim($_POST['name']);
        $price = trim($_POST['price']);

        if (!empty($id) && !empty($name) && !empty($price)) {
            $sql = 'UPDATE accounts SET name = :name, price = :price WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id]);
        }
    } elseif (isset($_POST['delete_account'])) {
        $id = trim($_POST['id']);

        if (!empty($id)) {
            $sql = 'DELETE FROM accounts WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        }
    }
}

// Fetch all accounts
$stmt = $pdo->query('SELECT * FROM accounts ORDER BY name');
$accounts = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h2>Accounts Management</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add New Account</h3>
    </div>
    <div class="card-body">
        <form action="accounts.php" method="post">
            <input type="hidden" name="add_account" value="1">
            <div class="form-group">
                <label class="form-label">Account Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Account">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Existing Accounts</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <form action="accounts.php" method="post">
                                <td>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($account['name']); ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($account['price']); ?>" required>
                                </td>
                                <td>
                                    <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
                                    <input type="submit" name="edit_account" class="btn btn-secondary" value="Update">
                                    <input type="submit" name="delete_account" class="btn btn-danger" value="Delete" onclick="return confirm('Are you sure you want to delete this account?');">
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
