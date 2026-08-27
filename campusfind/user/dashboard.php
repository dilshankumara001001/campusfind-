<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stats = [
    'lost' => getCount('lost_items', "user_id = $user_id"),
    'found' => getCount('found_items', "user_id = $user_id"),
    'claims' => getCount('claims', "claimant_id = $user_id"),
    'matches' => getCount('match_log', "lost_item_id IN (SELECT id FROM lost_items WHERE user_id = $user_id) OR found_item_id IN (SELECT id FROM found_items WHERE user_id = $user_id)")
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-4">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="icon">📱</div>
                    <div class="count"><?= $stats['lost'] ?></div>
                    <div class="label">Lost Items</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="icon">📦</div>
                    <div class="count"><?= $stats['found'] ?></div>
                    <div class="label">Found Items</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="icon">📩</div>
                    <div class="count"><?= $stats['claims'] ?></div>
                    <div class="label">Claims</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="icon">🎯</div>
                    <div class="count"><?= $stats['matches'] ?></div>
                    <div class="label">Matches</div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="add-lost.php" class="btn btn-danger">📱 Report Lost</a>
                            <a href="add-found.php" class="btn btn-success">📦 Report Found</a>
                            <a href="lost-items.php" class="btn btn-outline-primary">View My Lost</a>
                            <a href="found-items.php" class="btn btn-outline-primary">View My Found</a>
                            <a href="claims.php" class="btn btn-outline-primary">View Claims</a>
                            <a href="matches.php" class="btn btn-outline-primary">View Matches</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>