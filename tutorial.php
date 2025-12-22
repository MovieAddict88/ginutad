<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
include 'header.php';

// Fetch active promos grouped by carrier
$stmt = $pdo->prepare("SELECT * FROM promos WHERE is_active = 1 ORDER BY carrier, promo_name");
$stmt->execute();
$promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$carriers = [];
foreach ($promos as $promo) {
    $carriers[$promo['carrier']][] = $promo;
}

// Fetch troubleshooting guides
$stmt = $pdo->prepare("SELECT * FROM troubleshooting_guides WHERE is_active = 1 ORDER BY category, title");
$stmt->execute();
$guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

$guide_categories = [];
foreach ($guides as $guide) {
    $guide_categories[$guide['category']][] = $guide;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Configuration Tutorial</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        .tutorial-page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .page-header h1 {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-weight: 600;
        }

        /* Card Styling */
        .card-modern {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .card-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header-modern {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            border-bottom: 2px solid var(--primary-color);
            padding: 1.25rem 1.5rem;
        }

        .card-header-modern h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        /* Quick Navigation */
        .quick-nav-section {
            background: linear-gradient(to right, #f8f9fa, #fff);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 1rem;
        }

        .nav-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .nav-card:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        /* Tab System */
        .carrier-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .tab-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.9rem;
            color: #495057;
            cursor: pointer;
            transition: var(--transition);
        }

        .tab-btn:hover {
            background: #e9ecef;
            color: var(--primary-color);
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Configuration Display */
        .config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }

        .config-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-weight: 600;
        }

        .badge-modern {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .badge-free {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            color: white;
        }

        .badge-premium {
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
        }

        .config-container {
            position: relative;
            background: #1e1e1e;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .config-container pre {
            margin: 0;
            padding: 0;
            overflow-x: auto;
        }

        .config-container code {
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .copy-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .copy-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Carrier Sections */
        .carrier-section {
            margin-bottom: 2rem;
        }

        .carrier-section h2 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tutorial-page {
                padding: 10px;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .quick-nav-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
            
            .carrier-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 0.5rem;
            }
            
            .tab-btn {
                white-space: nowrap;
            }
            
            .config-container {
                padding: 1rem;
            }
            
            .config-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        /* Footer */
        .footer-note {
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Material Icons */
        .material-icons {
            vertical-align: middle;
        }

        /* Loading Animation */
        .loading-placeholder {
            display: none;
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="tutorial-page">
        <!-- Page Header -->
        <div class="page-header">
            <h1><span class="material-icons">vpn_lock</span> VPN Configuration Tutorial</h1>
            <p class="mt-2 mb-0" style="opacity: 0.9;">Complete guide for setting up VPN configurations across different carriers</p>
        </div>

        <!-- Important Disclaimer -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h3><span class="material-icons">warning</span> Important Disclaimer</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffecb5; border-radius: 8px; padding: 1rem;">
                    <strong>⚠️ Security Notice:</strong> These configurations are for educational purposes only. Use them exclusively on networks you own or have explicit permission to test. Unauthorized access is strictly prohibited and illegal.
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="quick-nav-section">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                <span class="material-icons">navigation</span> Quick Navigation
            </h3>
            <div class="quick-nav-grid">
                <?php foreach (array_keys($carriers) as $index => $carrier): ?>
                    <a href="#carrier-<?php echo strtolower(str_replace(' ', '-', $carrier)); ?>" 
                       class="nav-card">
                        <span class="material-icons" style="font-size: 2rem; margin-bottom: 8px;">router</span>
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($carrier); ?></span>
                    </a>
                <?php endforeach; ?>
                <a href="#troubleshooting" class="nav-card">
                    <span class="material-icons" style="font-size: 2rem; margin-bottom: 8px;">build_circle</span>
                    <span style="font-weight: 500;">Troubleshooting</span>
                </a>
            </div>
        </div>

        <!-- Carrier Configurations -->
        <div class="carrier-section">
            <h2><span class="material-icons">settings_input_antenna</span> Carrier Configurations</h2>
            
            <?php foreach ($carriers as $carrier => $configs): ?>
                <div id="carrier-<?php echo strtolower(str_replace(' ', '-', $carrier)); ?>" 
                     class="card-modern mb-4">
                    
                    <div class="card-header-modern">
                        <h3>
                            <span class="material-icons">public</span> 
                            <?php echo htmlspecialchars($carrier); ?>
                            <span class="badge badge-modern" style="background: var(--primary-color); color: white; margin-left: 10px;">
                                <?php echo count($configs); ?> configs
                            </span>
                        </h3>
                    </div>
                    
                    <div class="card-body p-3">
                        <!-- Tabs -->
                        <div class="carrier-tabs">
                            <?php foreach ($configs as $index => $config): ?>
                                <button class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-target="config-<?php echo $config['id']; ?>">
                                    <?php echo htmlspecialchars($config['promo_name']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tab Contents -->
                        <div class="tab-contents">
                            <?php foreach ($configs as $index => $config): ?>
                                <div class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     id="config-<?php echo $config['id']; ?>">
                                    
                                    <div class="config-header">
                                        <h4 class="config-title">
                                            <?php echo htmlspecialchars($config['promo_name']); ?>
                                        </h4>
                                        <div>
                                            <span class="badge-modern <?php echo !empty($config['is_free']) ? 'badge-free' : 'badge-premium'; ?>">
                                                <?php echo htmlspecialchars(!empty($config['is_free']) ? 'Free Plan' : 'Premium Plan'); ?>
                                            </span>
                                            <?php if (!empty($config['data_limit'])): ?>
                                                <span class="badge-modern" style="background: #6c757d; color: white; margin-left: 8px;">
                                                    <?php echo htmlspecialchars($config['data_limit']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="config-container">
                                        <pre><code class="language-generic"><?php echo htmlspecialchars($config['config_text']); ?></code></pre>
                                        <button class="copy-btn" data-clipboard-target="#config-<?php echo $config['id']; ?> pre code">
                                            <span class="material-icons">content_copy</span>
                                            <span class="copy-text">Copy Configuration</span>
                                        </button>
                                    </div>
                                    
                                    <?php if (!empty($config['notes'])): ?>
                                        <div class="alert alert-info mt-3" style="background: #e7f1ff; border: 1px solid #c2dbf2; border-radius: 6px; padding: 0.75rem;">
                                            <strong>📝 Note:</strong> <?php echo htmlspecialchars($config['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Troubleshooting Section -->
        <div id="troubleshooting" class="mt-5">
            <h2 style="color: var(--secondary-color); margin-bottom: 1.5rem;">
                <span class="material-icons">help_center</span> Troubleshooting Guides
            </h2>
            
            <?php foreach ($guide_categories as $category => $guides_in_category): ?>
                <div class="card-modern mb-4">
                    <div class="card-header-modern">
                        <h3>
                            <span class="material-icons">category</span> 
                            <?php echo htmlspecialchars($category); ?>
                            <span class="badge badge-modern" style="background: #6c757d; color: white; margin-left: 10px;">
                                <?php echo count($guides_in_category); ?> guides
                            </span>
                        </h3>
                    </div>
                    
                    <div class="card-body p-3">
                        <div class="carrier-tabs">
                            <?php foreach ($guides_in_category as $index => $guide): ?>
                                <button class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-target="guide-content-<?php echo $guide['id']; ?>">
                                    <?php echo htmlspecialchars($guide['title']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="tab-contents">
                            <?php foreach ($guides_in_category as $index => $guide): ?>
                                <div class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     id="guide-content-<?php echo $guide['id']; ?>">
                                    
                                    <h4 class="config-title mb-3">
                                        <?php echo htmlspecialchars($guide['title']); ?>
                                    </h4>
                                    
                                    <div class="config-container">
                                        <pre><code id="guide-<?php echo $guide['id']; ?>"><?php echo htmlspecialchars($guide['content']); ?></code></pre>
                                        <button class="copy-btn" data-clipboard-target="#guide-<?php echo $guide['id']; ?>">
                                            <span class="material-icons">content_copy</span>
                                            <span class="copy-text">Copy Guide</span>
                                        </button>
                                    </div>
                                    
                                    <?php if (!empty($guide['description'])): ?>
                                        <div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid var(--primary-color);">
                                            <?php echo htmlspecialchars($guide['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            <p>
                <span class="material-icons" style="vertical-align: middle; font-size: 1rem;">info</span>
                Last updated: <?php echo date('F j, Y'); ?> | For technical support, contact your system administrator
            </p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Clipboard.js
        var clipboard = new ClipboardJS('.copy-btn');
        
        clipboard.on('success', function(e) {
            var originalHTML = e.trigger.innerHTML;
            e.trigger.innerHTML = '<span class="material-icons">check_circle</span> <span class="copy-text">Copied!</span>';
            e.trigger.style.background = '#28a745';
            
            setTimeout(function() {
                e.trigger.innerHTML = originalHTML;
                e.trigger.style.background = '';
            }, 2000);
            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            console.error('Copy failed:', e);
            e.trigger.innerHTML = '<span class="material-icons">error</span> <span class="copy-text">Failed</span>';
            e.trigger.style.background = '#dc3545';
            
            setTimeout(function() {
                e.trigger.innerHTML = '<span class="material-icons">content_copy</span> <span class="copy-text">Copy</span>';
                e.trigger.style.background = '';
            }, 2000);
        });

        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const targetContent = document.getElementById(targetId);
                
                if (targetContent) {
                    // Get parent card body
                    const parentCardBody = button.closest('.card-body');
                    
                    // Remove active class from all tabs in this card
                    parentCardBody.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    parentCardBody.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab and target content
                    button.classList.add('active');
                    targetContent.classList.add('active');
                }
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>

    <!-- Clipboard.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
</body>
</html>

<?php include 'footer.php'; ?>