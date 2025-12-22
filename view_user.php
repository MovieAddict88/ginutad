<?php
// Start session
session_start();

// Check if the user is logged in and is admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Get user ID from URL
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('location: index.php');
    exit;
}

// Fetch user data
$sql = 'SELECT * FROM users WHERE id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('location: index.php');
    exit;
}

// Fetch payment history
$sql = 'SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC, payment_time DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h1>View User: <?php echo htmlspecialchars($user['username']); ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>User Information</h3>
    </div>
    <div class="card-body">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
        <p><strong>Login Code:</strong> <?php echo htmlspecialchars($user['login_code']); ?></p>
        <p><strong>Device ID:</strong> <?php echo htmlspecialchars($user['device_id']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
        <p><strong>Payment:</strong> <?php echo htmlspecialchars($user['payment']); ?></p>
        <p><strong>Billing Date:</strong> <?php if (!empty($user['billing_month'])) { echo htmlspecialchars(date('F d, Y', strtotime($user['billing_month']))); } else { echo 'N/A'; } ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Payment History</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Payment Method</th>
                        <th>Reference Number</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No payment history found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td data-label="Amount"><?php echo htmlspecialchars($payment['amount']); ?></td>
                                <td data-label="Date"><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                <td data-label="Time"><?php echo htmlspecialchars(date('h:i A', strtotime($payment['payment_time']))); ?></td>
                                <td data-label="Payment Method"><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td data-label="Reference Number"><?php echo htmlspecialchars($payment['reference_number']); ?></td>
                                <td data-label="Attachment">
                                    <?php if (!empty($payment['attachment_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($payment['attachment_path']); ?>" target="_blank" class="btn btn-sm btn-view">View Media</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="page-actions">
    <a href="document.php?type=invoice&customer=<?php echo $user['id']; ?>" class="btn btn-primary">Invoice</a>
    <a href="index.php" class="btn btn-secondary">Back to User List</a>
</div>

<?php include 'footer.php'; ?>
