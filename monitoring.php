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

// Fetch aggregated VPN session data from the database
$sql_sessions = 'SELECT
            users.id AS user_id,
            users.username,
            COUNT(vpn_sessions.id) AS session_count,
            SUM(vpn_sessions.bytes_in) AS total_bytes_in,
            SUM(vpn_sessions.bytes_out) AS total_bytes_out,
            SUM(vpn_sessions.bytes_in + vpn_sessions.bytes_out) AS total_data_usage,
            MAX(vpn_sessions.start_time) AS last_activity
        FROM
            vpn_sessions
        JOIN
            users ON vpn_sessions.user_id = users.id
        WHERE
            users.role = "user" AND users.banned = 0
        GROUP BY
            users.id, users.username
        ORDER BY
            total_data_usage DESC';
$stmt_sessions = $pdo->query($sql_sessions);
$userSessionsData = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);


include 'header.php';
?>

<div class="page-header">
    <h1><?php echo translate('vpn_monitoring'); ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('vpn_usage_statistics'); ?></h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="vpnUsageChart"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('vpn_sessions'); ?></h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class='table table-clickable'>
                <thead>
                    <tr>
                        <th><?php echo translate('username'); ?></th>
                        <th><?php echo translate('total_sessions'); ?></th>
                        <th><?php echo translate('last_activity'); ?></th>
                        <th><?php echo translate('total_bytes_in'); ?></th>
                        <th><?php echo translate('total_bytes_out'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($userSessionsData as $session) :
                        $username = htmlspecialchars($session['username']);
                    ?>
                        <tr data-href="session_history.php?user_id=<?php echo $session['user_id']; ?>">
                            <td data-label='Username'><?php echo $username; ?></td>
                            <td data-label='Total Sessions'><?php echo $session['session_count']; ?></td>
                            <td data-label='Last Activity'><?php echo $session['last_activity']; ?></td>
                            <td data-label='Total Bytes In'><?php echo format_bytes($session['total_bytes_in']); ?></td>
                            <td data-label='Total Bytes Out'><?php echo format_bytes($session['total_bytes_out']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tr[data-href]');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                window.location.href = row.dataset.href;
            });
        });

        const chartData = <?php
            $chartData = [];
            foreach ($userSessionsData as $session) {
                $chartData[] = [
                    'username' => htmlspecialchars($session['username']),
                    'total_data_usage' => $session['total_data_usage']
                ];
            }
            echo json_encode($chartData);
        ?>;

        // Prepare the data for the chart
        const labels = chartData.map(item => item.username);
        const data = chartData.map(item => item.total_data_usage);

        // Generate a unique color for each bar
        const backgroundColors = data.map(() => {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            return `rgba(${r}, ${g}, ${b}, 0.7)`;
        });

        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        // Function to format bytes into a human-readable format
        function formatBytes(bytes, decimals = 2) {
            if (bytes <= 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            if (i < 0) {
                // This handles fractional bytes, which would otherwise result in a negative index.
                return parseFloat(bytes.toFixed(dm)) + ' ' + sizes[0];
            }

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        const ctx = document.getElementById('vpnUsageChart').getContext('2d');
        const vpnUsageChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: <?php echo json_encode(translate('total_data_usage')); ?>,
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatBytes(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.x !== null) {
                                    label += formatBytes(context.parsed.x);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php include 'footer.php'; ?>
