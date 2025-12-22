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

// Fetch banned users
$sql_banned = 'SELECT id, username FROM users WHERE role = "user" AND banned = 1';
$stmt_banned = $pdo->query($sql_banned);
$bannedUsers = $stmt_banned->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="page-header">
    <h1><?php echo translate('banned_users'); ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('banned_users_list'); ?></h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class='table'>
                <thead>
                    <tr>
                        <th><?php echo translate('id'); ?></th>
                        <th><?php echo translate('username'); ?></th>
                        <th><?php echo translate('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bannedUsers as $user) : ?>
                        <tr>
                            <td data-label='ID'><?php echo $user['id']; ?></td>
                            <td data-label='Username'><?php echo htmlspecialchars($user['username']); ?></td>
                            <td data-label='Actions'>
                                <a href='unban_user.php?id=<?php echo $user['id']; ?>' 
   style="display: inline-block; padding: 8px 16px; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold;">
   <?php echo translate('unban_user'); ?>
</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
