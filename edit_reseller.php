<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';

// Check if the user is an admin
if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Check if the reseller ID is provided
if (!isset($_GET['id'])) {
    header('Location: reseller_management.php?error=reseller_id_missing');
    exit;
}

$reseller_id = $_GET['id'];

// Fetch reseller details from the database
try {
    $stmt = $pdo->prepare("SELECT id, username, first_name, address, contact_number, account_id, client_limit, credits FROM users WHERE id = ? AND is_reseller = 1");
    $stmt->execute([$reseller_id]);
    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reseller) {
        header('Location: reseller_management.php?error=reseller_not_found');
        exit;
    }

    // Fetch accounts from the database
    $stmt_accounts = $pdo->query('SELECT * FROM accounts ORDER BY name');
    $accounts = $stmt_accounts->fetchAll();

} catch (PDOException $e) {
    // Log error and redirect
    error_log("Database error fetching reseller: " . $e->getMessage());
    header('Location: reseller_management.php?error=database_error');
    exit;
}

// Now that all logic is done, include the header
require_once 'header.php';
?>

<div class="page-header">
    <h2>Edit Reseller</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Reseller Information</h3>
    </div>
    <div class="card-body">
        <form action="update_reseller.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($reseller['id']); ?>">

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($reseller['username']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">New Password (optional)</label>
                <input type="password" class="form-control" name="password">
            </div>

            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($reseller['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($reseller['address']); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($reseller['contact_number']); ?>">
            </div>

            <div class='form-group'>
                <label class="form-label">Account Type</label>
                <select name="account_id" id="account_id" class="form-control">
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>"
                                data-price="<?php echo $account['price']; ?>"
                                <?php echo ($account['id'] == $reseller['account_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($account['name']); ?> (<?php echo '₱' . number_format($account['price'], 2); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class='form-group'>
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo htmlspecialchars($reseller['client_limit']); ?>" min="1">
            </div>

            <div class='form-group'>
                <label class="form-label">Total Credits</label>
                <input type="text" id="total_credits" name="credits" class="form-control" value="<?php echo htmlspecialchars($reseller['credits']); ?>" readonly>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Reseller">
                <a class="btn btn-link" href="reseller_management.php">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountId = document.getElementById('account_id');
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

        // Initial calculation on page load
        calculateTotal();
    });
</script>

<?php
require_once 'footer.php';
?>
