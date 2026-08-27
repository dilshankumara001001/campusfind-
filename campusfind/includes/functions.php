<?php
require_once __DIR__ . '/../config/database.php';

function sanitize($input) {
    if (is_array($input)) return array_map('sanitize', $input);
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^0[0-9]{9}$/', $phone);
}

function uploadFile($file, $target_dir, $max_size = 5242880) {
    if ($file['size'] > $max_size) return ['error' => 'File too large.'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed)) return ['error' => 'Invalid file type.'];
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $target_path = $target_dir . $filename;
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['error' => 'Failed to upload.'];
}

function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function getUserByUsername($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}

function getStatusBadge($status) {
    $badges = ['open' => 'success', 'matched' => 'info', 'claimed' => 'warning', 
               'returned' => 'primary', 'closed' => 'secondary', 'pending' => 'warning',
               'approved' => 'success', 'rejected' => 'danger', 'completed' => 'primary'];
    $label = ucfirst($status);
    $class = $badges[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . $label . '</span>';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) { $mins = floor($diff / 60); return $mins . 'm ago'; }
    if ($diff < 86400) { $hours = floor($diff / 3600); return $hours . 'h ago'; }
    if ($diff < 604800) { $days = floor($diff / 86400); return $days . 'd ago'; }
    return date('M d, Y', $time);
}

function getCount($table, $where = '') {
    global $pdo;
    $query = "SELECT COUNT(*) FROM $table";
    if ($where) $query .= " WHERE $where";
    return $pdo->query($query)->fetchColumn();
}

function getRecentItems($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 'lost' as type, li.*, u.username 
        FROM lost_items li JOIN users u ON li.user_id = u.id
        WHERE li.status NOT IN ('returned', 'closed')
        UNION ALL
        SELECT 'found' as type, fi.*, u.username 
        FROM found_items fi JOIN users u ON fi.user_id = u.id
        WHERE fi.status NOT IN ('returned', 'closed')
        ORDER BY created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function createNotification($user_id, $type, $title, $message, $link = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $type, $title, $message, $link]);
}

function getUnreadNotificationCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function logActivity($user_id, $action, $details = '') {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $action, $details, $ip, $user_agent]);
}
?>