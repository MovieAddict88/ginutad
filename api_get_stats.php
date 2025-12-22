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

// Fetch data for the dashboard
// Total Clients
$total_clients_stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE username != "admin"');
$total_clients = $total_clients_stmt->fetchColumn();

// Total Connected
$total_connected_stmt = $pdo->query('SELECT COUNT(DISTINCT user_id) FROM vpn_sessions WHERE end_time IS NULL');
$total_connected = $total_connected_stmt->fetchColumn();

// Total Disconnected
$total_disconnected = $total_clients - $total_connected;

// Total Banned
$total_banned_stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE banned = TRUE');
$total_banned = $total_banned_stmt->fetchColumn();

// Prepare the data array
$data = [
    'total_clients' => $total_clients,
    'total_connected' => $total_connected,
    'total_disconnected' => $total_disconnected,
    'total_banned' => $total_banned,
];

// Return the data as JSON
echo json_encode($data);
?>
