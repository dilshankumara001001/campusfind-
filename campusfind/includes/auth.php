<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function registerUser($username, $email, $password, $phone, $college) {
    global $pdo;
    if (empty($username) || empty($email) || empty($password)) return ['error' => 'All fields required.'];
    if (!validateEmail($email)) return ['error' => 'Invalid email.'];
    if (strlen($password) < 6) return ['error' => 'Password must be 6+ chars.'];
    if ($phone && !validatePhone($phone)) return ['error' => 'Invalid phone number.'];
    if (getUserByUsername($username)) return ['error' => 'Username taken.'];
    if (getUserByEmail($email)) return ['error' => 'Email already registered.'];
    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone, college) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed, $phone, $college]);
        $user_id = $pdo->lastInsertId();
        logActivity($user_id, 'registration', 'User registered');
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'user';
        return ['success' => true, 'user_id' => $user_id];
    } catch (PDOException $e) {
        return ['error' => 'Registration failed: ' . $e->getMessage()];
    }
}

function loginUser($email, $password) {
    global $pdo;
    if (empty($email) || empty($password)) return ['error' => 'Email and password required.'];
    $user = getUserByEmail($email);
    if (!$user) return ['error' => 'Invalid credentials.'];
    if (!password_verify($password, $user['password'])) return ['error' => 'Invalid credentials.'];
    if (!$user['is_active']) return ['error' => 'Account disabled.'];
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    logActivity($user['id'], 'login', 'User logged in');
    return ['success' => true, 'user' => $user];
}

function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    return ['success' => true];
}

function changePassword($user_id, $current, $new) {
    global $pdo;
    $user = getUserById($user_id);
    if (!$user) return ['error' => 'User not found.'];
    if (!password_verify($current, $user['password'])) return ['error' => 'Current password incorrect.'];
    if (strlen($new) < 6) return ['error' => 'New password must be 6+ chars.'];
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
    logActivity($user_id, 'password_change', 'Password changed');
    return ['success' => true];
}
?>