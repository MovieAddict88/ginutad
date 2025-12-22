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

// Get user ID from the URL
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId > 0) {
    // Fetch user's username
    $userSql = 'SELECT username FROM users WHERE id = :userId AND role = "user"';
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute(['userId' => $userId]);
    $user = $userStmt->fetch();
    
    if (!$user) {
        header('location: monitoring.php');
        exit;
    }
    
    $username = $user['username'];

    // Fetch user's VPN sessions
    $sql = 'SELECT * FROM vpn_sessions WHERE user_id = :userId ORDER BY start_time DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId]);
    $sessions = $stmt->fetchAll();
} else {
    // Redirect if no user ID is provided
    header('location: monitoring.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h1>Session History for <?php echo htmlspecialchars($username); ?></h1>
    <div class="page-actions">
        <a class='btn btn-secondary' href='monitoring.php'>
            <span class="material-icons">arrow_back</span>
            Back to Monitoring
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Session Details</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class='table'>
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>IP Address</th>
                        <th>Bytes In</th>
                        <th>Bytes Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sessions)) : ?>
                        <?php foreach ($sessions as $session) : ?>
                            <tr>
                                <td data-label='Session ID'><?php echo $session['id']; ?></td>
                                <td data-label='Start Time'><?php echo $session['start_time']; ?></td>
                                <td data-label='End Time'><?php echo $session['end_time'] ? $session['end_time'] : 'Still Active'; ?></td>
                                <td data-label='IP Address'><?php echo htmlspecialchars($session['ip_address']); ?></td>
                                <td data-label='Bytes In'><?php echo format_bytes($session['bytes_in']); ?></td>
                                <td data-label='Bytes Out'><?php echo format_bytes($session['bytes_out']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No sessions found for this user.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>