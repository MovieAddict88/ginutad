<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

$profile_name = $profile_content = '';
$profile_name_err = $profile_content_err = '';
$profile_id = null;

// Get the profile ID from the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $profile_id = $_GET['id'];
} else {
    header('location: profiles.php');
    exit;
}

// Fetch the profile from the database
$stmt = $pdo->prepare('SELECT name, ovpn_config, type, icon_path, promo_id, management_ip, management_port FROM vpn_profiles WHERE id = :id');
$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
$stmt->execute();
$profile = $stmt->fetch();

if ($profile) {
    $profile_name = $profile['name'];
    $profile_content = $profile['ovpn_config'];
    $profile_type = $profile['type'];
    $profile_icon = $profile['icon_path'];
    $management_ip = $profile['management_ip'];
    $management_port = $profile['management_port'];
} else {
    header('location: profiles.php');
    exit;
}

// Process form data when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate profile name
    if (empty(trim($_POST['profile_name']))) {
        $profile_name_err = 'Please enter a profile name.';
    } else {
        $profile_name = trim($_POST['profile_name']);
    }

    // Validate profile content
    if (empty(trim($_POST['profile_content']))) {
        $profile_content_err = 'Please enter the profile content.';
    } else {
        $profile_content = trim($_POST['profile_content']);
    }
    // Check for errors before updating the database
    if (empty($profile_name_err) && empty($profile_content_err)) {
        $sql = 'UPDATE vpn_profiles SET name = :profile_name, ovpn_config = :profile_content, type = :profile_type, icon_path = :icon_path, promo_id = :promo_id, management_ip = :management_ip, management_port = :management_port WHERE id = :id';

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':profile_name', $profile_name, PDO::PARAM_STR);
            $stmt->bindParam(':profile_content', $profile_content, PDO::PARAM_STR);
            $stmt->bindParam(':profile_type', $_POST['profile_type'], PDO::PARAM_STR);
            $stmt->bindParam(':icon_path', $_POST['icon_path'], PDO::PARAM_STR);
            $stmt->bindParam(':promo_id', $_POST['promo_id'], PDO::PARAM_INT);
            $stmt->bindParam(':management_ip', $_POST['management_ip'], PDO::PARAM_STR);
            $stmt->bindParam(':management_port', $_POST['management_port'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('location: profiles.php');
                exit;
            } else {
                echo 'Something went wrong. Please try again later.';
            }
        }
    }
}

include 'header.php';
?>

<div class="page-header">
    <h1>Edit Profile</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Edit VPN Profile</h3>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
            <?php if (empty($_SESSION['is_reseller'])): ?>
            <div class="form-group <?php echo (!empty($profile_name_err)) ? 'has-error' : ''; ?>">
                <label>Profile Name</label>
                <input type="text" name="profile_name" class="form-control" value="<?php echo htmlspecialchars($profile_name); ?>">
                <span class="help-block"><?php echo $profile_name_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($profile_content_err)) ? 'has-error' : ''; ?>">
                <label>Profile Content</label>
                <textarea name="profile_content" class="form-control" rows="10"><?php echo htmlspecialchars($profile_content); ?></textarea>
                <span class="help-block"><?php echo $profile_content_err; ?></span>
            </div>
            <?php else: ?>
                <input type="hidden" name="profile_name" value="<?php echo htmlspecialchars($profile_name); ?>">
                <input type="hidden" name="profile_content" value="<?php echo htmlspecialchars($profile_content); ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Profile Type</label>
                <select name="profile_type" class="form-control">
                    <option value="Premium" <?php echo ($profile_type == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                    <option value="Freemium" <?php echo ($profile_type == 'Freemium') ? 'selected' : ''; ?>>Freemium</option>
                </select>
            </div>
            <div class="form-group">
                <label>Management IP</label>
                <input type="text" name="management_ip" class="form-control" value="<?php echo htmlspecialchars($management_ip); ?>" placeholder="e.g., 127.0.0.1">
            </div>
            <div class="form-group">
                <label>Management Port</label>
                <input type="number" name="management_port" class="form-control" value="<?php echo htmlspecialchars($management_port); ?>" placeholder="e.g., 7505">
            </div>
            <div class="form-group">
                <label>Icon</label>
                <select name="icon_path" class="form-control">
                    <?php
                    $icons = glob('assets/*.png');
                    foreach ($icons as $icon) {
                        $selected = ($icon == $profile_icon) ? 'selected' : '';
                        echo '<option value="' . $icon . '" ' . $selected . '>' . basename($icon) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Promo</label>
                <select name="promo_id" class="form-control">
                    <option value="">Select Promo</option>
                    <?php
                    $sql = 'SELECT id, promo_name FROM promos';
                    $promos = $pdo->query($sql)->fetchAll();
                    foreach ($promos as $promo) {
                        $selected = ($promo['id'] == $profile['promo_id']) ? 'selected' : '';
                        echo "<option value='" . $promo['id'] . "' " . $selected . ">" . htmlspecialchars($promo['promo_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a href="profiles.php" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
