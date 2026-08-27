<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/matching.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $date_lost = sanitize($_POST['date_lost'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($category) || empty($location) || empty($date_lost)) {
        $error = 'Please fill in all required fields.';
    } else {
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['image'], UPLOAD_PATH . 'lost/');
            if (isset($upload['success'])) {
                $image = $upload['filename'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO lost_items (user_id, category, title, description, location, date_lost, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $category, $title, $description, $location, $date_lost, $image]);
                $item_id = $pdo->lastInsertId();
                logActivity($user_id, 'add_lost', "Added lost item: $title");
                
                // Auto-match
                $matches = findMatchesForLost($item_id);
                if (!empty($matches)) {
                    $success = 'Item reported successfully! ' . count($matches) . ' potential matches found.';
                } else {
                    $success = 'Item reported successfully!';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
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
    <title>Report Lost - <?= SITE_NAME ?></title>
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
                        <h4 class="mb-0">📱 Report Lost Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat ?>"><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" placeholder="e.g., Main Library, 2nd Floor" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Lost *</label>
                                <input type="date" class="form-control" name="date_lost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <div class="image-preview mt-2"></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Report Lost Item</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>