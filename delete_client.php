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
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $client_id]);
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
    <title>Delete Client</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Delete Client</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <p>Are you sure you want to delete the client "<?php echo htmlspecialchars($client['username']); ?>"?</p>
        <form action="delete_client.php?id=<?php echo $client_id; ?>" method="post">
            <div class="form-group">
                <input type="submit" class="btn btn-danger" value="Yes, Delete">
                <a href="reseller_dashboard.php" class="btn btn-secondary">No, Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>