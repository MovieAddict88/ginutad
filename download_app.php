<?php
session_start();
require_once 'auth.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$apk_url = 'https://web.cornerstone-its-mobiledata.com/Cornerstone-ITS.apk';
$apk_file_name = 'Cornerstone-ITS.apk';

// Set headers to force download
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="' . $apk_file_name . '"');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// Read the file from the URL and output it to the browser
@readfile($apk_url);
exit;
?>
