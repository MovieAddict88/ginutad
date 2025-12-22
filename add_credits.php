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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST["amount"];

    // Simulate a successful payment
    if ($amount > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET credits = credits + :amount WHERE id = :id");
            $stmt->execute(['amount' => $amount, 'id' => $reseller_id]);
            header("location: reseller_dashboard.php");
            exit;
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a valid amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Credits</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Add Credits</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_credits.php" method="post">
            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Credits">
                <a href="reseller_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>