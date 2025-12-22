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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_time = $_POST['payment_time'];
    $payment_method = $_POST['payment_method'];
    $reference_number = $_POST['reference_number'];

    // Handle file upload securely
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_exts)) {
            $attachment_path = $upload_dir . uniqid('', true) . '.' . $file_ext;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path);
        }
    }

    try {
        $pdo->beginTransaction();

        // Insert payment into the database
        $sql = 'INSERT INTO payments (user_id, amount, payment_date, payment_time, payment_method, reference_number, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $amount, $payment_date, $payment_time, $payment_method, $reference_number, $attachment_path]);

        // Update user status
        $sql = 'UPDATE users SET status = "Paid", payment = ? WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$amount, $user_id]);

        // Check if the user is a client of a reseller
        $sql = 'SELECT reseller_id FROM reseller_clients WHERE client_id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $reseller_client = $stmt->fetch();

        if ($reseller_client) {
            $reseller_id = $reseller_client['reseller_id'];

            // Get reseller commission rate
            $sql_rate = 'SELECT commission_rate FROM resellers WHERE id = ?';
            $stmt_rate = $pdo->prepare($sql_rate);
            $stmt_rate->execute([$reseller_id]);
            $reseller = $stmt_rate->fetch();
            $commission_rate = $reseller['commission_rate'];

            $commission_earned = $amount * $commission_rate;

            // Insert commission into the commissions table
            $sql = 'INSERT INTO commissions (reseller_id, client_id, amount, commission_rate, commission_earned) VALUES (?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$reseller_id, $user_id, $amount, $commission_rate, $commission_earned]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    // Redirect to index page
    header('location: index.php');
    exit;
}

// Fetch user data
$sql = 'SELECT username FROM users WHERE id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('location: index.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h1>Payment for <?php echo htmlspecialchars($user['username']); ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Payment Details</h3>
    </div>
    <div class="card-body">
        <form action="pay_user.php?id=<?php echo $user_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="payment_date">Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="payment_time">Time</label>
                <input type="time" name="payment_time" id="payment_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="Cash">Cash</option>
                    <option value="Gcash">Gcash</option>
                    <option value="Paymaya">Paymaya</option>
                    <option value="Coins.ph">Coins.ph</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="reference_number">Reference Number</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control">
            </div>
            <div class="form-group">
                <label for="attachment">Attachment (Optional)</label>
                <input type="file" name="attachment" id="attachment" class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit Payment</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
