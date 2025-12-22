<?php
// This script resets the data usage for all users.
// It should be run daily at midnight via a cron job.
// Example cron job:
// 0 0 * * * /usr/bin/php /path/to/your/project/panel/reset_usage.php

require_once 'db_config.php';

try {
    $sql = "UPDATE users SET data_usage = 0";
    $pdo->exec($sql);
    echo "Data usage reset successfully.";
} catch (PDOException $e) {
    die("ERROR: Could not able to execute $sql. " . $e->getMessage());
}
?>
