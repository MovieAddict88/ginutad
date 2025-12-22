<?php
// api_get_ping.php

// Set the content type to application/json
header('Content-Type: application/json');

// Get the profile ID from the request, ensuring it's an integer
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profile_id <= 0) {
    // If the profile ID is invalid, return an error response
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid profile ID']);
    exit;
}

// Simulate ping (in ms)
$ping = rand(20, 200);

// Simulate signal strength (in percentage)
$signal_strength = rand(30, 100);

// Return the data as a JSON response
echo json_encode([
    'ping' => $ping,
    'signal_strength' => $signal_strength
]);
?>