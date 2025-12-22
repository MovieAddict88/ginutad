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
$username = $login_code = $device_id = $first_name = $last_name = $contact_number = '';
$username_err = $login_code_err = $device_id_err = $first_name_err = $last_name_err = $contact_number_err = '';

// Processing form data when form is submitted
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];

    // Validate username
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        $username = trim($_POST['username']);
    }

    // Validate login code
    if (empty(trim($_POST['login_code']))) {
        $login_code_err = 'Please enter a login code.';
    } else {
        $login_code = trim($_POST['login_code']);
    }

    // Validate device ID
    $device_id = trim($_POST['device_id']);

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

    // Check input errors before updating in database
    if (empty($username_err) && empty($login_code_err) && empty($first_name_err) && empty($last_name_err) && empty($contact_number_err)) {
        // Prepare an update statement
        $sql = 'UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, contact_number = :contact_number, login_code = :login_code, device_id = :device_id, role = :role, daily_limit = :daily_limit, billing_month = :billing_month WHERE id = :id';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
            $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':login_code', $param_login_code, PDO::PARAM_STR);
            $stmt->bindParam(':device_id', $param_device_id, PDO::PARAM_STR);
            $stmt->bindParam(':role', $param_role, PDO::PARAM_STR);
            $stmt->bindParam(':daily_limit', $param_daily_limit, PDO::PARAM_INT);
            $stmt->bindParam(':billing_month', $param_billing_month, PDO::PARAM_STR);
            $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

            // Set parameters
            $param_username = $username;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_contact_number = $contact_number;
            $param_login_code = $login_code;
            $param_device_id = $device_id;
            $param_id = $id;
            $param_role = $_POST['role'];
            $param_daily_limit = convert_to_bytes($_POST['limit_value'], $_POST['limit_unit']);
            $param_billing_month = $_POST['billing_month'];

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records updated successfully. Redirect to landing page
                header('location: index.php');
                exit();
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
} else {
    // Check existence of id parameter before processing further
    if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
        // Get URL parameter
        $id =  trim($_GET['id']);

        // Prepare a select statement
        $sql = 'SELECT * FROM users WHERE id = :id';
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

            // Set parameters
            $param_id = $id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Retrieve individual field value
                    $username = $row['username'];
                    $first_name = $row['first_name'];
                    $last_name = $row['last_name'];
                    $contact_number = $row['contact_number'];
                    $login_code = $row['login_code'];
                    $device_id = $row['device_id'];
                    $role = $row['role'];
                    $daily_limit = $row['daily_limit'];
                    $billing_month = $row['billing_month'];

                    if ($daily_limit > 0) {
                        if ($daily_limit >= 1073741824) {
                            $limit_value = $daily_limit / 1073741824;
                            $limit_unit = 'GB';
                        } elseif ($daily_limit >= 1048576) {
                            $limit_value = $daily_limit / 1048576;
                            $limit_unit = 'MB';
                        } else {
                            $limit_value = $daily_limit / 1024;
                            $limit_unit = 'KB';
                        }
                    } else {
                        $limit_value = 0;
                        $limit_unit = 'MB';
                    }
                } else {
                    // URL doesn't contain valid id. Redirect to error page
                    header('location: index.php');
                    exit();
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }

    } else {
        // URL doesn't contain id parameter. Redirect to error page
        header('location: index.php');
        exit();
    }
}

// Check if the user is the admin user
if ($username === 'admin') {
    header('location: index.php?message=Super admin user cannot be edited.');
    exit();
}

include 'header.php';
?>

<div class="page-header">
    <h2>Edit User</h2>
    <div class="page-actions">
        <a class='btn btn-secondary' href='index.php'>
            <span class="material-icons">arrow_back</span>
            Back to Users
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Update User Information</h3>
    </div>
    <div class="card-body">
        <div class="form-container">
            <p>Please edit the input values and submit to update the user record.</p>
            <form action='edit_user.php' method='post'>
                <div class='form-group'>
                    <label class="form-label">Username</label>
                    <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>'>
                    <span class='text-danger'><?php echo $username_err; ?></span>
                </div>
                <?php if ($role !== 'admin') : ?>
                <div class='form-group'>
                    <label class="form-label">Login Code</label>
                    <input type='text' name='login_code' class='form-control' value='<?php echo htmlspecialchars($login_code); ?>'>
                    <span class='text-danger'><?php echo $login_code_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Device ID</label>
                    <input type='text' name='device_id' class='form-control' value='<?php echo htmlspecialchars($device_id); ?>'>
                    <span class='text-danger'><?php echo $device_id_err; ?></span>
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
                        <input type="number" name="limit_value" class="form-control" placeholder="Enter limit" value="<?php echo $limit_value; ?>">
                        <select name="limit_unit" class="form-control">
                            <option value="KB" <?php if ($limit_unit == 'KB') echo 'selected'; ?>>KB</option>
                            <option value="MB" <?php if ($limit_unit == 'MB') echo 'selected'; ?>>MB</option>
                            <option value="GB" <?php if ($limit_unit == 'GB') echo 'selected'; ?>>GB</option>
                        </select>
                    </div>
                </div>
                <div class='form-group'>
                    <label class="form-label">Billing Date</label>
                    <input type='date' name='billing_month' class='form-control' value='<?php echo htmlspecialchars($billing_month); ?>'>
                </div>
                <?php else: ?>
                    <input type='hidden' name='login_code' value='<?php echo htmlspecialchars($login_code); ?>'/>
                    <input type='hidden' name='device_id' value='<?php echo htmlspecialchars($device_id); ?>'/>
                    <input type='hidden' name='first_name' value='<?php echo htmlspecialchars($first_name); ?>'/>
                    <input type='hidden' name='last_name' value='<?php echo htmlspecialchars($last_name); ?>'/>
                    <input type='hidden' name='contact_number' value='<?php echo htmlspecialchars($contact_number); ?>'/>
                    <input type='hidden' name='limit_value' value='<?php echo $limit_value; ?>'/>
                    <input type='hidden' name='limit_unit' value='<?php echo $limit_unit; ?>'/>
                    <input type='hidden' name='billing_month' value='<?php echo htmlspecialchars($billing_month); ?>'/>
                <?php endif; ?>
                <input type='hidden' name='id' value='<?php echo $id; ?>'/>
                <input type='hidden' name='role' value='<?php echo htmlspecialchars($role); ?>'/>
                <div class='form-group'>
                    <input type='submit' class='btn btn-primary' value='Update User'>
                    <a class='btn btn-link' href='index.php'>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>