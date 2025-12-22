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

// Define variables and initialize with empty values
$username = $password = $first_name = $last_name = $contact_number = '';
$username_err = $password_err = $first_name_err = $last_name_err = $contact_number_err = '';

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
        try {
            $pdo->beginTransaction();

            // Insert user into users table
            $sql = 'INSERT INTO users (username, password, first_name, last_name, contact_number, login_code, daily_limit) VALUES (:username, :password, :first_name, :last_name, :contact_number, :login_code, :daily_limit)';

            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
                $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
                $stmt->bindParam(':login_code', $param_login_code, PDO::PARAM_STR);
                $stmt->bindParam(':daily_limit', $param_daily_limit, PDO::PARAM_INT);

                // Set parameters
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_first_name = $first_name;
                $param_last_name = $last_name;
                $param_contact_number = $contact_number;
                $param_login_code = generate_unique_login_code($pdo);
                $param_daily_limit = convert_to_bytes($_POST['limit_value'], $_POST['limit_unit']);

                // Attempt to execute the prepared statement
                $stmt->execute();
                $client_id = $pdo->lastInsertId();

                // Close statement
                unset($stmt);
            }

            // Insert into reseller_clients table
            $sql = 'INSERT INTO reseller_clients (reseller_id, client_id) VALUES (:reseller_id, :client_id)';

            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);

                // Attempt to execute the prepared statement
                $stmt->execute();

                // Close statement
                unset($stmt);
            }

            $pdo->commit();

            // Redirect to client management page
            header('location: reseller_clients.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("An error occurred: " . $e->getMessage());
        }
    }
}

// Fetch clients for the reseller
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.first_name, u.last_name, u.contact_number, u.daily_limit, u.data_usage
    FROM users u
    JOIN reseller_clients rc ON u.id = rc.client_id
    WHERE rc.reseller_id = :reseller_id
");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="page-header">
    <h2>Client Management</h2>
    <div class="page-actions">
        <a href="reseller_dashboard.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Your Clients</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Daily Limit</th>
                    <th>Data Usage</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['username']); ?></td>
                        <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($client['contact_number']); ?></td>
                        <td><?php echo format_bytes($client['daily_limit']); ?></td>
                        <td><?php echo format_bytes($client['data_usage']); ?></td>
                        <td>
                            <a href="edit_client.php?id=<?php echo $client['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_client.php?id=<?php echo $client['id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>Add New Client</h3>
    </div>
    <div class="card-body">
        <form action="reseller_clients.php" method="post">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                <span class="text-danger"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                <span class="text-danger"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                <span class="text-danger"><?php echo $first_name_err; ?></span>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                <span class="text-danger"><?php echo $last_name_err; ?></span>
            </div>
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($contact_number); ?>" required>
                <span class="text-danger"><?php echo $contact_number_err; ?></span>
            </div>
            <div class="form-group">
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
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Client">
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
