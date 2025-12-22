<?php
// Start session
session_start();

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_reseller']) || $_SESSION['is_reseller'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Get reseller information
$reseller_id = $_SESSION['reseller_id'];
$stmt = $pdo->prepare("SELECT logo_path, primary_color, secondary_color, commission_rate FROM resellers WHERE id = :reseller_id");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$reseller = $stmt->fetch(PDO::FETCH_ASSOC);
$logo_path = $reseller['logo_path'];
$primary_color = $reseller['primary_color'];
$secondary_color = $reseller['secondary_color'];
$commission_rate = $reseller['commission_rate'];


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];
    $commission_rate = $_POST['commission_rate'] / 100;

    // Handle file upload securely
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/logos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_exts)) {
            $logo_path = $upload_dir . uniqid('', true) . '.' . $file_ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path);
        }
    }

    // Update reseller branding settings
    $sql = 'UPDATE resellers SET logo_path = ?, primary_color = ?, secondary_color = ?, commission_rate = ? WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$logo_path, $primary_color, $secondary_color, $commission_rate, $reseller_id]);

    // Redirect to branding page
    header('location: reseller_branding.php');
    exit;
}


include 'header.php';
?>

<div class="page-header">
    <h2>White-Label Branding</h2>
    <div class="page-actions">
        <a href="reseller_dashboard.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Customize Your Branding</h3>
    </div>
    <div class="card-body">
        <form action="reseller_branding.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" name="logo" id="logo" class="form-control">
                <?php if ($logo_path): ?>
                    <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Your Logo" style="max-width: 200px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="primary_color">Primary Color</label>
                <input type="color" name="primary_color" id="primary_color" class="form-control" value="<?php echo htmlspecialchars($primary_color); ?>">
            </div>
            <div class="form-group">
                <label for="secondary_color">Secondary Color</label>
                <input type="color" name="secondary_color" id="secondary_color" class="form-control" value="<?php echo htmlspecialchars($secondary_color); ?>">
            </div>
            <div class="form-group">
                <label for="commission_rate">Commission Rate (%)</label>
                <input type="number" name="commission_rate" id="commission_rate" class="form-control" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($commission_rate * 100); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Branding</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
