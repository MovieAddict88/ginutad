<?php
session_start();
require_once 'db_config.php';
require_once 'auth.php';

// Run the migration to create the admob_ads table if it doesn't exist
require_once 'migrations/20240802_create_admob_ads_table.php';

// Check if the user is an admin
if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_message = '';
$error_message = '';

// Handle different actions
switch ($action) {
    case 'add':
    case 'edit':
        // Handle form submission for add/edit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $ad_unit_id = trim($_POST['ad_unit_id']);
            $ad_type = $_POST['ad_type'];
            $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;
            $description = trim($_POST['description']);
            
            // Validation
            if (empty($name) || empty($ad_unit_id)) {
                $error_message = 'Ad name and Ad Unit ID are required.';
            } else {
                if ($action === 'add') {
                    $stmt = $pdo->prepare('INSERT INTO admob_ads (name, ad_unit_id, ad_type, is_enabled, description) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $ad_unit_id, $ad_type, $is_enabled, $description]);
                    $success_message = 'Ad unit added successfully!';
                } else {
                    $stmt = $pdo->prepare('UPDATE admob_ads SET name = ?, ad_unit_id = ?, ad_type = ?, is_enabled = ?, description = ? WHERE id = ?');
                    $stmt->execute([$name, $ad_unit_id, $ad_type, $is_enabled, $description, $id]);
                    $success_message = 'Ad unit updated successfully!';
                }
                
                // Redirect to list view after success
                if (!empty($success_message)) {
                    header('Location: ads_settings.php?success=' . urlencode($success_message));
                    exit;
                }
            }
        }
        
        // Fetch ad unit for editing
        $ad = null;
        if ($action === 'edit' && $id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM admob_ads WHERE id = ?');
            $stmt->execute([$id]);
            $ad = $stmt->fetch();
            
            if (!$ad) {
                $error_message = 'Ad unit not found.';
                $action = 'list';
            }
        }
        break;
        
    case 'delete':
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM admob_ads WHERE id = ?');
            $stmt->execute([$id]);
            $success_message = 'Ad unit deleted successfully!';
            header('Location: ads_settings.php?success=' . urlencode($success_message));
            exit;
        }
        break;
        
    case 'bulk-update':
        // Handle bulk update from list view
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ads'])) {
            foreach ($_POST['ads'] as $ad) {
                $stmt = $pdo->prepare('UPDATE admob_ads SET ad_unit_id = :ad_unit_id, is_enabled = :is_enabled WHERE id = :id');
                $stmt->execute([
                    'ad_unit_id' => $ad['ad_unit_id'],
                    'is_enabled' => isset($ad['is_enabled']) ? 1 : 0,
                    'id' => $ad['id']
                ]);
            }
            $success_message = 'AdMob settings updated successfully!';
            header('Location: ads_settings.php?success=' . urlencode($success_message));
            exit;
        }
        break;
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Fetch all ad units from the database for list view
$stmt = $pdo->query('SELECT * FROM admob_ads ORDER BY id ASC');
$ads = $stmt->fetchAll();

$page_title = 'AdMob Ads Management';
include 'header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Form -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $action === 'add' ? 'Add New Ad Unit' : 'Edit Ad Unit'; ?></h4>
                        <a href="ads_settings.php" class="btn btn-light btn-sm">Back to List</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" id="adForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Ad Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($ad) ? htmlspecialchars($ad['name']) : ''; ?>" 
                                           required>
                                    <div class="form-text">A descriptive name for this ad unit</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="ad_type" class="form-label">Ad Type</label>
                                    <select class="form-select" id="ad_type" name="ad_type">
                                        <option value="banner" <?php echo (isset($ad) && $ad['ad_type'] == 'banner') ? 'selected' : ''; ?>>Banner</option>
                                        <option value="interstitial" <?php echo (isset($ad) && $ad['ad_type'] == 'interstitial') ? 'selected' : ''; ?>>Interstitial</option>
                                        <option value="rewarded" <?php echo (isset($ad) && $ad['ad_type'] == 'rewarded') ? 'selected' : ''; ?>>Rewarded Video</option>
                                        <option value="native" <?php echo (isset($ad) && $ad['ad_type'] == 'native') ? 'selected' : ''; ?>>Native</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ad_unit_id" class="form-label">Ad Unit ID *</label>
                                <input type="text" class="form-control" id="ad_unit_id" name="ad_unit_id" 
                                       value="<?php echo isset($ad) ? htmlspecialchars($ad['ad_unit_id']) : ''; ?>" 
                                       required>
                                <div class="form-text">The AdMob Ad Unit ID (e.g., ca-app-pub-3940256099942544/6300978111)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($ad) ? htmlspecialchars($ad['description']) : ''; ?></textarea>
                                <div class="form-text">Optional description about this ad unit's placement or purpose</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_enabled" name="is_enabled" value="1" 
                                       <?php echo (isset($ad) && $ad['is_enabled']) || !isset($ad) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_enabled">Enable this ad unit</label>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="ads_settings.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $action === 'add' ? 'Add Ad Unit' : 'Update Ad Unit'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- List View -->
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold">AdMob Ads Management</h2>
            <a href="ads_settings.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Ad Unit
            </a>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ad Units (<?php echo count($ads); ?>)</h5>
                <span class="badge bg-info">Bulk Edit Mode</span>
            </div>
            
            <?php if (empty($ads)): ?>
                <div class="card-body text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="fas fa-ad fa-3x mb-3"></i>
                        <h4>No Ad Units Found</h4>
                        <p>Get started by adding your first AdMob ad unit.</p>
                    </div>
                    <a href="ads_settings.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your First Ad Unit
                    </a>
                </div>
            <?php else: ?>
                <form method="post" action="ads_settings.php?action=bulk-update">
                    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover mb-0" style="min-width: 900px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th width="200">Ad Name</th>
                                    <th width="120">Type</th>
                                    <th width="300">Ad Unit ID</th>
                                    <th width="100">Status</th>
                                    <th width="180" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ads as $index => $ad): ?>
                                    <tr>
                                        <td class="fw-bold align-middle"><?php echo $index + 1; ?></td>
                                        <td class="align-middle">
                                            <div class="fw-medium text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($ad['name']); ?>">
                                                <?php echo htmlspecialchars($ad['name']); ?>
                                            </div>
                                            <?php if (!empty($ad['description'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($ad['description']); ?>">
                                                    <?php echo htmlspecialchars($ad['description']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($ad['ad_type']); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <input type="hidden" name="ads[<?php echo $ad['id']; ?>][id]" value="<?php echo $ad['id']; ?>">
                                            <input type="text" name="ads[<?php echo $ad['id']; ?>][ad_unit_id]" 
                                                   value="<?php echo htmlspecialchars($ad['ad_unit_id']); ?>" 
                                                   class="form-control form-control-sm" style="min-width: 280px;">
                                        </td>
                                        <td class="align-middle">
                                            <div class="form-check form-switch d-inline-block">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="ads[<?php echo $ad['id']; ?>][is_enabled]" 
                                                       value="1" <?php echo $ad['is_enabled'] ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="btn-group btn-group-sm" role="group" style="white-space: nowrap;">
                                                <a href="ads_settings.php?action=edit&id=<?php echo $ad['id']; ?>" 
                                                   class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <a href="ads_settings.php?action=delete&id=<?php echo $ad['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this ad unit?');"
                                                   title="Delete">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i> Update Ad Unit IDs directly in the table or use Edit for full details
                        </div>
                        <div class="d-flex gap-2">
                            <a href="ads_settings.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Save All Changes
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Ad Types Legend -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Ad Types Reference</h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <span class="badge bg-secondary me-1">banner</span> <small>Rectangular ads</small>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <span class="badge bg-secondary me-1">interstitial</span> <small>Full-screen ads</small>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <span class="badge bg-secondary me-1">rewarded</span> <small>Video rewards</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-secondary me-1">native</span> <small>Custom native ads</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>

<style>
    /* Ensure buttons are clearly visible */
    .btn-primary {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7 !important;
        border-color: #0a58ca !important;
    }
    
    .btn-danger {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
    }
    
    .btn-danger:hover {
        background-color: #bb2d3b !important;
        border-color: #b02a37 !important;
    }
    
    .btn-success {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: white !important;
    }
    
    .btn-success:hover {
        background-color: #157347 !important;
        border-color: #146c43 !important;
    }
    
    .btn-secondary {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }
    
    .btn-secondary:hover {
        background-color: #5c636a !important;
        border-color: #565e64 !important;
    }
    
    /* Button group styling */
    .btn-group .btn {
        margin: 0 2px !important;
        border-radius: 4px !important;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    
    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.3em;
    }
    
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: 1px solid #e0e0e0;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Action buttons styling */
    .btn-group-sm > .btn {
        min-width: 75px;
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* Mobile-specific adjustments */
    @media (max-width: 768px) {
        .table {
            font-size: 0.875rem;
        }
        
        .btn-group .btn {
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
            min-width: 65px;
        }
        
        .btn-group .btn i {
            margin-right: 3px !important;
        }
        
        .form-control-sm {
            font-size: 0.8rem;
            min-width: 200px !important;
        }
        
        .card-header, .card-footer {
            padding: 0.75rem;
        }
        
        .h4 {
            font-size: 1.25rem;
        }
        
        /* Make table cells more compact on mobile */
        .table td, .table th {
            padding: 0.5rem;
        }
        
        /* Action column width */
        .table th:nth-child(6),
        .table td:nth-child(6) {
            min-width: 160px;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .btn-group .btn {
            width: 100%;
            justify-content: center;
            margin: 2px 0 !important;
        }
        
        .table-responsive {
            margin-left: -10px;
            margin-right: -10px;
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* Make action buttons more visible on small screens */
        .btn-primary, .btn-danger {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    }
    
    /* Ensure good contrast for buttons */
    .btn {
        transition: all 0.2s ease-in-out;
    }
    
    .btn:active {
        transform: scale(0.98);
    }
</style>

<script>
    // Add a visual indicator for horizontal scrolling
    document.addEventListener('DOMContentLoaded', function() {
        const tableWrapper = document.querySelector('.table-responsive');
        if (tableWrapper && tableWrapper.scrollWidth > tableWrapper.clientWidth) {
            // Add a hint for scrolling
            const hint = document.createElement('div');
            hint.className = 'text-center text-muted small py-2';
            hint.innerHTML = '<i class="fas fa-arrows-left-right me-1"></i> Scroll horizontally to see all columns and action buttons';
            tableWrapper.parentNode.insertBefore(hint, tableWrapper.nextSibling);
            
            // Add shadow effect when scrolled
            tableWrapper.addEventListener('scroll', function() {
                if (this.scrollLeft > 0) {
                    this.style.boxShadow = 'inset 5px 0 5px -5px rgba(0,0,0,0.1)';
                } else {
                    this.style.boxShadow = 'none';
                }
            });
        }
        
        // Add hover effects to table rows
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>