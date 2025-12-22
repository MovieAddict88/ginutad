<?php
// Start session
session_start();

// Check if the user is logged in and is an admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Get reseller ID from URL
$reseller_id = $_GET['id'] ?? null;
if (!$reseller_id) {
    header('location: reseller_management.php');
    exit;
}

// Fetch reseller information
$stmt = $pdo->prepare("
    SELECT u.id as reseller_user_id, u.username, r.company_name, r.logo_path, r.primary_color, r.secondary_color
    FROM users u
    JOIN resellers r ON u.id = r.user_id
    WHERE r.id = :reseller_id
");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$reseller = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch clients for the reseller
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.first_name, u.last_name, u.contact_number, u.daily_limit, u.data_usage
    FROM users u
    WHERE u.reseller_id = :reseller_user_id
");
$stmt->bindParam(':reseller_user_id', $reseller['reseller_user_id'], PDO::PARAM_INT);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch commissions for the reseller
$stmt = $pdo->prepare("
    SELECT c.amount, c.commission_earned, c.created_at, u.username as client_username
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
    <h2>Reseller Profile: <?php echo htmlspecialchars($reseller['username']); ?></h2>
    <div class="page-actions">
        <a href="reseller_management.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Reseller Management
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Reseller Information</h3>
    </div>
    <div class="card-body">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($reseller['username']); ?></p>
        <p><strong>Company Name:</strong> <?php echo htmlspecialchars($reseller['company_name'] ?? 'N/A'); ?></p>
        <?php if ($reseller['logo_path']): ?>
            <p><strong>Logo:</strong></p>
            <img src="<?php echo htmlspecialchars($reseller['logo_path']); ?>" alt="Reseller Logo" style="max-width: 200px;">
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>Clients</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Daily Limit</th>
                    <th>Data Usage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['username']); ?></td>
                        <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($client['contact_number']); ?></td>
                        <td><?php echo format_bytes($client['daily_limit']); ?></td>
                        <td><?php echo format_bytes($client['data_usage']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>Commission History</h3>
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
                        <td><?php echo htmlspecialchars($commission['client_username']); ?></td>
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
