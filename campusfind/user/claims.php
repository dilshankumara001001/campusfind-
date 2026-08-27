<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT c.*, 
           li.title as lost_title, li.location as lost_location,
           fi.title as found_title, fi.location as found_location,
           u.username as claimant_name
    FROM claims c
    JOIN lost_items li ON c.lost_item_id = li.id
    JOIN found_items fi ON c.found_item_id = fi.id
    JOIN users u ON c.claimant_id = u.id
    WHERE c.claimant_id = ? OR li.user_id = ? OR fi.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$claims = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-4">
        <h1 class="mb-4">My Claims</h1>
        <?php if (empty($claims)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No claims yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lost Item</th>
                            <th>Found Item</th>
                            <th>Claimant</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td><?= htmlspecialchars($claim['lost_title']) ?></td>
                                <td><?= htmlspecialchars($claim['found_title']) ?></td>
                                <td><?= htmlspecialchars($claim['claimant_name']) ?></td>
                                <td><?= getStatusBadge($claim['status']) ?></td>
                                <td><?= timeAgo($claim['created_at']) ?></td>
                                <td>
                                    <a href="../item-details.php?type=lost&id=<?= $claim['lost_item_id'] ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>