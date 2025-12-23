<?php
// Initialize the session
session_start();
require_once 'db_config.php';

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if the user is an admin and a user_id is provided in the URL
if (is_admin() && isset($_GET['user_id'])) {
    $reseller_id = $_GET['user_id'];
    // Fetch the username of the reseller being viewed
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute(['id' => $reseller_id]);
    $viewed_reseller = $stmt->fetch();
    $viewed_username = $viewed_reseller ? $viewed_reseller['username'] : 'Unknown Reseller';
}
// Check if the user is logged in and is a reseller, otherwise redirect to login page
else if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["is_reseller"])) {
    header("location: login.php");
    exit;
} else {
    // Fetch reseller's data from session
    $reseller_id = $_SESSION["id"];
    $viewed_username = $_SESSION["username"];
}

// Fetch reseller's data
$stmt = $pdo->prepare("SELECT credits FROM users WHERE id = :id");
$stmt->execute(['id' => $reseller_id]);
$reseller = $stmt->fetch();
$credit_balance = $reseller['credits'] ?? 0.00;

// Fetch reseller's clients
$stmt = $pdo->prepare("SELECT id, username, expiration_date, login_code, device_id FROM users WHERE reseller_id = :reseller_id");
$stmt->execute(['reseller_id' => $reseller_id]);
$clients = $stmt->fetchAll();

// Calculate client stats
$total_clients = count($clients);
$active_clients = 0;
$expired_clients = 0;
$current_date = new DateTime();

foreach ($clients as $client) {
    if ($client['expiration_date']) {
        $expiration_date = new DateTime($client['expiration_date']);
        if ($expiration_date > $current_date) {
            $active_clients++;
        } else {
            $expired_clients++;
        }
    }
}

// Fetch recent sales
$stmt = $pdo->prepare("
    SELECT u.username, s.sale_date, s.amount
    FROM sales s
    JOIN users u ON s.client_id = u.id
    WHERE s.reseller_id = :reseller_id
    ORDER BY s.sale_date DESC
    LIMIT 10
");
$stmt->execute(['reseller_id' => $reseller_id]);
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reseller Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .wrapper { padding: 20px; }
        .card-header { font-weight: bold; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($viewed_username); ?></b>. Welcome to your reseller dashboard.</h1>
            <a href="logout.php" class="btn btn-danger">Sign Out</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary"><div class="card-body">
                    <h5 class="card-title">Total Clients</h5>
                    <p class="card-text display-4"><?php echo $total_clients; ?></p>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success"><div class="card-body">
                    <h5 class="card-title">Active Clients</h5>
                    <p class="card-text display-4"><?php echo $active_clients; ?></p>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning"><div class="card-body">
                    <h5 class="card-title">Expired Clients</h5>
                    <p class="card-text display-4"><?php echo $expired_clients; ?></p>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6"><div class="card"><div class="card-header">Credit Balance</div><div class="card-body">
                <h5 class="card-title display-4">₱<?php echo number_format($credit_balance, 2); ?></h5>
            </div></div></div>
            <div class="col-md-6"><div class="card"><div class="card-header">Add Credits</div><div class="card-body">
                <a href="add_credits.php" class="btn btn-primary">Add Credits</a>
            </div></div></div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">Client Management<a href="add_client.php" class="btn btn-success">Add New Client</a></div>
            <div class="card-body"><table class="table table-striped">
                <thead><tr><th>Username</th><th>Status</th><th>Expiration Date</th><th>Login Code</th><th>Device ID</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['username']); ?></td>
                        <td>
                            <?php
                                if ($client['expiration_date']) {
                                    $expiration_date = new DateTime($client['expiration_date']);
                                    if ($expiration_date > new DateTime()) {
                                        echo '<span class="badge badge-success">Active</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Expired</span>';
                                    }
                                } else {
                                    echo '<span class="badge badge-secondary">No Expiry</span>';
                                }
                            ?>
                        </td>
                        <td><?php echo $client['expiration_date'] ? date('Y-m-d', strtotime($client['expiration_date'])) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($client['login_code']); ?></td>
                        <td><?php echo htmlspecialchars($client['device_id']); ?></td>
                        <td>
                            <a href="edit_client.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_client.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>

        <div class="card">
            <div class="card-header">Recent Sales</div>
            <div class="card-body"><table class="table table-striped">
                <thead><tr><th>Client</th><th>Date</th><th>Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['username']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                        <td>₱<?php echo number_format($sale['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</body>
</html>