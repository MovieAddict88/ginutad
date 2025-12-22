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

// Check if a profile ID is provided in the URL
if (!isset($_GET['profile_id']) || empty($_GET['profile_id'])) {
    header('location: profiles.php');
    exit;
}

$profile_id = $_GET['profile_id'];

// Fetch profile details, including promo information and icon_path
$sql_profile_details = 'SELECT p.name, p.type, p.icon_path, pr.promo_name
                        FROM vpn_profiles p 
                        LEFT JOIN promos pr ON p.promo_id = pr.id 
                        WHERE p.id = :profile_id';
$stmt_profile_details = $pdo->prepare($sql_profile_details);
$stmt_profile_details->execute(['profile_id' => $profile_id]);
$profile = $stmt_profile_details->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    // Redirect or show an error if the profile doesn't exist
    header('location: profiles.php');
    exit;
}

$profile_name = $profile['name'];
$profile_type = $profile['type'];
$promo_name = $profile['promo_name'];
$icon_path = $profile['icon_path'];

// Fetch total data usage and total users for the profile
$sql_stats = 'SELECT 
                COUNT(DISTINCT vs.user_id) AS total_users,
                SUM(vs.bytes_in + vs.bytes_out) AS total_data_usage
              FROM vpn_sessions vs
              WHERE vs.profile_id = :profile_id';
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->execute(['profile_id' => $profile_id]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$total_users = $stats['total_users'] ?? 0;
$total_data_usage = $stats['total_data_usage'] ?? 0;

// Fetch individual user data
$sql_user_data = 'SELECT 
                    u.username,
                    SUM(vs.bytes_in + vs.bytes_out) AS user_data_usage
                  FROM vpn_sessions vs
                  JOIN users u ON vs.user_id = u.id
                  WHERE vs.profile_id = :profile_id
                  GROUP BY u.username
                  ORDER BY user_data_usage DESC';
$stmt_user_data = $pdo->prepare($sql_user_data);
$stmt_user_data->execute(['profile_id' => $profile_id]);
$user_data = $stmt_user_data->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<!-- Main container with inline styles for centering and padding -->
<div style="padding: 20px; max-width: 800px; margin: auto; font-family: Arial, sans-serif;">

    <!-- Flex container for the two pie charts -->
    <div style="display: flex; justify-content: space-around; margin-bottom: 20px;">
        <!-- Container for the Total Data Usage pie chart -->
        <div style="width: 45%; text-align: center;">
            <h3 style="margin-bottom: 10px;">TOTAL DATA USAGE</h3>
            <canvas id="dataUsageChart" width="200" height="200"></canvas>
        </div>
        <!-- Container for the Total Users pie chart -->
        <div style="width: 45%; text-align: center;">
            <h3 style="margin-bottom: 10px;">TOTAL USER</h3>
            <canvas id="userChart" width="200" height="200"></canvas>
        </div>
    </div>

    <!-- Container for the profile's icon and details -->
    <div style="text-align: center; margin-bottom: 20px;">
        <!-- Display the profile icon if available, otherwise a default -->
        <img src="<?php echo !empty($icon_path) ? htmlspecialchars($icon_path) : 'assets/us.png'; ?>" alt="Profile Icon" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px;">
        <!-- Display the profile name -->
        <h2 style="margin: 5px 0;"><?php echo htmlspecialchars($profile_name); ?></h2>
        <!-- Display the promo name if available -->
        <p style="margin: 5px 0;"><?php echo htmlspecialchars($promo_name); ?></p>
        <!-- Display the profile type with a styled badge -->
        <span style="background-color: #007bff; color: white; padding: 5px 10px; border-radius: 12px; font-size: 14px;"><?php echo htmlspecialchars($profile_type); ?></span>
    </div>

    <!-- Card for the user statistics table -->
    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px;">
        <h3 style="margin-bottom: 15px; text-align: center;">Profile Usage Statistics</h3>
        <!-- Table to display individual user data -->
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: #007bff; color: white;">
                <tr>
                    <th style="padding: 10px; text-align: left;">CLIENT NAME</th>
                    <th style="padding: 10px; text-align: right;">DATA USED</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through and display each user's data -->
                <?php foreach ($user_data as $user): ?>
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td style="padding: 10px; text-align: right;"><?php echo format_bytes($user['user_data_usage']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data for the charts from PHP
    const totalUsers = <?php echo $total_users; ?>;
    const totalDataUsage = <?php echo $total_data_usage; ?>;
    const userData = <?php echo json_encode($user_data); ?>;

    // Colors for the pie chart segments
    const chartColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

    // --- Chart for Total Data Usage ---
    const dataUsageCtx = document.getElementById('dataUsageChart').getContext('2d');
    const dataUsageData = {
        labels: userData.map(user => user.username),
        datasets: [{
            data: userData.map(user => user.user_data_usage),
            backgroundColor: chartColors,
        }]
    };
    new Chart(dataUsageCtx, {
        type: 'pie',
        data: dataUsageData,
        options: {
            responsive: true,
            legend: {
                display: false // Hide legend as per sketch
            }
        }
    });

    // --- Chart for Total Users ---
    // This chart will represent each user as an equal slice of the pie.
    const userCtx = document.getElementById('userChart').getContext('2d');
    const userDataForChart = {
        labels: userData.map(user => user.username),
        datasets: [{
            data: Array(userData.length).fill(1), // Each user gets an equal slice
            backgroundColor: chartColors,
        }]
    };
    new Chart(userCtx, {
        type: 'pie',
        data: userDataForChart,
        options: {
            responsive: true,
            legend: {
                display: false // Hide legend as per sketch
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>
