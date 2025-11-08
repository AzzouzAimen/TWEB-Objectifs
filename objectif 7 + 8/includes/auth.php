<?php

 //check if username and password are valid using PDO.
function checkLogin($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(); // Fetch one row as an associative array
    
    // Verify hashed password securely (never store plain text passwords)
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: login.php?error=Access denied");
        exit;
    }
}
?>