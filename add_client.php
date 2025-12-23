<?php
// Initialize the session
session_start();
require_once 'db_config.php';
require_once 'utils.php';

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["is_reseller"])) {
    header("location: login.php");
    exit;
}

$reseller_id = $_SESSION["id"];

// Fetch reseller's account_id
$stmt = $pdo->prepare("SELECT account_id FROM users WHERE id = :id");
$stmt->execute(['id' => $reseller_id]);
$reseller = $stmt->fetch();
$account_id = $reseller['account_id'];

// Fetch the price of the reseller's account
$stmt = $pdo->prepare("SELECT price FROM accounts WHERE id = :id");
$stmt->execute(['id' => $account_id]);
$account = $stmt->fetch();
$client_cost = $account['price'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $expiration_date = $_POST["expiration_date"];

    // Fetch reseller's credits and client limit
    $stmt = $pdo->prepare("SELECT credits, client_limit FROM users WHERE id = :id");
    $stmt->execute(['id' => $reseller_id]);
    $reseller_data = $stmt->fetch();

    // Get current client count
    $stmt = $pdo->prepare("SELECT COUNT(*) as client_count FROM users WHERE reseller_id = :reseller_id");
    $stmt->execute(['reseller_id' => $reseller_id]);
    $client_count = $stmt->fetch()['client_count'];

    if ($client_count >= $reseller_data['client_limit']) {
        $error = "You have reached your client limit of " . $reseller_data['client_limit'] . " clients.";
    } elseif ($reseller_data['credits'] < $client_cost) {
        $error = "You do not have enough credits to add a new client. Required: ₱" . number_format($client_cost, 2) . ", Available: ₱" . number_format($reseller_data['credits'], 2);
    } else {
        try {
            $pdo->beginTransaction();

            // Deduct credits from reseller
            $new_credits = $reseller_data['credits'] - $client_cost;
            $stmt = $pdo->prepare("UPDATE users SET credits = :credits WHERE id = :id");
            $stmt->execute(['credits' => $new_credits, 'id' => $reseller_id]);

            // Create the new client
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            require_once 'utils.php'; // Ensure login code generation function is available
            $login_code = generate_unique_login_code($pdo);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, reseller_id, expiration_date, first_name, last_name, address, contact_number, login_code) VALUES (:username, :password, :reseller_id, :expiration_date, :first_name, :last_name, :address, :contact_number, :login_code)");
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'reseller_id' => $reseller_id,
                'expiration_date' => $expiration_date,
                'first_name' => 'Client',
                'last_name' => 'User',
                'address' => 'N/A',
                'contact_number' => 'N/A',
                'login_code' => $login_code
            ]);
            $client_id = $pdo->lastInsertId();

            // Record the sale
            $stmt = $pdo->prepare("INSERT INTO sales (reseller_id, client_id, amount) VALUES (:reseller_id, :client_id, :amount)");
            $stmt->execute(['reseller_id' => $reseller_id, 'client_id' => $client_id, 'amount' => $client_cost]);

            $pdo->commit();
            header("location: reseller_dashboard.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Client</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Client</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_client.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Client">
                <a href="reseller_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>