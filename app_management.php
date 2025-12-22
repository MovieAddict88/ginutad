<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';

$message = '';
$message_type = '';

function get_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_OK:
            return 'No error, file uploaded successfully.';
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload.';
        default:
            return 'Unknown upload error.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_apk'])) {
    $version_code = trim($_POST['version_code']);
    $version_name = trim($_POST['version_name']);
    $upload_dir = 'updates/';
    $parent_dir = dirname($upload_dir);

    if (!is_writable($parent_dir)) {
        $message = "Error: The directory '{$parent_dir}' is not writable by the web server. Please check file permissions (e.g., `chmod 755 php`).";
        $message_type = 'error';
        goto end_of_logic;
    }

    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $message = "Error: Failed to create the '{$upload_dir}' directory. Please check parent directory permissions.";
            $message_type = 'error';
            goto end_of_logic;
        }
    }
    
    if (!is_writable($upload_dir)) {
        $message = "Error: The directory '{$upload_dir}' was created but is not writable. Please check file permissions (e.g., `chmod 755 php/updates`).";
        $message_type = 'error';
        goto end_of_logic;
    }

    if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] != UPLOAD_ERR_OK) {
        $error_code = isset($_FILES['apk_file']['error']) ? $_FILES['apk_file']['error'] : UPLOAD_ERR_NO_FILE;
        $message = 'Upload Error: ' . get_upload_error_message($error_code);
        $message_type = 'error';
        goto end_of_logic;
    }
    
    $file_name = basename($_FILES['apk_file']['name']);
    $safe_file_name = preg_replace("/[^a-zA-Z0-9-_\.]/", "", $file_name);
    $target_file = $upload_dir . time() . '_' . $safe_file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $file_size = $_FILES['apk_file']['size'];

    if (empty($version_code) || empty($version_name)) {
        $message = 'Error: Version code and version name cannot be empty.';
        $message_type = 'error';
    } elseif ($file_type != 'apk') {
        $message = 'Error: Only .apk files are allowed.';
        $message_type = 'error';
    } elseif ($file_size > 50000000) { // 50MB limit
        $message = 'Error: File size must be less than 50MB.';
        $message_type = 'error';
    } else {
        if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $target_file)) {
            try {
                $sql = 'INSERT INTO app_updates (version_code, version_name, apk_path, file_size) VALUES (:version_code, :version_name, :apk_path, :file_size)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'version_code' => $version_code,
                    'version_name' => $version_name,
                    'apk_path' => $target_file,
                    'file_size' => $file_size
                ]);
                $message = 'APK uploaded and version information saved successfully.';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'error';
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
            }
        } else {
            $message = "Error: Failed to move uploaded file. This could be due to incorrect permissions on the temporary directory or the destination '{$upload_dir}' directory. Please check your server's PHP error logs for more details.";
            $message_type = 'error';
        }
    }
}
end_of_logic:

try {
    $stmt = $pdo->query('SELECT version_code, version_name, apk_path, file_size, upload_date FROM app_updates ORDER BY upload_date DESC');
    $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $updates = [];
    $message = "Error fetching updates: " . $e->getMessage();
    $message_type = 'error';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Management - VPN Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="content">
        <div class="container">
            <div class="page-header">
                <h1>App Management</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Upload New APK</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert <?php echo $message_type == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="app_management.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Version Code</label>
                            <input type="text" name="version_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Version Name</label>
                            <input type="text" name="version_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">APK File</label>
                            <input type="file" name="apk_file" class="form-control" accept=".apk" required>
                            <small class="form-text text-muted">Max file size: 50MB. File must be an .apk file.</small>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="upload_apk" class="btn btn-primary">
                                <span class="material-icons">cloud_upload</span>
                                Upload APK
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>System Diagnostics</h3>
                </div>
                <div class="card-body">
                    <div class="system-check">
                        <div class="check-list">
                            <?php
                            $updates_dir = 'updates/';
                            $parent_dir = '.';
                            $checks = [
                                "'php/' Directory Writable" => is_writable($parent_dir),
                                "'updates/' Directory Exists" => file_exists($updates_dir) && is_dir($updates_dir),
                                "'updates/' Directory Writable" => is_writable($updates_dir),
                                "PHP `upload_max_filesize`" => ini_get('upload_max_filesize'),
                                "PHP `post_max_size`" => ini_get('post_max_size'),
                            ];
                            
                            foreach ($checks as $check_name => $status) {
                                $is_boolean = is_bool($status);
                                $status_text = $is_boolean ? ($status ? '✓ OK' : '✗ Failed') : htmlspecialchars($status);
                                $status_class = $is_boolean ? ($status ? 'check-ok' : 'check-fail') : '';
                                echo '<div class="check-item">';
                                echo '<span class="check-name">' . $check_name . '</span>';
                                echo '<span class="check-status ' . $status_class . '">' . $status_text . '</span>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Uploaded APKs</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($updates)): ?>
                        <p>No APKs have been uploaded yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Version Code</th>
                                        <th>Version Name</th>
                                        <th>File Path</th>
                                        <th>Size</th>
                                        <th>Upload Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($updates as $update): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($update['version_code']); ?></td>
                                            <td><?php echo htmlspecialchars($update['version_name']); ?></td>
                                            <td><?php echo htmlspecialchars($update['apk_path']); ?></td>
                                            <td><?php echo round($update['file_size'] / 1024 / 1024, 20); ?> MB</td>
                                            <td><?php echo htmlspecialchars($update['upload_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
<style>
.system-check {
    margin-bottom: 30px;
}
.check-list {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}
.check-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #ddd;
}
.check-item:last-child {
    border-bottom: none;
}
.check-name {
    font-weight: 500;
}
.check-status {
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
}
.check-ok {
    background-color: rgba(76, 201, 240, 0.1);
    color: #4CAF50;
}
.check-fail {
    background-color: rgba(247, 37, 133, 0.1);
    color: #F44336;
}
</style>