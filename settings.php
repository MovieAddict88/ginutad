<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';
load_language($pdo);

// Check if the user is an admin
if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle Troubleshooting Guide POST/GET requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_guide'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $sql = 'INSERT INTO troubleshooting_guides (title, content, category, is_active) VALUES (:title, :content, :category, :is_active)';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: settings.php?tab=advanced'); // Redirect back to settings advanced tab
    exit;
}

if (isset($_GET['delete_guide'])) {
    $id = $_GET['delete_guide'];
    $sql = 'DELETE FROM troubleshooting_guides WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: settings.php?tab=advanced'); // Redirect back to settings advanced tab
    exit;
}


$page_title = translate('settings_title');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $response = ['success' => true, 'message' => 'Settings updated successfully.'];

    if (isset($_POST['site_title'])) {
        if (!update_setting($pdo, 'site_name', $_POST['site_title'])) {
            $response = ['success' => false, 'message' => 'Failed to update site name.'];
        }
    }

    if (isset($_POST['language'])) {
        if (!update_setting($pdo, 'language', $_POST['language'])) {
            $response = ['success' => false, 'message' => 'Failed to update language.'];
        }
    }

    // Handle site icon upload
    if (isset($_FILES['site_icon']) && $_FILES['site_icon']['error'] === UPLOAD_ERR_OK) {
        $target_dir = 'assets/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES['site_icon']['name'], PATHINFO_EXTENSION));
        $unique_filename = uniqid('icon_') . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;

        // Check if image file is a actual image or fake image
        $check = extension_loaded('fileinfo') ? getimagesize($_FILES['site_icon']['tmp_name']) : true;
        if ($check !== false) {
            // Allow certain file formats
            $allowed_exts = ['jpg', 'png', 'jpeg', 'gif', 'ico'];
            if (in_array($imageFileType, $allowed_exts)) {
                // Get old icon path to delete it later
                $old_icon = get_setting($pdo, 'site_icon');

                if (move_uploaded_file($_FILES['site_icon']['tmp_name'], $target_file)) {
                    if (update_setting($pdo, 'site_icon', $target_file)) {
                        // All good, now delete old icon
                        if ($old_icon && file_exists($old_icon)) {
                            unlink($old_icon);
                        }
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update site icon path.'];
                        // Delete the newly uploaded file since the DB update failed
                        if (file_exists($target_file)) {
                            unlink($target_file);
                        }
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Sorry, only JPG, JPEG, PNG, GIF & ICO files are allowed.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'File is not an image.'];
        }
    }

    echo json_encode($response);
    exit;
}

$site_name = get_setting($pdo, 'site_name');
$site_icon = get_setting($pdo, 'site_icon');
$current_lang = get_setting($pdo, 'language');

include 'header.php';
?>

<div class="settings-container">
    <!-- Settings Header -->
    <div class="settings-header">
        <div class="header-content">
            <h1><i class="fas fa-cog"></i> <?php echo $page_title; ?></h1>
            <p class="subtitle">Manage system configurations and preferences</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" id="resetBtn"><i class="fas fa-redo"></i> <?php echo translate('reset_all'); ?></button>
            <button class="btn btn-primary" id="saveBtn"><i class="fas fa-save"></i> <?php echo translate('save_changes'); ?></button>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <!-- Sidebar Navigation -->
        <div class="settings-sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-sliders-h"></i> Categories</h3>
            </div>
            <nav class="settings-nav">
                <a href="#" class="nav-item active" data-target="general">
                    <i class="fas fa-tachometer-alt"></i> General
                </a>
                <a href="ads_settings.php" class="nav-item">
                    <i class="fas fa-ad"></i> AdMob Settings
                </a>
                <a href="#" class="nav-item" data-target="security">
                    <i class="fas fa-shield-alt"></i> Security
                </a>
                <a href="#" class="nav-item" data-target="appearance">
                    <i class="fas fa-palette"></i> Appearance
                </a>
                <a href="#" class="nav-item" data-target="notifications">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="#" class="nav-item" data-target="advanced">
                    <i class="fas fa-code"></i> Advanced
                </a>
                <a href="admin_management.php" class="nav-item">
                    <i class="fas fa-users-cog"></i> Admin Management
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="system-info">
                    <h4><i class="fas fa-info-circle"></i> System Info</h4>
                    <p>PHP: <?php echo phpversion(); ?></p>
                    <p>Last Login: <?php echo date('Y-m-d H:i'); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Settings Area -->
        <div class="settings-main">
            <!-- General Settings Panel -->
            <div class="settings-panel active" id="general-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-tachometer-alt"></i> General Settings</h2>
                    <p>Configure basic system preferences and defaults</p>
                </div>
                
                <div class="panel-content">
                    <div class="settings-grid">
                        <!-- Site Title -->
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4><i class="fas fa-heading"></i> <?php echo translate('site_title'); ?></h4>
                                <p>The name of your website displayed in the browser title.</p>
                            </div>
                            <div class="setting-control">
                                <form method="POST" action="settings.php" id="site-settings-form" enctype="multipart/form-data">
                                    <input type="text" class="form-control" name="site_title" value="<?php echo htmlspecialchars($site_name); ?>" placeholder="Enter site title" style="margin-bottom: 10px;">
                                    <input type="file" class="form-control" name="site_icon" id="site_icon_input" style="margin-bottom: 10px;">
                                    <?php if ($site_icon): ?>
                                        <img src="<?php echo htmlspecialchars($site_icon); ?>" alt="Site Icon" style="width: 32px; height: 32px; margin-top: 10px;">
                                    <?php endif; ?>

                                    <!-- Language -->
                                    <select class="form-control" name="language">
                                        <option value="en" <?php echo ($current_lang == 'en') ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo ($current_lang == 'es') ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="fr" <?php echo ($current_lang == 'fr') ? 'selected' : ''; ?>>French</option>
                                        <option value="de" <?php echo ($current_lang == 'de') ? 'selected' : ''; ?>>German</option>
                                        <option value="fil" <?php echo ($current_lang == 'fil') ? 'selected' : ''; ?>>Filipino</option>
                                        <option value="zh" <?php echo ($current_lang == 'zh') ? 'selected' : ''; ?>>Chinese</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Timezone -->
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4><i class="fas fa-clock"></i> <?php echo translate('timezone'); ?></h4>
                                <p>Default timezone for date and time display.</p>
                            </div>
                            <div class="setting-control">
                                <select class="form-control">
                                    <option value="UTC" selected>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York">America/New_York</option>
                                    <option value="Europe/London">Europe/London</option>
                                    <option value="Asia/Tokyo">Asia/Tokyo</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Maintenance Mode -->
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4><i class="fas fa-wrench"></i> <?php echo translate('maintenance_mode'); ?></h4>
                                <p>Take the site offline for maintenance.</p>
                            </div>
                            <div class="setting-control">
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                                <span class="switch-label">Enabled</span>
                            </div>
                        </div>

                        <!-- Download App -->
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4><i class="fas fa-download"></i> Download the app</h4>
                                <p>Download the Android application (APK).</p>
                            </div>
                            <div class="setting-control">
                                <a href="download_app.php" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- AdMob Settings Panel -->
            <div class="settings-panel" id="admob-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-ad"></i> AdMob Settings</h2>
                    <p>Configure your AdMob integration and ad preferences</p>
                </div>
                
                <div class="panel-content">
                    <div class="alert alert-info">
                        <i class="fas fa-external-link-alt"></i>
                        <p>Click the button below to configure AdMob settings in detail.</p>
                        <a href="ads_settings.php" class="btn btn-primary mt-2">
                            <i class="fas fa-cogs"></i> Go to AdMob Settings
                        </a>
                    </div>
                    
                    <div class="settings-grid">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4><i class="fas fa-toggle-on"></i> Enable Ads</h4>
                                <p>Toggle advertising on or off throughout the application.</p>
                            </div>
                            <div class="setting-control">
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                                <span class="switch-label">Enabled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel" id="advanced-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-tools"></i> Troubleshooting Manager</h2>
                    <p>Manage troubleshooting guides and tips for users.</p>
                </div>
                <div class="panel-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Add New Guide/Tip</h3>
                        </div>
                        <div class="card-body">
                            <form action="settings.php" method="post">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="title" id="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea name="content" id="content" class="form-control" rows="10" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <input type="text" name="category" id="category" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="is_active">Active</label>
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="add_guide" class="btn btn-primary" value="Add Guide">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3>Existing Guides</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = 'SELECT * FROM troubleshooting_guides ORDER BY category, title';
                                    $guides = $pdo->query($sql)->fetchAll();
                                    foreach ($guides as $guide) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($guide['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($guide['category']) . "</td>";
                                        echo "<td>" . ($guide['is_active'] ? 'Active' : 'Inactive') . "</td>";
                                        echo "<td>";
                                        echo "<a href='edit_troubleshooting_guide.php?id=" . $guide['id'] . "' class='btn btn-primary'>Edit</a> ";
                                        echo "<a href='settings.php?delete_guide=" . $guide['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional panels for other categories would go here -->
            <!-- They would be shown/hidden based on navigation clicks -->
        </div>
    </div>
</div>

<!-- Modal for saving confirmation -->
<div class="modal" id="saveModal">
    <div class="modal-content">
        <h3><i class="fas fa-check-circle"></i> Changes Saved</h3>
        <p>Your settings have been successfully updated.</p>
        <button class="btn btn-primary" id="closeModal">OK</button>
    </div>
</div>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* Main Settings Container */
.settings-container {
    min-height: 100vh;
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Settings Header */
.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #4a6fa5 0%, #2c3e50 100%);
    color: white;
    padding: 1.5rem 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content h1 {
    margin: 0;
    font-weight: 600;
    font-size: 1.8rem;
}

.header-content .subtitle {
    margin: 0.3rem 0 0 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #4a6fa5;
    color: white;
}

.btn-primary:hover {
    background-color: #3a5a85;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Settings Content Layout */
.settings-content {
    display: flex;
    min-height: calc(100vh - 100px);
}

/* Sidebar Styles */
.settings-sidebar {
    width: 260px;
    background-color: white;
    border-right: 1px solid #eaeaea;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}

.sidebar-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.settings-nav {
    flex: 1;
    padding: 1rem 0;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 0.9rem 1.5rem;
    color: #555;
    text-decoration: none;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
    gap: 0.8rem;
}

.nav-item:hover {
    background-color: #f8f9fa;
    color: #4a6fa5;
    border-left-color: #4a6fa5;
}

.nav-item.active {
    background-color: #f0f5ff;
    color: #4a6fa5;
    font-weight: 500;
    border-left-color: #4a6fa5;
}

.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid #f0f0f0;
    background-color: #f9f9f9;
}

.system-info h4 {
    margin: 0 0 0.8rem 0;
    font-size: 1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.system-info p {
    margin: 0.3rem 0;
    font-size: 0.85rem;
    color: #666;
}

/* Main Settings Area */
.settings-main {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.settings-panel {
    display: none;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.settings-panel.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.panel-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f0f0f0;
    background-color: #fafbfc;
}

.panel-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.panel-header p {
    margin: 0.5rem 0 0 0;
    color: #666;
    font-size: 0.95rem;
}

.panel-content {
    padding: 2rem;
}

.settings-grid {
    display: grid;
    gap: 1.5rem;
    max-width: 800px;
}

.setting-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    border: 1px solid #eee;
    transition: all 0.2s ease;
}

.setting-item:hover {
    border-color: #d0d7e7;
    background-color: #f5f7fb;
}

.setting-info h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.setting-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
}

.setting-control {
    min-width: 200px;
}

.form-control {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #4a6fa5;
    box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.1);
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
    margin-right: 10px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #4a6fa5;
}

input:checked + .slider:before {
    transform: translateX(30px);
}

.switch-label {
    font-weight: 500;
    color: #333;
}

/* Alert Box */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.alert-info {
    background-color: #e8f4fd;
    border-left: 4px solid #4a6fa5;
    color: #2c3e50;
}

.alert i {
    font-size: 1.2rem;
    margin-top: 2px;
}

.alert p {
    margin: 0;
    flex: 1;
}

.mt-2 {
    margin-top: 0.5rem;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.modal-content h3 {
    margin: 0 0 1rem 0;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
}

.modal-content p {
    margin-bottom: 1.5rem;
    color: #666;
}

/* Responsive Design */
@media (max-width: 992px) {
    .settings-content {
        flex-direction: column;
    }
    
    .settings-sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #eaeaea;
    }
    
    .settings-nav {
        display: flex;
        overflow-x: auto;
        padding: 0.5rem 1rem;
    }
    
    .nav-item {
        flex-shrink: 0;
        border-left: none;
        border-bottom: 3px solid transparent;
        padding: 0.8rem 1rem;
    }
    
    .nav-item.active,
    .nav-item:hover {
        border-left: none;
        border-bottom-color: #4a6fa5;
    }
    
    .sidebar-footer {
        display: none;
    }
}

@media (max-width: 768px) {
    .settings-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.2rem;
    }
    
    .header-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: flex-end;
    }
    
    .setting-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .setting-control {
        min-width: 100%;
    }
    
    .panel-content {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .settings-main {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .header-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation between settings panels
    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const panels = document.querySelectorAll('.settings-panel');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            const target = this.getAttribute('data-target');
            
            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding panel
            panels.forEach(panel => {
                panel.classList.remove('active');
                if (panel.id === `${target}-panel`) {
                    panel.classList.add('active');
                }
            });
        });
    });
    
    // Save button functionality
    const saveBtn = document.getElementById('saveBtn');
    const resetBtn = document.getElementById('resetBtn');
    const saveModal = document.getElementById('saveModal');
    const closeModal = document.getElementById('closeModal');
    
    saveBtn.addEventListener('click', function() {
        const siteSettingsForm = document.getElementById('site-settings-form');
        const siteIconInput = document.getElementById('site_icon_input');

        // Create a FormData object to handle both text and file data
        const formData = new FormData(siteSettingsForm);

        // Use fetch to submit the form data
        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show a success message and reload the page
                saveModal.style.display = 'flex';
                setTimeout(() => location.reload(), 1500);
            } else {
                // Handle errors
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            alert('An error occurred. Please try again.');
        });
    });
    
    resetBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all settings to their default values?')) {
            // In a real application, reset form values
            // For this demo, just reload the page
            location.reload();
        }
    });
    
    closeModal.addEventListener('click', function() {
        saveModal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === saveModal) {
            saveModal.style.display = 'none';
        }
    });
    
    // Make AdMob settings link active when clicked
    const admobLink = document.querySelector('a[href="ads_settings.php"]');
    if (admobLink) {
        admobLink.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Hide all panels since we're navigating away
            panels.forEach(panel => panel.classList.remove('active'));
        });
    }

    // Handle opening tab from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        const targetNavItem = document.querySelector(`.nav-item[data-target="${tab}"]`);
        if (targetNavItem) {
            targetNavItem.click();
        }
    }
});
</script>

<?php include 'footer.php'; ?>

