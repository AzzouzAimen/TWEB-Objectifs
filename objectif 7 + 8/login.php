<?php

// Start session - must be called before any output
session_start();

// Include authentication functions
require_once 'includes/auth.php';

// If already logged in, redirect to admin page
if (isLoggedIn()) {
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Administration</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Connexion Administrateur</h2>
        
        <?php
        // Display error message if exists
        // $_GET['error'] comes from URL parameter: login.php?error=message
        if (isset($_GET['error'])) {
            // htmlspecialchars() prevents XSS attacks by escaping HTML
            echo '<div class="error-message">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <!-- Login form -->
        <!-- action: where form data is sent -->
        <!-- method: POST (secure, data not visible in URL) -->
        <form action="actions/login_handler.php" method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur:</label>
                <!-- name: field name used in PHP ($_POST['username']) -->
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Se connecter</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>

