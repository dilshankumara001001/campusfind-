<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: lost-items.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $date_lost = sanitize($_POST['date_lost'] ?? '');
    
    if (empty($title) || empty($category) || empty($location) || empty($date_lost)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE lost_items SET category=?, title=?, description=?, location=?, date_lost=? WHERE id=? AND user_id=?");
            $stmt->execute([$category, $title, $description, $location, $date_lost, $id, $user_id]);
            logActivity($user_id, 'edit_lost', "Edited lost item: $title");
            $success = 'Item updated successfully!';
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$categories = ['Electronics', 'Keys', 'Books', 'Accessories', 'Clothing', 'Documents', 'Wallets', 'Bags', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lost Item - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">✏️ Edit Lost Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($item['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($item['location']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Lost *</label>
                                <input type="date" class="form-control" name="date_lost" value="<?= $item['date_lost'] ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Item</button>
                            <a href="lost-items.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>