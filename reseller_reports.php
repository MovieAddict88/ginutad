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

// Fetch client bandwidth usage
$stmt = $pdo->prepare("
    SELECT u.username, u.data_usage
    FROM users u
    JOIN reseller_clients rc ON u.id = rc.client_id
    WHERE rc.reseller_id = :reseller_id
    ORDER BY u.data_usage DESC
");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$bandwidth_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch financial performance data (commissions over time)
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, SUM(commission_earned) as total_commission
    FROM commissions
    WHERE reseller_id = :reseller_id
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$financial_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bandwidth_labels = json_encode(array_column($bandwidth_data, 'username'));
$bandwidth_values = json_encode(array_column($bandwidth_data, 'data_usage'));
$financial_labels = json_encode(array_column($financial_data, 'date'));
$financial_values = json_encode(array_column($financial_data, 'total_commission'));

include 'header.php';
?>

<div class="page-header">
    <h2>Reports</h2>
    <div class="page-actions">
        <a href="reseller_dashboard.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Client Bandwidth Usage</h3>
            </div>
            <div class="card-body">
                <canvas id="bandwidthChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Financial Performance</h3>
            </div>
            <div class="card-body">
                <canvas id="financialChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bandwidthCtx = document.getElementById('bandwidthChart').getContext('2d');
        var bandwidthChart = new Chart(bandwidthCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $bandwidth_labels; ?>,
                datasets: [{
                    label: 'Data Usage (bytes)',
                    data: <?php echo $bandwidth_values; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var financialCtx = document.getElementById('financialChart').getContext('2d');
        var financialChart = new Chart(financialCtx, {
            type: 'line',
            data: {
                labels: <?php echo $financial_labels; ?>,
                datasets: [{
                    label: 'Commission Earned ($)',
                    data: <?php echo $financial_values; ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php include 'footer.php'; ?>
