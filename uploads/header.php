<?php
require_once 'db_config.php';
require_once 'utils.php';
$site_name = get_setting($pdo, 'site_name');
$site_icon = get_setting($pdo, 'site_icon');
$current_page = basename($_SERVER['PHP_SELF']);

// Define logo path - check if it exists, otherwise use default
$logo_path = 'assets/orig_cs.png';
$logo_exists = file_exists($logo_path);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=5.0'>
    <meta name='apple-mobile-web-app-capable' content='yes'>
    <meta name='mobile-web-app-capable' content='yes'>
    <title><?php echo ucwords(str_replace('_', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    <?php if ($site_icon): ?>
        <link rel='icon' href='<?php echo htmlspecialchars($site_icon); ?>' type='image/x-icon'>
        <link rel='apple-touch-icon' href='<?php echo htmlspecialchars($site_icon); ?>'>
    <?php endif; ?>
    <link rel='stylesheet' href='style.css?v=1.2'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Mobile viewport optimization -->
    <style>
        @media (max-width: 768px) {
            .mobile-nav-toggle {
                display: flex !important;
            }
            
            .sidebar-logo img {
                max-width: 120px !important;
            }
            
            .page-title {
                font-size: 1.25rem !important;
                margin-left: 60px !important;
            }
        }
        
        @media (max-width: 480px) {
            .sidebar-logo img {
                max-width: 100px !important;
            }
            
            .mobile-nav-toggle {
                width: 44px !important;
                height: 44px !important;
                font-size: 1.25rem !important;
            }
            
            .page-title {
                font-size: 1.1rem !important;
                margin-left: 50px !important;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar-logo img {
                max-width: 140px !important;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <span class="material-icons" id="menuIcon">menu</span>
    </button>
    
    <!-- Mobile Page Title (Visible only on mobile) -->
    <div class="mobile-page-title" id="mobilePageTitle">
        <h1><?php echo ucwords(str_replace('_', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?></h1>
    </div>
    
    <div class='sidebar' id="sidebar">
        <div class='sidebar-header'>
            <div class="sidebar-logo">
                <?php if ($logo_exists): ?>
                    <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_name); ?> Logo" style="width: 180px; max-width: 100%; height: auto; margin-bottom: 10px; border-radius: 4px;">
                <?php else: ?>
                    <!-- Fallback to site name if logo doesn't exist -->
                    <h2 style="display: flex; align-items: center; gap: 10px; margin: 0;">
                        <span class="material-icons">vpn_lock</span>
                        <?php echo htmlspecialchars($site_name); ?>
                    </h2>
                <?php endif; ?>
            </div>
            <?php if ($logo_exists): ?>
                <h2 style="font-size: 1.1rem; margin-top: 5px; opacity: 0.9; font-weight: 600; text-align: center;">
                    <?php echo htmlspecialchars($site_name); ?>
                </h2>
            <?php endif; ?>
        </div>
        <nav aria-label="Main navigation">
            <ul>
                <li class='<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>'>
                    <a href='dashboard.php' style="align-items: center;">
                        <span class="material-icons">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>'>
                    <a href='index.php' style="align-items: center;">
                        <span class="material-icons">people</span>
                        <span>User Management</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'monitoring.php') ? 'active' : ''; ?>'>
                    <a href='monitoring.php' style="align-items: center;">
                        <span class="material-icons">monitoring</span>
                        <span>VPN Monitoring</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'upload_profiles.php') ? 'active' : ''; ?>'>
                    <a href='upload_profiles.php' style="align-items: center;">
                        <span class="material-icons">cloud_upload</span>
                        <span>Upload Profiles</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'profiles.php') ? 'active' : ''; ?>'>
                    <a href='profiles.php' style="align-items: center;">
                        <span class="material-icons">manage_accounts</span>
                        <span>Manage Profiles</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'promo_manager.php') ? 'active' : ''; ?>'>
                    <a href='promo_manager.php' style="align-items: center;">
                        <span class="material-icons">card_giftcard</span>
                        <span>Promo Manager</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'settings.php' || $current_page == 'ads_settings.php') ? 'active' : ''; ?>'>
                    <a href='settings.php' style="align-items: center;">
                        <span class="material-icons">settings</span>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href='logout.php' style="align-items: center; color: rgba(255, 255, 255, 0.8);">
                        <span class="material-icons">logout</span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Sidebar footer for mobile -->
        <div class="sidebar-footer" style="padding: 15px; margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); display: none;">
            <p style="font-size: 0.8rem; opacity: 0.7; text-align: center;">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?></p>
        </div>
    </div>
    <div class='content'>
        <div class='container'>