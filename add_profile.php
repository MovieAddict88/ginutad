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

    // Check for errors before inserting into the database
    if (empty($profile_name_err) && empty($profile_content_err)) {
        $sql = 'INSERT INTO vpn_profiles (name, ovpn_config, type, icon_path, promo_id) VALUES (:name, :ovpn_config, :type, :icon_path, :promo_id)';

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':name', $profile_name, PDO::PARAM_STR);
            $stmt->bindParam(':ovpn_config', $profile_content, PDO::PARAM_STR);
            $stmt->bindParam(':type', $_POST['profile_type'], PDO::PARAM_STR);
            $stmt->bindParam(':icon_path', $_POST['icon_path'], PDO::PARAM_STR);
            $stmt->bindParam(':promo_id', $_POST['promo_id'], PDO::PARAM_INT);

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
    <h1>Add Profile</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add a New VPN Profile</h3>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group <?php echo (!empty($profile_name_err)) ? 'has-error' : ''; ?>">
                <label>Profile Name</label>
                <input type="text" name="profile_name" class="form-control" value="<?php echo $profile_name; ?>">
                <span class="help-block"><?php echo $profile_name_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($profile_content_err)) ? 'has-error' : ''; ?>">
                <label>Profile Content</label>
                <textarea name="profile_content" class="form-control" rows="10"><?php echo $profile_content; ?></textarea>
                <span class="help-block"><?php echo $profile_content_err; ?></span>
            </div>
            <div class="form-group">
                <label>Profile Type</label>
                <select name="profile_type" class="form-control">
                    <option value="Premium">Premium</option>
                    <option value="Freemium">Freemium</option>
                </select>
            </div>
            <div class="form-group">
                <label>Icon</label>
                <select name="icon_path" class="form-control">
                    <?php
                    $icons = glob('assets/*.png');
                    foreach ($icons as $icon) {
                        echo '<option value="' . $icon . '">' . basename($icon) . '</option>';
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
                        echo "<option value='" . $promo['id'] . "'>" . htmlspecialchars($promo['promo_name']) . "</option>";
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
