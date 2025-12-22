<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Check if the user is a reseller
if (isset($_SESSION['is_reseller']) && $_SESSION['is_reseller'] == 1) {
    header('location: reseller_dashboard.php');
    exit;
}

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// --- SEARCH ---
$search = $_GET['search'] ?? '';

// --- PAGINATION ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// --- Fetch total number of users for pagination ---
$sql_count = "SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 0 AND reseller_id IS NULL";
if ($search) {
    $sql_count .= " AND (username LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
}
$stmt_count = $pdo->prepare($sql_count);
if ($search) {
    $stmt_count->bindValue(':search', '%' . $search . '%');
}
$stmt_count->execute();
$total_users = $stmt_count->fetchColumn();
$total_pages = ceil($total_users / $limit);


// Fetch users from the database (only regular users, not admin or banned)
$sql = "SELECT id, username, first_name, last_name, contact_number, login_code, device_id, role, status, payment FROM users WHERE role = 'user' AND banned = 0 AND reseller_id IS NULL";
if ($search) {
    $sql .= " AND (username LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
}
$sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();


include 'header.php';
?>

<div class="page-header">
    <h1><?php echo translate('user_management'); ?></h1>
    <div class="page-actions">
        <a href='add_user.php' class='btn btn-success'>
            <span class="material-icons">person_add</span>
            <?php echo translate('add_new_user'); ?>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('users'); ?></h3>
    </div>
    <div class="card-body">
        <div class="search-container">
            <form action="user_management.php" method="get" id="search-form">
                <input type="text" name="search" id="search" placeholder="<?php echo translate('search_for_users'); ?>" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                <button type="submit" class="btn btn-primary"><?php echo translate('search'); ?></button>
            </form>
        </div>
        <div class="table-container">
            <table class='table'>
                <thead>
                    <tr>
                        <th><?php echo translate('id'); ?></th>
                        <th><?php echo translate('username'); ?></th>
                        <th><?php echo translate('first_name'); ?></th>
                        <th><?php echo translate('last_name'); ?></th>
                        <th><?php echo translate('contact_number'); ?></th>
                        <th><?php echo translate('login_code'); ?></th>
                        <th><?php echo translate('device_id'); ?></th>
                        <th><?php echo translate('role'); ?></th>
                        <th><?php echo translate('status'); ?></th>
                        <th><?php echo translate('payment'); ?></th>
                        <th><?php echo translate('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="11" style="text-align:center;"><?php echo translate('no_users_found'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td data-label='ID'><?php echo $user['id']; ?></td>
                                <td data-label='Username'><?php echo htmlspecialchars($user['username']); ?></td>
                                <td data-label='First Name'><?php echo htmlspecialchars($user['first_name']); ?></td>
                                <td data-label='Last Name'><?php echo htmlspecialchars($user['last_name']); ?></td>
                                <td data-label='Contact #'><?php echo htmlspecialchars($user['contact_number']); ?></td>
                                <td data-label='Login Code'><?php echo htmlspecialchars($user['login_code']); ?></td>
                                <td data-label='Device ID'><?php echo htmlspecialchars($user['device_id']); ?></td>
                                <td data-label='Role'><?php echo htmlspecialchars($user['role']); ?></td>
                                <td data-label='Status'><?php echo htmlspecialchars($user['status']); ?></td>
                                <td data-label='Payment'><?php echo htmlspecialchars($user['payment']); ?></td>
                                <td data-label='Actions'>
                                    <div class="action-buttons">
                                        <a href='delete_user.php?id=<?php echo $user['id']; ?>' class='btn btn-sm btn-delete'>
                                            <span class="material-icons">delete</span>
                                            <?php echo translate('delete'); ?>
                                        </a>
                                        <a href='edit_user.php?id=<?php echo $user['id']; ?>' class='btn btn-sm btn-edit'>
                                            <span class="material-icons">edit</span>
                                            <?php echo translate('edit'); ?>
                                        </a>
                                        <a href='pay_user.php?id=<?php echo $user['id']; ?>' class='btn btn-sm btn-pay'>
                                            <span class="material-icons">payment</span>
                                            <?php echo translate('pay'); ?>
                                        </a>
                                        <a href='document.php?type=statement&customer=<?php echo $user['id']; ?>' class='btn btn-sm btn-invoice'>
                                            <span class="material-icons">description</span>
                                            <?php echo translate('receipt'); ?>
                                        </a>
                                        <a href='ban_user.php?id=<?php echo $user['id']; ?>' class='btn btn-sm btn-ban'>
                                            <span class="material-icons">block</span>
                                            <?php echo translate('ban'); ?>
                                        </a>
                                        <a href='view_user.php?id=<?php echo $user['id']; ?>' class='btn btn-sm btn-view'>
                                            <span class="material-icons">visibility</span>
                                            <?php echo translate('view'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="user_management.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-secondary"><?php echo translate('previous'); ?></a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="user_management.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-secondary"><?php echo translate('next'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('search');
    let currentSuggestion = '';

    searchInput.addEventListener('input', (e) => {
        const query = searchInput.value;

        if (e.inputType === 'deleteContentBackward') {
            return;
        }

        if (query.length < 2) {
            return;
        }

        fetch(`api_get_users.php?query=${query}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const topSuggestion = data[0].username;
                    if (topSuggestion.toLowerCase().startsWith(query.toLowerCase())) {
                        currentSuggestion = topSuggestion;
                        searchInput.value = query + topSuggestion.substring(query.length);
                        searchInput.setSelectionRange(query.length, topSuggestion.length);
                    }
                }
            });
    });

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Tab' || e.key === 'Enter') {
            if (currentSuggestion) {
                e.preventDefault();
                searchInput.value = currentSuggestion;
                const end = searchInput.value.length;
                searchInput.setSelectionRange(end, end);
                currentSuggestion = '';
            }
        } else if (e.key === 'Backspace') {
            const selectionStart = searchInput.selectionStart;
            const selectionEnd = searchInput.selectionEnd;

            if (selectionStart !== selectionEnd) {
                e.preventDefault();
                searchInput.value = searchInput.value.substring(0, selectionStart);
                currentSuggestion = '';
            }
        }
    });
</script>


<?php include 'footer.php'; ?>
