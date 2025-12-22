<?php
// Start session
session_start();

// Include the utility functions
require_once 'utils.php';

// Check if the user is logged in and is admin, otherwise return an error
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Check if a profile ID is provided in the URL
if (!isset($_GET['profile_id']) || empty($_GET['profile_id'])) {
    // Return an empty array if no profile ID is provided
    echo json_encode([]);
    exit;
}

$profile_id = $_GET['profile_id'];

// Fetch profile monitoring data for the specific profile
$sql = 'SELECT
            p.name AS profile_name,
            COUNT(DISTINCT vs.user_id) AS concurrent_users,
            SUM(vs.bytes_in + vs.bytes_out) AS total_data_usage
        FROM
            vpn_profiles p
        LEFT JOIN
            vpn_sessions vs ON p.id = vs.profile_id
        WHERE
            p.id = :profile_id AND vs.session_status = "active"
        GROUP BY
            p.name';
$stmt = $pdo->prepare($sql);
$stmt->execute(['profile_id' => $profile_id]);
$profile_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the data usage to be human-readable
foreach ($profile_data as &$profile) {
    $profile['total_data_usage'] = format_bytes($profile['total_data_usage']);
}

// Set the content type to JSON and output the data
header('Content-Type: application/json');
echo json_encode($profile_data);
?>
