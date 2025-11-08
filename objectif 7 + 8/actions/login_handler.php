<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    header("Location: ../login.php?error=Veuillez remplir tous les champs");
    exit;
}

$user = checkLogin($username, $password, $pdo);

if ($user) {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    // Store user data in session
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_prenom'] = $user['prenom'];
    
    header("Location: ../admin.php");
    exit;
} else {
    header("Location: ../login.php?error=Nom d'utilisateur ou mot de passe incorrect");
    exit;
}
?>