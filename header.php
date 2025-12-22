<?php
require_once 'db_config.php';
require_once 'utils.php';
load_language($pdo);
$site_name = get_setting($pdo, 'site_name');
$site_icon = get_setting($pdo, 'site_icon');
$current_page = basename($_SERVER['PHP_SELF']);

// Check if the user is a client of a reseller and apply branding
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    $user_id = $_SESSION['id'];
    $sql = 'SELECT r.logo_path, r.primary_color, r.secondary_color, r.company_name FROM resellers r JOIN reseller_clients rc ON r.id = rc.reseller_id WHERE rc.client_id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $reseller_branding = $stmt->fetch();

    if ($reseller_branding) {
        $site_name = $reseller_branding['company_name'] ?: $site_name;
        $site_icon = $reseller_branding['logo_path'] ?: $site_icon;
        $primary_color = $reseller_branding['primary_color'];
        $secondary_color = $reseller_branding['secondary_color'];
    }
}


// Define logo path - check if it exists, otherwise use default
$logo_path = $site_icon ? $site_icon : 'assets/orig_cs.png';
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
    <!-- clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <!-- Logo profile styling -->
    <style>
        /* Logo Profile Styles */
        .logo-profile {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 5px solid #FFD700; /* Yellow border */
            padding: 5px;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3), 
                        0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            margin: 0 auto 15px;
            transition: all 0.3s ease;
        }
        
        .logo-profile:hover {
            border-color: #FFC107; /* Darker yellow on hover */
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.4), 
                        0 6px 20px rgba(0, 0, 0, 0.25);
            transform: scale(1.02);
        }
        
        .logo-profile::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 215, 0, 0.1) 50%, transparent 70%);
            z-index: 1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .logo-profile:hover::before {
            opacity: 1;
        }
        
        .logo-profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
        
        /* Status indicator (optional - can add online/offline status) */
        .logo-status {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #4CAF50; /* Green for online status */
            border: 3px solid white;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Alternative: Ring effect */
        .logo-ring {
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 50%;
            border: 2px solid rgba(255, 215, 0, 0.5);
            pointer-events: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .logo-profile {
                width: 140px;
                height: 140px;
                border-width: 4px;
                margin-bottom: 10px;
            }
            
            .logo-status {
                width: 18px;
                height: 18px;
                bottom: 12px;
                right: 12px;
            }
            
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
            .logo-profile {
                width: 120px;
                height: 120px;
                border-width: 3px;
            }
            
            .logo-status {
                width: 16px;
                height: 16px;
                bottom: 10px;
                right: 10px;
                border-width: 2px;
            }
            
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
            .logo-profile {
                width: 160px;
                height: 160px;
            }
            
            .sidebar-logo img {
                max-width: 140px !important;
            }
        }
        
        /* For Smart TV/Large Screens */
        @media (min-width: 1920px) {
            .logo-profile {
                width: 220px;
                height: 220px;
                border-width: 6px;
            }
            
            .logo-status {
                width: 25px;
                height: 25px;
                border-width: 4px;
            }
        }
        
        /* Animation for page load */
        @keyframes logoFadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .logo-profile {
            animation: logoFadeIn 0.5s ease-out;
        }
    </style>
    <?php if (isset($primary_color) && isset($secondary_color)): ?>
    <style>
        .sidebar {
            background-color: <?php echo htmlspecialchars($primary_color); ?> !important;
        }
        .btn-primary {
            background-color: <?php echo htmlspecialchars($secondary_color); ?> !important;
            border-color: <?php echo htmlspecialchars($secondary_color); ?> !important;
        }
    </style>
    <?php endif; ?>
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
                    <div class="logo-profile">
                        <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_name); ?> Logo">
                        <div class="logo-status" title="Online"></div>
                        <div class="logo-ring"></div>
                    </div>
                <?php else: ?>
                    <!-- Fallback to site name without lock icon -->
                    <h2 style="margin: 0; text-align: center; font-weight: 700; font-size: 1.5rem; color: white;">
                        <?php echo htmlspecialchars($site_name); ?>
                    </h2>
                <?php endif; ?>
            </div>
            <?php if ($logo_exists): ?>
                <h2 style="font-size: 1.1rem; margin-top: 5px; opacity: 0.9; font-weight: 600; text-align: center; color: white;">
                    <?php echo htmlspecialchars($site_name); ?>
                </h2>
            <?php endif; ?>
        </div>
        <nav aria-label="Main navigation">
            <ul>
                <li class='<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>'>
                    <a href='dashboard.php' style="align-items: center;">
                        <span class="material-icons">dashboard</span>
                        <span><?php echo translate('nav_dashboard'); ?></span>
                    </a>
                </li>
                <?php if (isset($_SESSION['is_reseller']) && $_SESSION['is_reseller']): ?>
                <li class='<?php echo ($current_page == 'reseller_dashboard.php') ? 'active' : ''; ?>'>
                    <a href='reseller_dashboard.php' style="align-items: center;">
                        <span class="material-icons">store</span>
                        <span>Reseller Dashboard</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class='<?php echo ($current_page == 'user_management.php' || $current_page == 'add_user.php') ? 'active' : ''; ?>'>
                    <a href='user_management.php' style="align-items: center;">
                        <span class="material-icons">people</span>
                        <span><?php echo translate('nav_user_management'); ?></span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'monitoring.php') ? 'active' : ''; ?>'>
                    <a href='monitoring.php' style="align-items: center;">
                        <span class="material-icons">monitoring</span>
                        <span><?php echo translate('nav_vpn_monitoring'); ?></span>
                    </a>
                </li>
                <?php if (empty($_SESSION['is_reseller'])): ?>
                <li class='<?php echo ($current_page == 'upload_profiles.php') ? 'active' : ''; ?>'>
                    <a href='upload_profiles.php' style="align-items: center;">
                        <span class="material-icons">cloud_upload</span>
                        <span><?php echo translate('nav_upload_profiles'); ?></span>
                    </a>
                </li>
                <?php endif; ?>
                <li class='<?php echo ($current_page == 'profiles.php') ? 'active' : ''; ?>'>
                    <a href='profiles.php' style="align-items: center;">
                        <span class="material-icons">manage_accounts</span>
                        <span><?php echo translate('nav_manage_profiles'); ?></span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'promo_manager.php') ? 'active' : ''; ?>'>
                    <a href='promo_manager.php' style="align-items: center;">
                        <span class="material-icons">card_giftcard</span>
                        <span><?php echo translate('nav_promo_manager'); ?></span>
                    </a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class='<?php echo ($current_page == 'admin_management.php') ? 'active' : ''; ?>'>
                    <a href='admin_management.php' style="align-items: center;">
                        <span class="material-icons">admin_panel_settings</span>
                        <span>Admin Management</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'reseller_management.php') ? 'active' : ''; ?>'>
                    <a href='reseller_management.php' style="align-items: center;">
                        <span class="material-icons">groups</span>
                        <span>Reseller Management</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'accounts.php') ? 'active' : ''; ?>'>
                    <a href='accounts.php' style="align-items: center;">
                        <span class="material-icons">account_balance_wallet</span>
                        <span>Accounts Management</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class='<?php echo ($current_page == 'app_management.php') ? 'active' : ''; ?>'>
                    <a href='app_management.php' style="align-items: center;">
                        <span class="material-icons">system_update</span>
                        <span>App Management</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'tutorial.php') ? 'active' : ''; ?>'>
                    <a href='tutorial.php' style="align-items: center;">
                        <span class="material-icons">school</span>
                        <span>Tutorial</span>
                    </a>
                </li>
                <li class='<?php echo ($current_page == 'settings.php' || $current_page == 'ads_settings.php') ? 'active' : ''; ?>'>
                    <a href='settings.php' style="align-items: center;">
                        <span class="material-icons">settings</span>
                        <span><?php echo translate('nav_settings'); ?></span>
                    </a>
                </li>
                <li>
                    <a href='logout.php' style="align-items: center; color: rgba(255, 255, 255, 0.8);">
                        <span class="material-icons">logout</span>
                        <span><?php echo translate('nav_logout'); ?></span>
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