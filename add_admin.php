<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Define variables and initialize with empty values
$username = $password = '';
$username_err = $password_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate username
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        // Prepare a select statement
        $sql = 'SELECT id FROM users WHERE username = :username';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = trim($_POST['username']);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = 'This username is already taken.';
                } else {
                    $username = trim($_POST['username']);
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST['password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password must have atleast 6 characters.';
    } else {
        $password = trim($_POST['password']);
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO users (username, password, first_name, last_name, contact_number, role) VALUES (:username, :password, :first_name, :last_name, :contact_number, :role)';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
            $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':role', $param_role, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_first_name = 'Admin';
            $param_last_name = 'User';
            $param_contact_number = '0';
            $param_role = 'admin';

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to admin management page
                header('location: admin_management.php');
                exit;
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }
}

include 'header.php';
?>

<div class="page-header">
    <h2>Add Admin</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Admin Information</h3>
    </div>
    <div class="card-body">
        <form action='add_admin.php' method='post'>
            <div class='form-group'>
                <label class="form-label">Username</label>
                <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>'>
                <span class='text-danger'><?php echo $username_err; ?></span>
            </div>
            <div class='form-group'>
                <label class="form-label">Password</label>
                <input type='password' name='password' class='form-control'>
                <span class='text-danger'><?php echo $password_err; ?></span>
            </div>
            <div class='form-group'>
                <input type='submit' class='btn btn-primary' value='Create Admin'>
                <a class='btn btn-link' href='admin_management.php'>Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
