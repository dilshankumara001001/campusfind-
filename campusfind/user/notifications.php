<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    markAllNotificationsAsRead($user_id);
    header('Location: notifications.php');
    exit;
}

// Mark single as read
if (isset($_GET['read']) && isset($_GET['id'])) {
    markNotificationAsRead((int)$_GET['id'], $user_id);
    header('Location: notifications.php');
    exit;
}

// Delete notification
if (isset($_GET['delete']) && isset($_GET['id'])) {
    deleteNotification((int)$_GET['id'], $user_id);
    header('Location: notifications.php');
    exit;
}

$notifications = getUserNotifications($user_id);
$unread_count = getUnreadNotificationCount($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Notifications <span class="badge bg-danger"><?= $unread_count ?></span></h1>
            <?php if ($unread_count > 0): ?>
                <a href="?mark_all_read=1" class="btn btn-sm btn-primary">Mark All as Read</a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No notifications yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($notif['title']) ?></h5>
                            <p class="mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                            <small class="text-muted"><?= timeAgo($notif['created_at']) ?></small>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if (!$notif['is_read']): ?>
                                <a href="?read=1&id=<?= $notif['id'] ?>" class="btn btn-sm btn-outline-primary">Mark Read</a>
                            <?php endif; ?>
                            <a href="?delete=1&id=<?= $notif['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notification?')">×</a>
                        </div>
                    </div>
                    <?php if ($notif['link']): ?>
                        <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-primary mt-2">View Details</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>