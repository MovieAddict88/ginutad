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

$upload_message = '';
$error_message = '';

// Check if a file was uploaded and a name was provided
if (isset($_FILES['profiles_ovpn']) && isset($_POST['profile_type'])) {
    $profile_type = trim($_POST['profile_type']);
    $promo_id = !empty($_POST['promo_id']) ? intval($_POST['promo_id']) : null;
    $icon_path = trim($_POST['icon_path']);

    // Validate icon_path
    $icons = glob('assets/*.png');
    if (!in_array($icon_path, $icons)) {
        $error_message = 'Invalid icon selected.';
    }

    // Validate promo_id
    $promo_ids = isset($_POST['promo_ids']) ? $_POST['promo_ids'] : [];

    if(empty($error_message)) {
        $files = $_FILES['profiles_ovpn'];
        $file_count = count($files['name']);
        $upload_count = 0;

        for ($i = 0; $i < $file_count; $i++) {
            $file_name = $files['name'][$i];
            $file_tmp_name = $files['tmp_name'][$i];
            $file_size = $files['size'][$i];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext == 'ovpn' && $file_size <= 1000000) {
                $profile_name = pathinfo($file_name, PATHINFO_FILENAME);
                $ovpn_config = file_get_contents($file_tmp_name);

                $sql = 'INSERT INTO vpn_profiles (profile_name, ovpn_config, type, icon_path) VALUES (:profile_name, :ovpn_config, :type, :icon_path)';
                if ($stmt = $pdo->prepare($sql)) {
                    $stmt->bindParam(':profile_name', $profile_name, PDO::PARAM_STR);
                    $stmt->bindParam(':ovpn_config', $ovpn_config, PDO::PARAM_STR);
                    $stmt->bindParam(':type', $profile_type, PDO::PARAM_STR);
                    $stmt->bindParam(':icon_path', $icon_path, PDO::PARAM_STR);
                    if ($stmt->execute()) {
                        $profile_id = $pdo->lastInsertId();
                        if (!empty($promo_ids) && is_array($promo_ids)) {
                            $sql_insert_promo = 'INSERT INTO profile_promos (profile_id, promo_id) VALUES (:profile_id, :promo_id)';
                            $stmt_insert_promo = $pdo->prepare($sql_insert_promo);

                            foreach ($promo_ids as $promo_id) {
                                $stmt_insert_promo->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
                                $stmt_insert_promo->bindParam(':promo_id', $promo_id, PDO::PARAM_INT);
                                $stmt_insert_promo->execute();
                            }
                        }
                        $upload_count++;
                    }
                }
            }
        }
    }
    if ($upload_count > 0) {
        $upload_message = $upload_count . ' of ' . $file_count . ' profiles uploaded successfully.';
    } else {
        $error_message = 'No profiles were uploaded. Please check the file types and sizes.';
    }
}

include 'header.php';
?>

<div class="page-header">
    <h1>Bulk Upload .ovpn Profiles</h1>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Bulk Upload .ovpn Profiles</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($upload_message)): ?>
            <div class="alert alert-success"><?php echo $upload_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="upload_profiles.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_type">Profile Type:</label>
                <select name="profile_type" id="profile_type" class="form-control">
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
                <label>Promos</label>
                <div class="checkbox-group">
                    <?php
                    $sql_promos = 'SELECT id, promo_name FROM promos';
                    $promos = $pdo->query($sql_promos)->fetchAll();
                    foreach ($promos as $promo) {
                        echo '<div class="form-check">';
                        echo '<input class="form-check-input" type="checkbox" name="promo_ids[]" value="' . $promo['id'] . '" id="promo_' . $promo['id'] . '">';
                        echo '<label class="form-check-label" for="promo_' . $promo['id'] . '">' . htmlspecialchars($promo['promo_name']) . '</label>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label for="profiles_ovpn">Select .ovpn files to upload:</label>
                <input type="file" name="profiles_ovpn[]" id="profiles_ovpn" class="form-control" multiple required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
