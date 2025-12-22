<?php
// Initialize the session
session_start();
require_once 'db_config.php';

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["is_reseller"])) {
    header("location: login.php");
    exit;
}

$reseller_id = $_SESSION["id"];
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("location: reseller_dashboard.php");
    exit;
}

// Fetch client's data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND reseller_id = :reseller_id");
$stmt->execute(['id' => $client_id, 'reseller_id' => $reseller_id]);
$client = $stmt->fetch();

if (!$client) {
    header("location: reseller_dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $expiration_date = $_POST["expiration_date"];

    try {
        $sql = "UPDATE users SET username = :username, expiration_date = :expiration_date";
        $params = ['username' => $username, 'expiration_date' => $expiration_date, 'id' => $client_id];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params['password'] = $hashed_password;
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("location: reseller_dashboard.php");
        exit;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Client</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Client</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="edit_client.php?id=<?php echo $client_id; ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($client['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Password (leave blank to keep current password)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label>Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" value="<?php echo htmlspecialchars($client['expiration_date']); ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Client">
                <a href="reseller_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>