<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

require_once 'db_config.php';

$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tutorial_content'])) {
    $content = $_POST['tutorial_content'];

    $sql = 'UPDATE tutorial SET content = :content WHERE id = 1';
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $update_success = true;
        } else {
            $update_error = 'Failed to save the tutorial.';
        }
    }
}

$sql = 'SELECT content FROM tutorial WHERE id = 1';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tutorial = $stmt->fetch();
$tutorial_content = $tutorial ? $tutorial['content'] : '';

include 'header.php';
?>

<div class="page-header">
    <h2>Edit Tutorial</h2>
</div>

<?php if ($update_success): ?>
    <div class="alert alert-success">
        Tutorial updated successfully!
    </div>
<?php endif; ?>
<?php if ($update_error): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($update_error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Tutorial Content</h3>
    </div>
    <div class="card-body">
        <form action="edit_tutorial.php" method="post">
            <div class="form-group">
                <textarea name="tutorial_content" class="form-control" rows="20"><?php echo htmlspecialchars($tutorial_content); ?></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Save Tutorial">
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
