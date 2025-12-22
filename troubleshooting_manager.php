<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';
require_once 'utils.php';

// Handle Add/Edit Guide
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_guide'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $category = trim($_POST['category']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $sql = 'INSERT INTO troubleshooting_guides (title, content, category, is_active) VALUES (:title, :content, :category, :is_active)';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    header('location: troubleshooting_manager.php');
    exit;
}

// Handle Delete Guide
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = 'DELETE FROM troubleshooting_guides WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: troubleshooting_manager.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Troubleshooting Manager</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add New Guide/Tip</h3>
    </div>
    <div class="card-body">
        <form action="troubleshooting_manager.php" method="post">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea name="content" id="content" class="form-control" rows="10" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" name="category" id="category" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" name="is_active" id="is_active" value="1" checked>
            </div>
            <div class="form-group">
                <input type="submit" name="add_guide" class="btn btn-primary" value="Add Guide">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Existing Guides</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT * FROM troubleshooting_guides ORDER BY category, title';
                $guides = $pdo->query($sql)->fetchAll();
                foreach ($guides as $guide) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($guide['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($guide['category']) . "</td>";
                    echo "<td>" . ($guide['is_active'] ? 'Active' : 'Inactive') . "</td>";
                    echo "<td>";
                    echo "<a href='edit_troubleshooting_guide.php?id=" . $guide['id'] . "' class='btn btn-primary'>Edit</a>";
                    echo "<a href='troubleshooting_manager.php?delete=" . $guide['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
