<?php
// update_system.php - Add upload functionality to existing system
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

$message = '';
$message_type = '';

// Process the system update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_system'])) {
    try {
        // 1. Check if required PHP extensions are available
        $required_extensions = ['zip'];
        $missing_extensions = [];
        
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }
        
        if (!empty($missing_extensions)) {
            throw new Exception('Missing PHP extensions: ' . implode(', ', $missing_extensions));
        }
        
        // 2. Check file permissions - Only check current directory, not profiles.zip
        $files_to_check = ['.'];
        $permission_errors = [];
        
        foreach ($files_to_check as $file) {
            if (!is_writable($file)) {
                $permission_errors[] = $file;
            }
        }
        
        if (!empty($permission_errors)) {
            throw new Exception('Directory not writable. Please check file permissions.');
        }
        
        // 3. Create upload_profile.php file
        $upload_profile_content = '<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION[\'loggedin\']) || $_SESSION[\'loggedin\'] !== true) {
    header(\'location: login.php\');
    exit;
}

// Include the database connection file
require_once \'db_config.php\';

$message = \'\';
$message_type = \'\';

// Process file upload
if ($_SERVER[\'REQUEST_METHOD\'] == \'POST\' && isset($_FILES[\'profile_zip\'])) {
    $upload_dir = \'./\';
    $target_file = $upload_dir . \'profiles.zip\';
    $backup_file = $upload_dir . \'profiles_backup_\' . date(\'Y-m-d_H-i-s\') . \'.zip\';
    
    // Check if file is a valid ZIP
    $file_type = strtolower(pathinfo($_FILES[\'profile_zip\'][\'name\'], PATHINFO_EXTENSION));
    if ($file_type != \'zip\') {
        $message = \'Error: Only ZIP files are allowed.\';
        $message_type = \'error\';
    } elseif ($_FILES[\'profile_zip\'][\'size\'] > 10000000) { // 10MB limit
        $message = \'Error: File size must be less than 10MB.\';
        $message_type = \'error\';
    } else {
        // Create backup of existing file if it exists
        if (file_exists($target_file)) {
            if (!copy($target_file, $backup_file)) {
                $message = \'Warning: Could not create backup of existing file.\';
                $message_type = \'error\';
            }
        }
        
        // Upload new file
        if (move_uploaded_file($_FILES[\'profile_zip\'][\'tmp_name\'], $target_file)) {
            $message = \'Profile ZIP uploaded successfully!\';
            $message_type = \'success\';
            
            // Log the upload action
            $sql = \'INSERT INTO vpn_sessions (user_id, start_time, ip_address) VALUES (:user_id, NOW(), :ip_address)\';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                \'user_id\' => $_SESSION[\'id\'],
                \'ip_address\' => $_SERVER[\'REMOTE_ADDR\']
            ]);
        } else {
            $message = \'Error uploading file. Please check file permissions.\';
            $message_type = \'error\';
            
            // Restore backup if upload failed and backup exists
            if (file_exists($backup_file)) {
                copy($backup_file, $target_file);
                unlink($backup_file);
            }
        }
    }
}

// Get current page for navigation
$current_page = basename($_SERVER[\'PHP_SELF\']);
?>
<!DOCTYPE html>
<html lang=\'en\'>
<head>
    <meta charset=\'UTF-8\'>
    <meta name=\'viewport\' content=\'width=device-width, initial-scale=1.0\'>
    <title>Upload Profiles - VPN Admin Panel</title>
    <link rel=\'stylesheet\' href=\'style.css\'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <button class="mobile-nav-toggle" id="mobileNavToggle">
        <span class="material-icons">menu</span>
    </button>
    
    <div class=\'sidebar\' id="sidebar">
        <div class=\'sidebar-header\'>
            <h2>VPN Panel</h2>
        </div>
        <ul>
            <li class="<?php echo ($current_page == \'index.php\') ? \'active\' : \'\'; ?>">
                <a href=\'index.php\'>
                    <span class="material-icons">people</span>
                    User Management
                </a>
            </li>
            <li class="<?php echo ($current_page == \'upload_profile.php\') ? \'active\' : \'\'; ?>">
                <a href=\'upload_profile.php\'>
                    <span class="material-icons">cloud_upload</span>
                    Upload Profiles
                </a>
            </li>
            <li class="<?php echo ($current_page == \'monitoring.php\') ? \'active\' : \'\'; ?>">
                <a href=\'monitoring.php\'>
                    <span class="material-icons">monitoring</span>
                    VPN Monitoring
                </a>
            </li>
            <li>
                <a href=\'logout.php\'>
                    <span class="material-icons">logout</span>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    <div class=\'content\'>
        <div class=\'container\'>

<div class="page-header">
    <h1>Upload Profile ZIP</h1>
    <div class="page-actions">
        <a href=\'index.php\' class=\'btn btn-secondary\'>
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Upload New Profile Package</h3>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert <?php echo $message_type == \'success\' ? \'alert-success\' : \'alert-danger\'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <p>Upload a new <code>profiles.zip</code> file to update the VPN configurations. The file should be encrypted with the same password stored in the database.</p>
            
            <form action=\'upload_profile.php\' method=\'post\' enctype=\'multipart/form-data\'>
                <div class=\'form-group\'>
                    <label class="form-label">Profile ZIP File</label>
                    <input type=\'file\' name=\'profile_zip\' class=\'form-control\' accept=\'.zip\' required>
                    <small class="form-text text-muted">Maximum file size: 10MB. File must be a ZIP archive.</small>
                </div>
                
                <div class=\'form-group\'>
                    <button type=\'submit\' class=\'btn btn-primary\'>
                        <span class="material-icons">cloud_upload</span>
                        Upload Profile ZIP
                    </button>
                    <a class=\'btn btn-link\' href=\'index.php\'>Cancel</a>
                </div>
            </form>
        </div>

        <div class="mt-4">
            <h4>Current File Information</h4>
            <?php
            $current_file = \'./profiles.zip\';
            if (file_exists($current_file)) {
                $file_size = filesize($current_file);
                $file_date = date(\'Y-m-d H:i:s\', filemtime($current_file));
                echo "<p><strong>File:</strong> profiles.zip</p>";
                echo "<p><strong>Size:</strong> " . number_format($file_size) . " bytes</p>";
                echo "<p><strong>Last Modified:</strong> $file_date</p>";
                
                // Show backup files if any exist
                $backup_files = glob(\'./profiles_backup_*.zip\');
                if (!empty($backup_files)) {
                    echo "<h5>Backup Files:</h5>";
                    echo "<ul>";
                    foreach ($backup_files as $backup) {
                        $backup_size = filesize($backup);
                        $backup_date = date(\'Y-m-d H:i:s\', filemtime($backup));
                        echo "<li>$backup (" . number_format($backup_size) . " bytes) - $backup_date</li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "<p class=\'text-danger\'>No profiles.zip file found on server. Upload a file to get started.</p>";
            }
            ?>
        </div>
    </div>
</div>

<script>
    // Mobile navigation toggle
    document.getElementById(\'mobileNavToggle\').addEventListener(\'click\', function() {
        document.getElementById(\'sidebar\').classList.toggle(\'active\');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener(\'click\', function(event) {
        const sidebar = document.getElementById(\'sidebar\');
        const toggle = document.getElementById(\'mobileNavToggle\');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target) && 
            sidebar.classList.contains(\'active\')) {
            sidebar.classList.remove(\'active\');
        }
    });
    
    // Handle window resize
    window.addEventListener(\'resize\', function() {
        if (window.innerWidth > 768) {
            document.getElementById(\'sidebar\').classList.remove(\'active\');
        }
    });
</script>
</body>
</html>';

        if (file_put_contents('upload_profile.php', $upload_profile_content) === false) {
            throw new Exception('Failed to create upload_profile.php - check directory permissions');
        }
        
        // 4. Update header.php to include upload link
        $header_content = file_get_contents('header.php');
        if (strpos($header_content, 'upload_profile.php') === false) {
            // Find the navigation section and add the upload link
            $new_nav_item = '
            <li class=\'<?php echo ($current_page == \'upload_profile.php\') ? \'active\' : \'\'; ?>\'>
                <a href=\'upload_profile.php\'>
                    <span class="material-icons">cloud_upload</span>
                    Upload Profiles
                </a>
            </li>';
            
            // Insert after User Management link
            $header_content = str_replace(
                "<li class='<?php echo (\$current_page == 'index.php') ? 'active' : ''; ?>'>\n                <a href='index.php'>\n                    <span class=\"material-icons\">people</span>\n                    User Management\n                </a>\n            </li>",
                "<li class='<?php echo (\$current_page == 'index.php') ? 'active' : ''; ?>'>\n                <a href='index.php'>\n                    <span class=\"material-icons\">people</span>\n                    User Management\n                </a>\n            </li>{$new_nav_item}",
                $header_content
            );
            
            if (file_put_contents('header.php', $header_content) === false) {
                throw new Exception('Failed to update header.php - check file permissions');
            }
        }
        
        // 5. Add CSS styles to style.css if they don't exist
        $css_content = file_get_contents('style.css');
        $additional_css = '
/* Additional styles for upload functionality */
.form-text {
    font-size: 0.875rem;
    color: var(--gray);
    margin-top: 4px;
}

.text-muted {
    color: var(--gray) !important;
}

.mt-4 {
    margin-top: 24px;
}

.alert-success {
    background-color: rgba(76, 201, 240, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
}

.alert-danger {
    background-color: rgba(247, 37, 133, 0.1);
    border: 1px solid var(--danger);
    color: var(--danger);
}

code {
    background-color: var(--gray-light);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    color: var(--danger);
}

.text-danger {
    color: var(--danger);
}

.btn-info {
    background-color: var(--info);
}

.btn-info:hover {
    background-color: #3a7be0;
    transform: translateY(-2px);
}';

        if (strpos($css_content, 'Additional styles for upload functionality') === false) {
            $css_content .= $additional_css;
            if (file_put_contents('style.css', $css_content) === false) {
                throw new Exception('Failed to update style.css - check file permissions');
            }
        }
        
        $message = 'System updated successfully! Upload functionality has been added.';
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = 'Update failed: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get current page for navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Update System - VPN Admin Panel</title>
    <link rel='stylesheet' href='style.css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <button class="mobile-nav-toggle" id="mobileNavToggle">
        <span class="material-icons">menu</span>
    </button>
    
    <div class='sidebar' id="sidebar">
        <div class='sidebar-header'>
            <h2>VPN Panel</h2>
        </div>
        <ul>
            <li class='<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>'>
                <a href='index.php'>
                    <span class="material-icons">people</span>
                    User Management
                </a>
            </li>
            <li class='<?php echo ($current_page == 'monitoring.php') ? 'active' : ''; ?>'>
                <a href='monitoring.php'>
                    <span class="material-icons">monitoring</span>
                    VPN Monitoring
                </a>
            </li>
            <li class='active'>
                <a href='update_system.php'>
                    <span class="material-icons">system_update</span>
                    Update System
                </a>
            </li>
            <li>
                <a href='logout.php'>
                    <span class="material-icons">logout</span>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    <div class='content'>
        <div class='container'>

<div class="page-header">
    <h1>System Update</h1>
    <div class="page-actions">
        <a href='index.php' class='btn btn-secondary'>
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add Upload Functionality</h3>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert <?php echo $message_type == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="system-check">
            <h4>System Requirements Check</h4>
            <div class="check-list">
                <?php
                $checks = [
                    'PHP ZIP Extension' => extension_loaded('zip'),
                    'Directory Write Permissions' => is_writable('.'),
                    'upload_profile.php Exists' => file_exists('upload_profile.php'),
                    'Database Connection' => true,
                ];
                
                foreach ($checks as $check_name => $status) {
                    echo '<div class="check-item">';
                    echo '<span class="check-name">' . $check_name . '</span>';
                    echo '<span class="check-status ' . ($status ? 'check-ok' : 'check-fail') . '">';
                    echo $status ? '✓ OK' : '✗ Failed';
                    echo '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <?php if (!is_writable('.')): ?>
        <div class="warning-box">
            <h4>⚠️ Permission Issue Detected</h4>
            <p>The current directory is not writable. This is required to create the upload functionality.</p>
            <p><strong>Quick Fix:</strong> Set directory permissions to 755 or 775:</p>
            <code>chmod 755 /path/to/your/directory</code>
            <p><strong>Or contact your hosting provider</strong> to ensure PHP has write permissions in this directory.</p>
        </div>
        <?php endif; ?>

        <div class="update-info">
            <h4>What will be updated:</h4>
            <ul>
                <li>Create <code>upload_profile.php</code> - File upload interface</li>
                <li>Update <code>header.php</code> - Add navigation link</li>
                <li>Update <code>style.css</code> - Add upload-specific styles</li>
                <li>Enable profile ZIP upload functionality</li>
            </ul>
            
            <div class="warning-box">
                <strong>Note:</strong> This update will add the ability to upload VPN profile packages. 
                Make sure your <code>profiles.zip</code> files are encrypted with the same password 
                stored in your database.
            </div>
        </div>

        <form action='update_system.php' method='post'>
            <div class='form-group'>
                <button type='submit' name='update_system' class='btn btn-primary' 
                        <?php echo (file_exists('upload_profile.php') || !is_writable('.')) ? 'disabled' : ''; ?>>
                    <span class="material-icons">system_update</span>
                    <?php 
                    if (file_exists('upload_profile.php')) {
                        echo 'Already Updated';
                    } elseif (!is_writable('.')) {
                        echo 'Fix Permissions First';
                    } else {
                        echo 'Update System Now';
                    }
                    ?>
                </button>
                <a class='btn btn-link' href='index.php'>Cancel</a>
            </div>
        </form>
        
        <?php if (file_exists('upload_profile.php')): ?>
        <div class="success-box">
            <h4>✅ Upload functionality is already installed</h4>
            <p>You can now <a href='upload_profile.php'>upload profile ZIP files</a> to your server.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.system-check {
    margin-bottom: 30px;
}

.check-list {
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.check-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--gray-light);
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
    color: var(--success);
}

.check-fail {
    background-color: rgba(247, 37, 133, 0.1);
    color: var(--danger);
}

.update-info {
    margin-bottom: 30px;
}

.update-info ul {
    padding-left: 20px;
    margin: 16px 0;
}

.update-info li {
    margin-bottom: 8px;
}

.warning-box {
    background-color: rgba(248, 150, 30, 0.1);
    border: 1px solid var(--warning);
    border-radius: var(--border-radius);
    padding: 16px;
    margin: 20px 0;
}

.warning-box h4 {
    margin-top: 0;
    color: var(--warning);
}

.warning-box code {
    background: rgba(0,0,0,0.1);
    padding: 8px 12px;
    border-radius: 4px;
    display: block;
    margin: 10px 0;
    font-family: monospace;
}

.success-box {
    background-color: rgba(76, 201, 240, 0.1);
    border: 1px solid var(--success);
    border-radius: var(--border-radius);
    padding: 20px;
    margin-top: 20px;
    text-align: center;
}

.success-box h4 {
    margin: 0 0 10px 0;
    color: var(--success);
}
</style>

<script>
    // Mobile navigation toggle
    document.getElementById('mobileNavToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('mobileNavToggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target) && 
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.getElementById('sidebar').classList.remove('active');
        }
    });
</script>
</body>
</html>