<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$recent_items = getRecentItems(8);
$stats = [
    'total_lost' => getCount('lost_items'),
    'total_found' => getCount('found_items'),
    'total_users' => getCount('users'),
    'total_matches' => getCount('match_log')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Find Lost Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="hero-section text-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-4">Find Your Lost Items on Campus</h1>
                    <p class="lead mb-4">Report lost or found items and get matched automatically</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="user/add-lost.php" class="btn btn-danger btn-lg">📱 Report Lost</a>
                        <a href="user/add-found.php" class="btn btn-success btn-lg">📦 Report Found</a>
                        <a href="browse.php" class="btn btn-outline-light btn-lg">🔍 Browse</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="stats-section py-4 bg-light">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h3 class="display-6 text-primary"><?= $stats['total_lost'] ?></h3>
                        <p class="text-muted">Lost Items</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h3 class="display-6 text-success"><?= $stats['total_found'] ?></h3>
                        <p class="text-muted">Found Items</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h3 class="display-6 text-info"><?= $stats['total_matches'] ?></h3>
                        <p class="text-muted">Smart Matches</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h3 class="display-6 text-warning"><?= $stats['total_users'] ?></h3>
                        <p class="text-muted">Active Users</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="recent-items py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Recent Items</h2>
                <a href="browse.php" class="btn btn-outline-primary">View All →</a>
            </div>
            
            <?php if (empty($recent_items)): ?>
                <div class="text-center py-5">
                    <p class="text-muted">No items reported yet. Be the first!</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($recent_items as $item): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 item-card">
                                <?php if ($item['image']): ?>
                                    <img src="uploads/<?= $item['type'] ?>/<?= $item['image'] ?>" class="card-img-top" alt="<?= $item['title'] ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <span class="display-1 text-muted">📦</span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-<?= $item['type'] === 'lost' ? 'danger' : 'success' ?>">
                                            <?= ucfirst($item['type']) ?>
                                        </span>
                                        <?= getStatusBadge($item['status']) ?>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                    <p class="card-text text-muted small">📍 <?= htmlspecialchars($item['location']) ?></p>
                                    <p class="card-text small text-muted">🕐 <?= timeAgo($item['created_at']) ?></p>
                                    <a href="item-details.php?type=<?= $item['type'] ?>&id=<?= $item['id'] ?>" class="btn btn-primary btn-sm w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>