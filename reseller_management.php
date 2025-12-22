<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}



$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.is_reseller, r.id as reseller_id,
           u.first_name, u.address, u.contact_number, u.credits,
           (SELECT COUNT(*) FROM users c WHERE c.reseller_id = u.id) as client_count,
           (SELECT SUM(c.commission_earned) FROM commissions c WHERE c.reseller_id = r.id) as total_commission
    FROM users u
    LEFT JOIN resellers r ON u.id = r.user_id
    WHERE u.is_reseller = 1
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include 'header.php';
?>

<div class="page-header">
    <h2>Reseller Management</h2>
    <div class="page-actions">
        <a href="add_reseller.php" class="btn btn-primary">
            <span class="material-icons">add</span>
            Add Reseller
        </a>
    </div>
</div>

<?php
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $message = '';
    if ($error === 'invalid_password') {
        $message = 'Invalid password. Deletion failed.';
    } elseif ($error === 'delete_failed') {
        $message = 'An error occurred and the reseller could not be deleted.';
    }
    if ($message) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
    }
}
?>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to permanently delete this reseller? This action cannot be undone.</p>
        <form action="delete_reseller.php" method="post">
            <input type="hidden" name="id" id="deleteUserId">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label for="admin_password">Admin Password:</label>
                <input type="password" name="admin_password" id="admin_password" required>
            </div>
            <button type="submit" class="btn btn-danger">Confirm Delete</button>
            <button type="button" class="btn btn-secondary close-button">Cancel</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Users</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Credits</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                            <td><?php echo '₱' . number_format($user['credits'] ?? 0, 2); ?></td>
                            <td>
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                    <?php if ($user['is_reseller']): ?>
                                        <button class="delete-btn" data-id="<?php echo $user['id']; ?>" style="background-color: #E91E63; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Remove</button>
                                        <a href="edit_reseller.php?id=<?php echo $user['id']; ?>" style="background-color: #303F9F; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Edit</a>
                                        <a href="pay_reseller.php?id=<?php echo $user['id']; ?>" style="background-color: #F44336; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Pay</a>
                                        <a href="resellers_receipt.php?customer=<?php echo $user['id']; ?>" style="background-color: #8E24AA; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Receipt</a>
                                        <a href="view_reseller.php?id=<?php echo $user['reseller_id']; ?>" style="background-color: #3F51B5; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">View</a>
                                        <a href="reseller_dashboard.php?user_id=<?php echo $user['id']; ?>" style="background-color: #00BCD4; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Dashboard</a>
                                    <?php else: ?>
                                        <form action="toggle_reseller.php" method="post" style="margin: 0;">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <button type="submit" style="background-color: #4CAF50; border: none; color: white; padding: 10px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 20px; width: 100%;">Make Reseller</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountId = document.getElementById('account_id');
        if (accountId) {
            const quantity = document.getElementById('quantity');
            const totalCredits = document.getElementById('total_credits');

            function calculateTotal() {
                const selectedOption = accountId.options[accountId.selectedIndex];
                const price = selectedOption.dataset.price;
                const total = price * quantity.value;
                totalCredits.value = '₱' + total.toFixed(2);
            }

            accountId.addEventListener('change', calculateTotal);
            quantity.addEventListener('input', calculateTotal);

            calculateTotal();
        }

        const addResellerBtn = document.getElementById('addResellerBtn');
        if (addResellerBtn) {
            addResellerBtn.addEventListener('click', function() {
                document.getElementById('addResellerForm').style.display = 'block';
                this.style.display = 'none';
            });
        }

        const cancelAddReseller = document.getElementById('cancelAddReseller');
        if(cancelAddReseller) {
            cancelAddReseller.addEventListener('click', function() {
                document.getElementById('addResellerForm').style.display = 'none';
                document.getElementById('addResellerBtn').style.display = 'block';
            });
        }

        // Modal script
        var modal = document.getElementById('deleteModal');
        var deleteButtons = document.querySelectorAll('.delete-btn');
        var closeButtons = document.querySelectorAll('.close-button');
        var deleteUserIdInput = document.getElementById('deleteUserId');

        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var userId = this.getAttribute('data-id');
                deleteUserIdInput.value = userId;
                modal.style.display = 'block';
            });
        });

        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        // If there are errors, show the form
        <?php if (!empty($username_err) || !empty($password_err) || !empty($first_name_err) || !empty($address_err) || !empty($contact_number_err)): ?>
        document.getElementById('addResellerForm').style.display = 'block';
        document.getElementById('addResellerBtn').style.display = 'none';
        <?php endif; ?>
    });
</script>

<?php include 'footer.php'; ?>
