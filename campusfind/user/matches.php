<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/matching.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$matches = getUserMatches($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Matches - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-4">
        <h1 class="mb-4">🎯 My Matches</h1>
        <?php if (empty($matches)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No matches found yet.</p>
                <p class="text-muted small">Matches appear when your lost/found items match with others.</p>
            </div>
        <?php else: ?>
            <div id="matches-container">
                <?php foreach ($matches as $match): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <h6>📱 Lost: <?= htmlspecialchars($match['lost_title']) ?></h6>
                                    <p class="text-muted small">📍 <?= htmlspecialchars($match['lost_location']) ?></p>
                                </div>
                                <div class="col-md-5">
                                    <h6>📦 Found: <?= htmlspecialchars($match['found_title']) ?></h6>
                                    <p class="text-muted small">📍 <?= htmlspecialchars($match['found_location']) ?></p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="badge bg-success fs-5 p-2"><?= $match['score'] ?>%</span>
                                    <p class="text-muted small mt-2"><?= getStatusBadge($match['status']) ?></p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="../item-details.php?type=lost&id=<?= $match['lost_item_id'] ?>" class="btn btn-sm btn-outline-primary">View Lost</a>
                                <a href="../item-details.php?type=found&id=<?= $match['found_item_id'] ?>" class="btn btn-sm btn-outline-primary">View Found</a>
                                <?php if ($match['status'] === 'pending'): ?>
                                    <a href="claim-match.php?match_id=<?= $match['id'] ?>" class="btn btn-sm btn-success">Claim This Match</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/matching.js"></script>
</body>
</html>