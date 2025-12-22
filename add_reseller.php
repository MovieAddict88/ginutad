<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';
require_once 'utils.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Define variables and initialize with empty values
$username = $password = $first_name = $address = $contact_number = '';
$credits = 0;
$username_err = $password_err = $first_name_err = $address_err = $contact_number_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate username, password, first_name, address, contact_number
    // (Similar validation logic as in add_user.php for reseller)
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        $sql = 'SELECT id FROM users WHERE username = :username';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST['username']);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = 'This username is already taken.';
                } else {
                    $username = trim($_POST['username']);
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
            unset($stmt);
        }
    }

    if (empty(trim($_POST['password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password must have atleast 6 characters.';
    } else {
        $password = trim($_POST['password']);
    }

    if (empty(trim($_POST['first_name']))) {
        $first_name_err = 'Please enter a name.';
    } else {
        $first_name = trim($_POST['first_name']);
    }

    if (empty(trim($_POST['address']))) {
        $address_err = 'Please enter an address.';
    } else {
        $address = trim($_POST['address']);
    }

    if (empty(trim($_POST['contact_number']))) {
        $contact_number_err = 'Please enter a contact number.';
    } else {
        $contact_number = trim($_POST['contact_number']);
    }

    $account_id = trim($_POST['account_id']);
    $quantity = trim($_POST['quantity']);

    // Get the price of the selected account
    $sql = 'SELECT price FROM accounts WHERE id = :account_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['account_id' => $account_id]);
    $account = $stmt->fetch();
    $price = $account['price'];

    $credits = $price * $quantity;


    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($first_name_err) && empty($address_err) && empty($contact_number_err)) {

        $pdo->beginTransaction();

        try {
            // Prepare an insert statement for users table
            $sql = 'INSERT INTO users (username, password, first_name, last_name, contact_number, address, credits, role, is_reseller, account_id, client_limit) VALUES (:username, :password, :first_name, :last_name, :contact_number, :address, :credits, :role, :is_reseller, :account_id, :client_limit)';

            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
                $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
                $stmt->bindParam(':address', $param_address, PDO::PARAM_STR);
                $stmt->bindParam(':credits', $param_credits, PDO::PARAM_STR);
                $stmt->bindParam(':role', $param_role, PDO::PARAM_STR);
                $stmt->bindParam(':is_reseller', $param_is_reseller, PDO::PARAM_BOOL);
                $stmt->bindParam(':account_id', $param_account_id, PDO::PARAM_INT);
                $stmt->bindParam(':client_limit', $param_client_limit, PDO::PARAM_INT);

                // Set parameters
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_first_name = $first_name;
                $param_last_name = ''; // Last name is not used for resellers
                $param_contact_number = $contact_number;
                $param_address = $address;
                $param_credits = $credits;
                $param_role = 'reseller';
                $param_is_reseller = true;
                $param_account_id = $account_id;
                $param_client_limit = $quantity;

                $stmt->execute();
                $user_id = $pdo->lastInsertId();
                unset($stmt);

                // Insert into resellers table
                $sql_reseller = 'INSERT INTO resellers (user_id) VALUES (:user_id)';
                if($stmt_reseller = $pdo->prepare($sql_reseller)) {
                    $stmt_reseller->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt_reseller->execute();
                    unset($stmt_reseller);
                }

                $pdo->commit();
                header('location: reseller_management.php');
                exit;

            }
        } catch (Exception $e) {
            $pdo->rollBack();
            echo 'Something went wrong. Please try again later.';
        }
    }
}

include 'header.php';
?>

<div class="page-header">
    <h2>Add Reseller</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Reseller Information</h3>
    </div>
    <div class="card-body">
        <form action='add_reseller.php' method='post'>
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
                <label class="form-label">Name</label>
                <input type='text' name='first_name' class='form-control' value='<?php echo htmlspecialchars($first_name); ?>'>
                <span class='text-danger'><?php echo $first_name_err; ?></span>
            </div>
            <div class='form-group'>
                <label class="form-label">Address</label>
                <input type='text' name='address' class='form-control' value='<?php echo htmlspecialchars($address); ?>'>
                <span class='text-danger'><?php echo $address_err; ?></span>
            </div>
            <div class='form-group'>
                <label class="form-label">Contact Number</label>
                <input type='text' name='contact_number' class='form-control' value='<?php echo htmlspecialchars($contact_number); ?>'>
                <span class='text-danger'><?php echo $contact_number_err; ?></span>
            </div>
            <?php
            // Fetch accounts from the database
            $stmt = $pdo->query('SELECT * FROM accounts ORDER BY name');
            $accounts = $stmt->fetchAll();
            ?>
            <div class='form-group'>
                <label class="form-label">Account Type</label>
                <select name="account_id" id="account_id" class="form-control">
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>" data-price="<?php echo $account['price']; ?>">
                            <?php echo htmlspecialchars($account['name']); ?> (<?php echo '₱' . number_format($account['price'], 2); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class='form-group'>
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
            </div>
            <div class='form-group'>
                <label class="form-label">Total Credits</label>
                <input type="text" id="total_credits" class="form-control" readonly>
            </div>
            <div class='form-group'>
                <input type='submit' class='btn btn-primary' value='Create Reseller'>
                <a class='btn btn-link' href='reseller_management.php'>Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountId = document.getElementById('account_id');
        const quantity = document.getElementById('quantity');
        const totalCredits = document.getElementById('total_credits');

        function calculateTotal() {
            const selectedOption = accountId.options[accountId.selectedIndex];
            const price = selectedOption.dataset.price;
            const total = price * quantity.value;
            totalCredits.value = '₱' + total.toFixed(2);
        }

        accountId.addEventListener('change', calculateTotal);
        quantity.addEventListener('input', calculateTotal);

        calculateTotal();
    });
</script>

<?php include 'footer.php'; ?>
