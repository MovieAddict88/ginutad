<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$password_err = '';
$password = '';
$confirm_password = '';
$confirm_password_err = '';

$user_id = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST['id'] : $_GET['id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    if (empty(trim($_POST['new_password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['new_password'])) < 6) {
        $password_err = 'Password must have at least 6 characters.';
    } else {
        $password = trim($_POST['new_password']);
    }

    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = 'Please confirm the password.';
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = 'Passwords do not match.';
        }
    }

    if (empty($password_err) && empty($confirm_password_err)) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
            $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_id = $_POST['id'];

            if ($stmt->execute()) {
                header('location: admin_management.php');
                exit();
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            unset($stmt);
        }
    }

}

include 'header.php';
?>

<div class="page-header">
    <h2>Change Password</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Change Password</h3>
    </div>
    <div class="card-body">
        <form action="change_password.php" method="post">
            <input type="hidden" name="id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link" href="admin_management.php">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
