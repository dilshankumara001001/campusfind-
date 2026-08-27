<?php
require_once __DIR__ . '/../config/database.php';

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function checkRateLimit($key, $max_attempts = 10, $time_window = 300) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$time_window]);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE request_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$key, $time_window]);
    $attempts = $stmt->fetchColumn();
    if ($attempts >= $max_attempts) return false;
    $stmt = $pdo->prepare("INSERT INTO rate_limits (request_key) VALUES (?)");
    $stmt->execute([$key]);
    return true;
}

function validateInput($data, $type) {
    switch ($type) {
        case 'email': return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'int': return filter_var($data, FILTER_VALIDATE_INT);
        default: return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }
}

function checkPasswordStrength($password) {
    $score = 0;
    if (strlen($password) >= 8) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
    return ['score' => $score, 'level' => ['Very Weak','Weak','Fair','Good','Strong','Very Strong'][$score]];
}

function sanitizeFilename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return str_replace(chr(0), '', $filename);
}