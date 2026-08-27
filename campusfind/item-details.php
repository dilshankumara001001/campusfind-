<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/matching.php';

$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || !$id || !in_array($type, ['lost', 'found'])) {
    header('Location: browse.php');
    exit;
}

$table = $type === 'lost' ? 'lost_items' : 'found_items';
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: browse.php');
    exit;
}

$user = getUserById($item['user_id']);
$is_owner = isLoggedIn() && $_SESSION['user_id'] == $item['user_id'];

// Get matches
$matches = [];
if (isLoggedIn()) {
    if ($type === 'lost') {
        $matches = findMatchesForLost($id);
    } else {
        $matches = findMatchesForFound($id);
    }
}

// Get claims
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM claims c
    JOIN users u ON c.claimant_id = u.id
    WHERE " . ($type === 'lost' ? 'lost_item_id' : 'found_item_id') . " = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$claims = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <?php if ($item['image']): ?>
                        <img src="uploads/<?= $type ?>/<?= $item['image'] ?>" class="card-img-top" style="max-height:400px; object-fit:contain;">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-<?= $type === 'lost' ? 'danger' : 'success' ?> fs-6"><?= ucfirst($type) ?></span>
                                <?= getStatusBadge($item['status']) ?>
                            </div>
                            <span class="text-muted small"><?= timeAgo($item['created_at']) ?></span>
                        </div>
                        <h2 class="card-title"><?= htmlspecialchars($item['title']) ?></h2>
                        <p class="text-muted">📂 Category: <?= htmlspecialchars($item['category']) ?></p>
                        <p class="text-muted">📍 Location: <?= htmlspecialchars($item['location']) ?></p>
                        <?php if ($type === 'lost'): ?>
                            <p class="text-muted">📅 Date Lost: <?= date('M d, Y', strtotime($item['date_lost'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">📅 Date Found: <?= date('M d, Y', strtotime($item['date_found'])) ?></p>
                        <?php endif; ?>
                        <p class="card-text"><?= nl2br(htmlspecialchars($item['description'] ?? '')) ?></p>
                        <p class="text-muted small">👤 Reported by: <?= htmlspecialchars($user['username']) ?></p>
                        
                        <?php if (isLoggedIn() && !$is_owner && $item['status'] === 'open'): ?>
                            <a href="user/add-claim.php?type=<?= $type ?>&id=<?= $id ?>" class="btn btn-primary">Submit Claim</a>
                        <?php endif; ?>
                        
                        <?php if ($is_owner): ?>
                            <div class="mt-3">
                                <a href="user/edit-<?= $type ?>.php?id=<?= $id ?>" class="btn btn-warning">Edit</a>
                                <a href="user/delete-item.php?type=<?= $type ?>&id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Matches -->
                <?php if (!empty($matches) && isLoggedIn()): ?>
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="mb-0">🎯 Potential Matches</h5></div>
                        <div class="card-body">
                            <?php foreach ($matches as $match): ?>
                                <div class="match-item p-3 mb-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($match['found_item']['title'] ?? $match['lost_item']['title']) ?></strong>
                                            <span class="text-muted small">📍 <?= htmlspecialchars($match['found_item']['location'] ?? $match['lost_item']['location']) ?></span>
                                        </div>
                                        <span class="badge bg-success fs-6"><?= $match['score'] ?>%</span>
                                    </div>
                                    <div class="mt-2">
                                        <a href="item-details.php?type=<?= $type === 'lost' ? 'found' : 'lost' ?>&id=<?= $match['found_item']['id'] ?? $match['lost_item']['id'] ?>" class="btn btn-sm btn-outline-primary">View Item</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Claims -->
                <?php if (!empty($claims) && (isLoggedIn() && ($is_owner || isAdmin()))): ?>
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="mb-0">📩 Claims</h5></div>
                        <div class="card-body">
                            <?php foreach ($claims as $claim): ?>
                                <div class="claim-card <?= $claim['status'] ?> p-3 mb-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($claim['username']) ?></strong>
                                            <span class="text-muted small"><?= timeAgo($claim['created_at']) ?></span>
                                            <p class="mb-0 mt-1"><?= htmlspecialchars($claim['message'] ?? 'No message') ?></p>
                                        </div>
                                        <?= getStatusBadge($claim['status']) ?>
                                    </div>
                                    <?php if (($is_owner || isAdmin()) && $claim['status'] === 'pending'): ?>
                                        <div class="mt-2">
                                            <a href="admin/claim-action.php?id=<?= $claim['id'] ?>&action=approve" class="btn btn-sm btn-success">Approve</a>
                                            <a href="admin/claim-action.php?id=<?= $claim['id'] ?>&action=reject" class="btn btn-sm btn-danger">Reject</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>