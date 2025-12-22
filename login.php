<?php
// Start session
session_start();

// Check if the user is already logged in, if yes then redirect to the welcome page
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('location: index.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';
load_language($pdo);

$site_name = get_setting($pdo, 'site_name');
$site_icon = get_setting($pdo, 'site_icon');

// Define variables and initialize with empty values
$username = $password = '';
$username_err = $password_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if username is empty
    if (empty(trim($_POST['username']))) {
        $username_err = translate('please_enter_username');
    } else {
        $username = trim($_POST['username']);
    }

    // Check if password is empty
    if (empty(trim($_POST['password']))) {
        $password_err = translate('please_enter_your_password');
    } else {
        $password = trim($_POST['password']);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement - Only allow admin users to login
        $sql = 'SELECT id, username, password, role, is_reseller FROM users WHERE username = :username';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists and is admin
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $hashed_password = $row['password'];
                        $role = $row['role'];
                        
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id'] = $id;
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $row['role'];
                            $_SESSION['is_reseller'] = $row['is_reseller'];

                            // Redirect user to welcome page
                            header('location: index.php');
                        } else {
                            // Display an error message if password is not valid
                            $password_err = translate('invalid_password');
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist or is not admin
                    $username_err = translate('no_admin_account_found');
                }
            } else {
                echo translate('oops_something_went_wrong');
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title><?php echo translate('login_title'); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    <?php if ($site_icon): ?>
        <link rel='icon' href='<?php echo htmlspecialchars($site_icon); ?>?v=<?php echo time(); ?>' type='image/x-icon'>
    <?php endif; ?>
    <link rel='stylesheet' href='style.css'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="login-page">
    <div class='login-container'>
        <div style="text-align: center; margin-bottom: 30px;">
            <?php if ($site_icon): ?>
                <img src="<?php echo htmlspecialchars($site_icon); ?>?v=<?php echo time(); ?>" alt="Site Icon" style="width: 64px; height: 64px; margin-bottom: 10px; border-radius: 50%; border: 3px solid yellow; padding: 4px; box-sizing: border-box;">
            <?php else: ?>
                <span class="material-icons" style="font-size: 3rem; color: #4361ee; margin-bottom: 10px;">vpn_lock</span>
            <?php endif; ?>
            <h2 style="color: #4361ee;"><?php echo htmlspecialchars($site_name); ?></h2>
        </div>
        <p style="text-align: center; margin-bottom: 30px; color: #6c757d;"><?php echo translate('please_fill_credentials'); ?></p>
        <form action='login.php' method='post'>
            <div class='form-group'>
                <label class="form-label"><?php echo translate('username'); ?></label>
                <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>'>
                <span class='text-danger'><?php echo $username_err; ?></span>
            </div>
            <div class='form-group'>
                <label class="form-label"><?php echo translate('password'); ?></label>
                <input type='password' name='password' class='form-control'>
                <span class='text-danger'><?php echo $password_err; ?></span>
            </div>
            <div class='form-group'>
                <input type='submit' class='btn btn-primary' value='<?php echo translate('login'); ?>' style='width: 100%; padding: 14px;'>
            </div>
        </form>
    </div>
</body>
</html>