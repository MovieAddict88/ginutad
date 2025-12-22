<?php
// Start session
session_start();

// Include the utility functions
require_once 'utils.php';

// Check if the user is logged in and is admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Fetch data for the dashboard
// Total Clients (only regular, non-banned users who are not reseller clients)
$total_clients_stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user" AND banned = 0 AND reseller_id IS NULL');
$total_clients = $total_clients_stmt->fetchColumn();

// Total Connected (only regular, non-banned users who are not reseller clients)
$total_connected_stmt = $pdo->query('SELECT COUNT(DISTINCT user_id) FROM vpn_sessions JOIN users ON vpn_sessions.user_id = users.id WHERE vpn_sessions.end_time IS NULL AND users.role = \'user\' AND users.banned = 0 AND users.reseller_id IS NULL');
$total_connected = $total_connected_stmt->fetchColumn();

// Total Disconnected
$total_disconnected = $total_clients - $total_connected;

// Total Banned (only regular users who are not reseller clients)
$total_banned_stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE banned = 1 AND role = "user" AND reseller_id IS NULL');
$total_banned = $total_banned_stmt->fetchColumn();

// Data for charts
$connection_data = [$total_connected, $total_disconnected];
$user_stats_data = [$total_clients, $total_connected, $total_disconnected, $total_banned];

// Translated chart labels
$chart_labels_connection = [translate('connected'), translate('disconnected')];
$chart_labels_user_stats = [translate('total_clients'), translate('connected'), translate('disconnected'), translate('banned')];


include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Dashboard</title>
    <style>
        /* Responsive CSS */
        .dashboard-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            color: white;
            border-radius: 15px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .stat-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card p {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card .material-icons {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            font-size: 4rem;
            opacity: 0.2;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #212529;
        }
        
        .chart-wrapper {
            flex: 1;
            position: relative;
        }
        
        /* Mobile-specific adjustments */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
                min-height: 100px;
            }
            
            .stat-card p {
                font-size: 2rem;
            }
            
            .stat-card .material-icons {
                font-size: 3rem;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .chart-card {
                padding: 20px;
                min-height: 350px;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: 10px;
            }
            
            .dashboard-title {
                font-size: 1.3rem;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-card p {
                font-size: 1.8rem;
            }
            
            .stat-card .material-icons {
                font-size: 2.5rem;
            }
            
            .chart-card {
                padding: 15px;
                min-height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="dashboard-title"><?php echo translate('dashboard_title'); ?></h1>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #4361ee, #4895ef);">
                <h3><?php echo translate('total_clients'); ?></h3>
                <p><?php echo $total_clients; ?></p>
                <span class="material-icons">people</span>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4cc9f0, #80ffdb);">
                <h3><?php echo translate('connected'); ?></h3>
                <p><?php echo $total_connected; ?></p>
                <span class="material-icons">wifi</span>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f72585, #f8961e);">
                <h3><?php echo translate('disconnected'); ?></h3>
                <p><?php echo $total_disconnected; ?></p>
                <span class="material-icons">wifi_off</span>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #212529, #6c757d);">
                <h3><?php echo translate('banned'); ?></h3>
                <a href="banned_users.php" style="text-decoration: none; color: white;">
                    <p><?php echo $total_banned; ?></p>
                </a>
                <span class="material-icons">block</span>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-container">
            <!-- Connection Status Chart -->
            <div class="chart-card">
                <h3><?php echo translate('connection_status'); ?></h3>
                <div class="chart-wrapper">
                    <canvas id="connectionStatusChart"></canvas>
                </div>
            </div>

            <!-- User Statistics Chart -->
            <div class="chart-card">
                <h3><?php echo translate('user_statistics'); ?></h3>
                <div class="chart-wrapper">
                    <canvas id="userStatsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart.js Global Configuration
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.font.size = 14;
        Chart.defaults.color = '#6c757d';

        // Connection Status Doughnut Chart
        const connectionCtx = document.getElementById('connectionStatusChart').getContext('2d');
        new Chart(connectionCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_labels_connection); ?>,
                datasets: [{
                    label: '<?php echo translate('connection_status'); ?>',
                    data: <?php echo json_encode($connection_data); ?>,
                    backgroundColor: [
                        'rgba(76, 201, 240, 0.8)', // success
                        'rgba(247, 37, 133, 0.8)'  // danger
                    ],
                    borderColor: [
                        '#4cc9f0',
                        '#f72585'
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        bottom: 20
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // User Statistics Bar Chart
        const userStatsCtx = document.getElementById('userStatsChart').getContext('2d');
        new Chart(userStatsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels_user_stats); ?>,
                datasets: [{
                    label: 'User Count',
                    data: <?php echo json_encode($user_stats_data); ?>,
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',  // primary
                        'rgba(76, 201, 240, 0.8)', // success
                        'rgba(248, 150, 30, 0.8)', // warning
                        'rgba(33, 37, 41, 0.8)'   // dark
                    ],
                    borderColor: [
                        '#4361ee',
                        '#4cc9f0',
                        '#f8961e',
                        '#212529'
                    ],
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
    </script>

<?php include 'footer.php'; ?>
