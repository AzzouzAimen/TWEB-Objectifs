<?php
/**
 * Authentication Functions (MySQLi Version)
 */

/**
 * Check if username and password are valid using MySQLi.
 * 
 * @param string $username
 * @param string $password
 * @param mysqli $mysqli - The MySQLi connection object
 * @return array|false
 */
function checkLogin($username, $password, $mysqli) {
    // Prepare SQL query with placeholder (?)
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
    
    // Bind the parameter to the placeholder
    // "s" means the parameter is a string
    $stmt->bind_param("s", $username);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result of the query
    $result = $stmt->get_result();
    
    // Fetch one row as an associative array
    $user = $result->fetch_assoc();
    
    // Check if user exists AND password matches
    if ($user && password_verify($password, $user['password'])) {
        return $user; // Valid credentials
    }
    
    return false; // Invalid credentials
}

// --- NO CHANGES TO THE FUNCTIONS BELOW ---

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