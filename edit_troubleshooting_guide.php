<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('location: settings.php?tab=advanced');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM troubleshooting_guides WHERE id = :id');
$stmt->execute(['id' => $id]);
$guide = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $sql = 'UPDATE troubleshooting_guides SET title = :title, content = :content, category = :category, is_active = :is_active WHERE id = :id';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('location: settings.php?tab=advanced');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Edit Guide/Tip</h2>
</div>

<div class="card">
    <div class="card-body">
        <form action="edit_troubleshooting_guide.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($guide['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea name="content" id="content" class="form-control" rows="10" required><?php echo htmlspecialchars($guide['content']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" name="category" id="category" class="form-control" value="<?php echo htmlspecialchars($guide['category']); ?>" required>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $guide['is_active'] ? 'checked' : ''; ?>>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Guide">
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
