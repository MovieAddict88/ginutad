<?php
// Start session
session_start();

// Check if the user is logged in and is admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Define variables and initialize with empty values
$username = $password = $login_code = $first_name = $last_name = $contact_number = $address = '';
$credits = 0;
$username_err = $password_err = $login_code_err = $first_name_err = $last_name_err = $contact_number_err = $address_err = '';

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

    // Validate first name
    if (empty(trim($_POST['first_name']))) {
        $first_name_err = 'Please enter a first name.';
    } else {
        $first_name = trim($_POST['first_name']);
    }

    // Validate last name
    if (empty(trim($_POST['last_name']))) {
        $last_name_err = 'Please enter a last name.';
    } else {
        $last_name = trim($_POST['last_name']);
    }


    // Validate contact number
    if (empty(trim($_POST['contact_number']))) {
        $contact_number_err = 'Please enter a contact number.';
    } else {
        $contact_number = trim($_POST['contact_number']);
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($first_name_err) && empty($last_name_err) && empty($contact_number_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO users (username, password, first_name, last_name, contact_number, address, credits, login_code, role, is_reseller, daily_limit, promo_id, billing_month) VALUES (:username, :password, :first_name, :last_name, :contact_number, :address, :credits, :login_code, :role, :is_reseller, :daily_limit, :promo_id, :billing_month)';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
            $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':address', $param_address, PDO::PARAM_STR);
            $stmt->bindParam(':credits', $param_credits, PDO::PARAM_STR);
            $stmt->bindParam(':login_code', $param_login_code, PDO::PARAM_STR);
            $stmt->bindParam(':role', $param_role, PDO::PARAM_STR);
            $stmt->bindParam(':is_reseller', $param_is_reseller, PDO::PARAM_BOOL);
            $stmt->bindParam(':daily_limit', $param_daily_limit, PDO::PARAM_INT);
            $stmt->bindParam(':promo_id', $param_promo_id, PDO::PARAM_INT);
            $stmt->bindParam(':billing_month', $param_billing_month, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_contact_number = $contact_number;
            $param_address = ''; // Not applicable for user
            $param_credits = 0; // Not applicable for user
            $param_login_code = generate_unique_login_code($pdo);
            $param_role = 'user'; // Hardcoded role
            $param_is_reseller = false; // Not a reseller
            $param_daily_limit = convert_to_bytes($_POST['limit_value'], $_POST['limit_unit']);
            $param_promo_id = $_POST['promo_id'];
            $param_billing_month = $_POST['billing_month'];

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to user management page
                header('location: user_management.php');
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}

include 'header.php';
?>

<div class="page-header">
    <h2>Add User</h2>
    <div class="page-actions">
        <a class='btn btn-secondary' href='user_management.php'>
            <span class="material-icons">arrow_back</span>
            Back to Users
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>User Information</h3>
    </div>
    <div class="card-body">
        <div class="form-container">
            <p>Please fill this form to create a new VPN user.</p>
            <form action='add_user.php' method='post'>
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
                    <label class="form-label">First Name</label>
                    <input type='text' name='first_name' class='form-control' value='<?php echo htmlspecialchars($first_name); ?>'>
                    <span class='text-danger'><?php echo $first_name_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Last Name</label>
                    <input type='text' name='last_name' class='form-control' value='<?php echo htmlspecialchars($last_name); ?>'>
                    <span class='text-danger'><?php echo $last_name_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Contact Number</label>
                    <input type='text' name='contact_number' class='form-control' value='<?php echo htmlspecialchars($contact_number); ?>'>
                    <span class='text-danger'><?php echo $contact_number_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Daily Limit</label>
                    <div class="input-group">
                        <input type="number" name="limit_value" class="form-control" placeholder="Enter limit">
                        <select name="limit_unit" class="form-control">
                            <option value="KB">KB</option>
                            <option value="MB">MB</option>
                            <option value="GB">GB</option>
                        </select>
                    </div>
                </div>
                <div class='form-group'>
                    <label class="form-label">Billing Date</label>
                    <input type='date' name='billing_month' class='form-control'>
                </div>
                <div class='form-group'>
                    <input type='submit' class='btn btn-primary' value='Create User'>
                    <a class='btn btn-link' href='user_management.php'>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>