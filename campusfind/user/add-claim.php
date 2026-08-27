<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || !$item_id) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];

// Get item
if ($type === 'lost') {
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
}
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found.");
}

// Check if user owns item
if ($item['user_id'] == $user_id) {
    die("You cannot claim your own item!");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    
    if (empty($message)) {
        $error = 'Please enter a message.';
    } else {
        try {
            // For lost item - find a found item
            if ($type === 'lost') {
                // Get any open found item
                $stmt = $pdo->query("SELECT id FROM found_items WHERE status = 'open' LIMIT 1");
                $found = $stmt->fetch();
                
                if (!$found) {
                    $error = 'No found items available.';
                } else {
                    $found_id = $found['id'];
                    
                    // Check for existing claim
                    $stmt = $pdo->prepare("SELECT * FROM claims WHERE lost_item_id = ? AND found_item_id = ?");
                    $stmt->execute([$item_id, $found_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $error = 'Claim already exists for this item.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO claims (lost_item_id, found_item_id, claimant_id, message) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$item_id, $found_id, $user_id, $message]);
                        $success = true;
                    }
                }
            } else {
                // For found item - find a lost item
                $stmt = $pdo->query("SELECT id FROM lost_items WHERE status = 'open' LIMIT 1");
                $lost = $stmt->fetch();
                
                if (!$lost) {
                    $error = 'No lost items available.';
                } else {
                    $lost_id = $lost['id'];
                    
                    $stmt = $pdo->prepare("SELECT * FROM claims WHERE lost_item_id = ? AND found_item_id = ?");
                    $stmt->execute([$lost_id, $item_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $error = 'Claim already exists for this item.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO claims (lost_item_id, found_item_id, claimant_id, message) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$lost_id, $item_id, $user_id, $message]);
                        $success = true;
                    }
                }
            }
            
            if ($success) {
                // Update status
                if ($type === 'lost') {
                    $stmt = $pdo->prepare("UPDATE lost_items SET status = 'claimed' WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE found_items SET status = 'claimed' WHERE id = ?");
                }
                $stmt->execute([$item_id]);
                
                header('Location: ../user/claims.php?success=1');
                exit;
            }
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Claim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>📩 Submit Claim</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5>Item: <?= htmlspecialchars($item['title']) ?></h5>
                            <p>Category: <?= htmlspecialchars($item['category']) ?></p>
                            <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                            <p>Status: <?= getStatusBadge($item['status']) ?></p>
                        </div>
                        
                        <?php if ($item['status'] === 'open'): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Why is this yours?</label>
                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Claim</button>
                                <a href="../item-details.php?type=<?= $type ?>&id=<?= $item_id ?>" class="btn btn-secondary">Cancel</a>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">This item is already claimed!</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>