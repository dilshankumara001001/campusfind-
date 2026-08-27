<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$type || !$id || !in_array($type, ['lost', 'found'])) {
    header('Location: dashboard.php');
    exit;
}

$table = $type === 'lost' ? 'lost_items' : 'found_items';

// Delete image if exists
$stmt = $pdo->prepare("SELECT image FROM $table WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$item = $stmt->fetch();

if ($item && $item['image']) {
    $path = UPLOAD_PATH . $type . '/' . $item['image'];
    if (file_exists($path)) unlink($path);
}

$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
logActivity($user_id, 'delete_item', "Deleted $type item ID: $id");

header('Location: ' . ($type === 'lost' ? 'lost-items.php' : 'found-items.php') . '?deleted=1');
exit;
?>