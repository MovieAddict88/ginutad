<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection file
require_once 'db_config.php';

// Get the search query from the request
$query = $_GET['query'] ?? '';

// Prepare and execute the SQL statement
$sql = "SELECT username FROM users WHERE role = 'user' AND banned = 0 AND username LIKE :query";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':query', '%' . $query . '%');
$stmt->execute();

// Fetch the results
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the results as JSON
echo json_encode($users);
