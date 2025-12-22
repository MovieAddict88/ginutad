<?php
// Start session
session_start();

// Check if the user is logged in, otherwise return an error
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Set header to return JSON
header('Content-Type: application/json');

// Include the database connection file
require_once 'db_config.php';

// Get the profile ID from the URL
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profile_id === 0) {
    echo json_encode(['error' => 'Profile ID not provided']);
    exit;
}

// Fetch profile details from the database, including management IP and port
$stmt = $pdo->prepare('SELECT mgmt_ip, mgmt_port, promo_type FROM vpn_profiles WHERE id = ?');
$stmt->execute([$profile_id]);
$profile = $stmt->fetch();

if (!$profile) {
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

// Open a socket connection to the OpenVPN management interface
$socket = @fsockopen($profile['mgmt_ip'], $profile['mgmt_port'], $errno, $errstr, 2);

if (!$socket) {
    echo json_encode(['error' => 'Could not connect to OpenVPN management interface']);
    exit;
}

// Send the "status" command to get the list of connected users
fwrite($socket, "status\n");
$status_output = '';
while (!feof($socket)) {
    $line = fgets($socket, 1024);
    if (strpos($line, 'END') !== false) {
        break;
    }
    $status_output .= $line;
}

// Parse the status output to get the list of connected users
$users = [];
$lines = explode("\n", $status_output);
$in_client_list = false;
$skip_header = false;
foreach ($lines as $line) {
    if (strpos($line, 'CLIENT_LIST') !== false) {
        $in_client_list = true;
        $skip_header = true; // Set flag to skip the next line, which is the header
        continue; // Skip the 'CLIENT_LIST' line itself
    }

    if ($in_client_list) {
        if ($skip_header) {
            $skip_header = false; // Unset the flag
            continue; // Skip the actual header row
        }

        // The ROUTING_TABLE or GLOBAL_STATS section marks the end of the client list
        if (strpos($line, 'ROUTING_TABLE') !== false || strpos($line, 'GLOBAL_STATS') !== false) {
            $in_client_list = false;
            break; // Exit the loop as we have all client data
        }

        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $parts = explode(',', $line);
        // A valid client data line should have at least 4 values we care about
        if (count($parts) >= 4) {
            $users[] = [
                'username' => $parts[0],   // Common Name
                'ip_address' => $parts[1], // Real Address
                'bytes_in' => $parts[2],   // Bytes Received
                'bytes_out' => $parts[3],  // Bytes Sent
            ];
        }
    }
}

// Prepare the data array
$data = [
    'user_count' => count($users),
    'users' => $users,
    'promo_type' => $profile['promo_type'],
];

// Return the data as JSON
fclose($socket);
echo json_encode($data);
?>
