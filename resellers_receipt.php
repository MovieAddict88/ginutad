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
$user_id = $_GET['customer'] ?? null;
if (!$user_id) {
    header('location: reseller_management.php');
    exit;
}

// Fetch user data
$sql = 'SELECT * FROM users WHERE id = ? AND is_reseller = 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('location: reseller_management.php');
    exit;
}

// Fetch payment history
$sql = 'SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC, payment_time DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset=" utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="component/css/bootstrap.css">
    <link rel="stylesheet" href="component/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="component/css/style.css">
    <link rel="stylesheet" href="component/css/reset.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <script src="component/js/modernizr.js"></script>
    <title>Reseller Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #000;
        }

        .statement-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .header-container {
            text-align: center;
            height: 120px;
            /* Adjust as needed */
            margin-bottom: 20px;
        }

        .header-container .logo {
            display: inline-block;
            vertical-align: middle;
        }

        .header-container .company-info {
            display: inline-block;
            vertical-align: middle;
            text-align: left;
            margin-left: 15px;
            font-size: 12px;
            line-height: 1.4;
        }

        .header-container .company-info strong {
            font-size: 24px;
            font-weight: bold;
        }

        .statement-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 40px 0;
        }

        .statement-title span {
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .customer-details-grid {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 5px 10px;
            margin-bottom: 20px;
        }

        .customer-details-grid strong {
            font-weight: bold;
        }

        .account-info {
            margin-bottom: 20px;
        }

        .account-info p {
            margin: 5px 0;
        }

        .account-summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .account-summary th,
        .account-summary td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .account-summary td:nth-child(2) {
            text-align: right;
        }

        .total-due {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        .total-due .leader {
            flex-grow: 1;
            border-bottom: 2px dotted #000;
            margin: 0 10px;
        }

        .footer-notes {
            margin-top: 40px;
            font-style: italic;
        }

        .highlight {
            background-color: yellow;
            padding: 10px;
            text-align: center;
            font-size: 18px;
        }

        .blue-bg {
            background-color: #284390 !important;
            color: white !important;
        }

        @media print {
            body {
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none;
            }

            .statement-container {
                width: 100%;
                margin: 0;
                padding: 10px;
                box-sizing: border-box;
            }

            .account-summary {
                width: 100%;
                font-size: 10px; /* Smaller font for print */
                table-layout: auto;
            }

            .account-summary th,
            .account-summary td {
                word-break: break-word; /* Break long words */
            }

            .contact-info-container, .footer-notes {
                text-align: left !important;
            }

            .header-container {
                position: relative !important;
                height: 120px !important;
                margin: 0 auto 20px auto;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .header-container .logo {
                position: relative !important;
                top: auto !important;
                left: auto !important;
                margin-right: 15px;
            }

            .header-container .logo img {
                max-width: 100px !important;
            }

            .header-container .company-info {
                position: relative !important;
                top: auto !important;
                left: auto !important;
                font-size: 12px !important;
                line-height: 1.4 !important;
                white-space: nowrap;
            }

            .customer-details-grid {
                text-align: left;
            }

            .blue-bg {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #284390 !important;
                color: white !important;
            }

            .blue-bg strong,
            .blue-bg b {
                color: white !important;
            }

            .highlight {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: yellow !important;
                color: black !important;
            }
        }
    </style>
</head>

<body>
    <div class="statement-container">
        <div class="header-container">
            <div class="logo">
                <img src="assets/orig_cs.png" alt="Company Logo" style="max-width: 100px;">
            </div>
            <div class="company-info">
                <strong>CORNERSTONE INNOVATE TECH SOL</strong><br>
                #11 Cassa Apartment Mambog IV, Bacoor, Cavite<br>
                Brix Bryan S. Villas-Prop.<br>
                NON-VAT Reg. TIN: 434-028-840-000
            </div>
        </div>

        <div class="statement-title"><span>OFFICIAL RECEIPT</span></div>

        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <a href="reseller_management.php" class="btn btn-secondary">Back to Reseller Management</a>
            <button class="btn btn-primary" onclick="window.print();">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        <p style="text-align: right;">Date: <?php echo htmlspecialchars(date("F d, Y")); ?></p>

        <div class="customer-details-grid">
            <strong>Name:</strong>
            <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>

            <strong>Contact Number:</strong>
            <span><?php echo htmlspecialchars($user['contact_number']); ?></span>
        </div>

        <table class="account-summary">
            <thead>
                <tr>
                    <th class="blue-bg">Date</th>
                    <th class="blue-bg">Time</th>
                    <th class="blue-bg">Payment Method</th>
                    <th class="blue-bg">Reference Number</th>
                    <th class="blue-bg">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_paid = 0;
                if (!empty($payments)) {
                    foreach ($payments as $payment) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($payment['payment_date']) . "</td>";
                        echo "<td>" . htmlspecialchars(date('h:i A', strtotime($payment['payment_time']))) . "</td>";
                        echo "<td>" . htmlspecialchars($payment['payment_method']) . "</td>";
                        echo "<td>" . htmlspecialchars($payment['reference_number']) . "</td>";
                        echo "<td>" . htmlspecialchars('₱' . number_format($payment['amount'], 2)) . "</td>";
                        echo "</tr>";
                        $total_paid += $payment['amount'];
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No payment history found.</td></tr>";
                }
                ?>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL PAID</td>
                    <td style="font-weight: bold;"><?php echo htmlspecialchars('₱' . number_format($total_paid, 2)); ?></td>
                </tr>
                 <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">CREDITS</td>
                    <td style="font-weight: bold;"><?php echo htmlspecialchars('₱' . number_format($user['credits'], 2)); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-notes">
            <p><em>Thank you for your payment.</em></p>
        </div>
        <hr>
        <div class="contact-info-container">
            <div class="contact-title">
                <strong>Contact us:</strong>
            </div>
            <table>
                <tr>
                    <td><strong>FB Page</strong></td>
                    <td>:</td>
                    <td>CORNER STONE INNOVATE TECH SOL</td>
                </tr>
                <tr>
                    <td><strong>Customer Service</strong></td>
                    <td>:</td>
                    <td>0951-6551142</td>
                </tr>
                <tr>
                    <td><strong>Billing Department</strong></td>
                    <td>:</td>
                    <td>0985-3429675</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
