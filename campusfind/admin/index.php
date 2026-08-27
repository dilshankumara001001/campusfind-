<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$stats = getDashboardStats();

// Recent activity
$stmt = $pdo->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <?php include 'sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10">
                <div class="container py-4">
                    <h1 class="mb-4">Admin Dashboard</h1>
                    
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-primary"><?= $stats['total_users'] ?></div>
                                <div class="label">Total Users</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-danger"><?= $stats['total_lost'] ?></div>
                                <div class="label">Lost Items</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-success"><?= $stats['total_found'] ?></div>
                                <div class="label">Found Items</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-warning"><?= $stats['pending_claims'] ?></div>
                                <div class="label">Pending Claims</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-info"><?= $stats['total_claims'] ?></div>
                                <div class="label">Total Claims</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-primary"><?= $stats['returned_items'] ?></div>
                                <div class="label">Returned Items</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-success"><?= $stats['total_matches'] ?></div>
                                <div class="label">Smart Matches</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card text-center">
                                <div class="count text-danger"><?= $stats['unread_notifications'] ?></div>
                                <div class="label">Unread Notifications</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">Recent Activity</div>
                        <div class="card-body">
                            <?php if (empty($activities)): ?>
                                <p class="text-muted">No activity yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Details</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activities as $activity): ?>
                                                <tr>
                                                    <td>#<?= $activity['user_id'] ?></td>
                                                    <td><?= htmlspecialchars($activity['action']) ?></td>
                                                    <td><?= htmlspecialchars($activity['details']) ?></td>
                                                    <td><?= timeAgo($activity['created_at']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>