<?php
// Start session
session_start();

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_reseller']) || $_SESSION['is_reseller'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Get reseller information
$reseller_id = $_SESSION['reseller_id'];

// Fetch commissions for the reseller
$stmt = $pdo->prepare("
    SELECT c.amount, c.commission_earned, c.created_at, u.username
    FROM commissions c
    JOIN users u ON c.client_id = u.id
    WHERE c.reseller_id = :reseller_id
    ORDER BY c.created_at DESC
");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="page-header">
    <h2>Commission Tracking</h2>
    <div class="page-actions">
        <a href="reseller_dashboard.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Your Commissions</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Sale Amount</th>
                    <th>Commission Earned</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commissions as $commission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($commission['username']); ?></td>
                        <td>$<?php echo number_format($commission['amount'], 2); ?></td>
                        <td>$<?php echo number_format($commission['commission_earned'], 2); ?></td>
                        <td><?php echo htmlspecialchars($commission['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
