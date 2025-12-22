<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}



$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'admin'");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="page-header">
    <h2>Admin Management</h2>
    <div class="page-actions">
        <a href="add_admin.php" class="btn btn-primary">
            <span class="material-icons">add</span>
            Add Admin
        </a>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3>Admins</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td>
                            <a href="change_password.php?id=<?php echo $admin['id']; ?>" class="btn btn-primary">Change Password</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('addAdminBtn').addEventListener('click', function() {
        document.getElementById('addAdminForm').style.display = 'block';
        this.style.display = 'none';
    });

    document.getElementById('cancelAddAdmin').addEventListener('click', function() {
        document.getElementById('addAdminForm').style.display = 'none';
        document.getElementById('addAdminBtn').style.display = 'block';
    });

    // If there are errors, show the form
    <?php if (!empty($username_err) || !empty($password_err)): ?>
    document.getElementById('addAdminForm').style.display = 'block';
    document.getElementById('addAdminBtn').style.display = 'none';
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>
