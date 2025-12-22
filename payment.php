<?php
session_start();
require_once 'config/dbconnection.php';
require_once 'includes/customer_header.php';
require_once 'includes/classes/admin-class.php';

$admins = new Admins($dbh);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure customer ID is present
if (!isset($_GET['customer']) && !isset($_POST['customer'])) {
    header('Location: index.php'); // Redirect to a safe page if no customer is specified
    exit();
}

$customer_id = $_GET['customer'] ?? $_POST['customer'];
$customer = $admins->getCustomerInfo($customer_id);

// If customer doesn't exist, redirect
if (!$customer) {
    $_SESSION['error'] = 'The selected customer does not exist.';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $amount = (float)$_POST['amount'];
    $reference_number = trim($_POST['reference_number']);
    $payment_method = trim($_POST['payment_method']);
    $payment_date = $_POST['payment_date'];
    $payment_time = $_POST['payment_time'];
    $screenshot = isset($_FILES['screenshot']) ? $_FILES['screenshot'] : null;

    if (empty($amount) || empty($reference_number) || empty($payment_method) || empty($payment_date) || empty($payment_time)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($amount <= 0) {
        $error_message = "Payment amount must be a positive number.";
    } else {
        // Here you would process the payment.
        // This is a placeholder for your payment processing logic.
        // For demonstration, we'll just show a success message.
        
        // Example of what you might do:
        // $payment_processed = $admins->processNewPayment($customer_id, $amount, $reference_number, $payment_method, $screenshot, $payment_date, $payment_time);
        
        // if ($payment_processed) {
        //     $_SESSION['success'] = 'Payment submitted successfully and is pending approval.';
        //     header('Location: index.php');
        //     exit();
        // } else {
        //     $error_message = "Failed to process payment. Please try again.";
        // }

        // For now, just a success message:
        $_SESSION['success'] = 'Payment of ₱' . number_format($amount, 2) . ' for ' . htmlspecialchars($customer->full_name) . ' has been recorded.';
        header('Location: index.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General form styling */
        .form-label {
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="mb-0">Process Payment for <?php echo htmlspecialchars($customer->full_name); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form action="payment.php" method="POST" enctype="multipart/form-data" id="paymentForm">
                        <input type="hidden" name="customer" value="<?php echo $customer_id; ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-label">Payment Amount *</label>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="GCash">GCash</option>
                                        <option value="PayMaya">PayMaya</option>
                                        <option value="Coins.ph">Coins.ph</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_date" class="form-label">Payment Date *</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_time" class="form-label">Payment Time *</label>
                                    <input type="time" name="payment_time" id="payment_time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="reference_number" class="form-label">Reference Number *</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="Transaction reference" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="screenshot" class="form-label">Transaction Proof (Optional)</label>
                            <input type="file" name="screenshot" id="screenshot" class="form-control" accept="image/*,.pdf">
                            <div class="form-text text-muted">
                                <small>Upload proof of payment (e.g., screenshot or receipt)</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
