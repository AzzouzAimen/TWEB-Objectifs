<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin.php?error=Invalid request");
    exit;
}

// Team data
$team_nom = trim($_POST['team_nom']);
$description = trim($_POST['description']);

// Member data (comes in as arrays)
$prenoms = $_POST['prenom'] ?? [];
$noms = $_POST['nom'] ?? [];
$usernames = $_POST['username'] ?? [];
$passwords = $_POST['password'] ?? [];
$emails = $_POST['email'] ?? [];
$grades = $_POST['grade'] ?? [];

if (empty($team_nom)) {
    header("Location: ../admin.php?error=Team name is required.");
    exit;
}

// Begin transaction: all inserts must succeed or all will rollback
$pdo->beginTransaction();
try {
    //Insert the new team
    $stmt_team = $pdo->prepare("INSERT INTO teams (nom, description) VALUES (?, ?)");
    $stmt_team->execute([$team_nom, $description]);
    
    // Get the auto-generated team ID for linking members
    $new_team_id = $pdo->lastInsertId();

    // Loop through and insert each new member
    for ($i = 0; $i < count($prenoms); $i++) {
        // Basic validation for each member
        if (empty($prenoms[$i]) || empty($noms[$i]) || empty($usernames[$i]) || empty($passwords[$i])) {
            continue; // Skip any empty rows accidentally submitted
        }

        // Hash the password using bcrypt (never store plain passwords)
        $hashed_password = password_hash($passwords[$i], PASSWORD_DEFAULT);

        // Insert the new user
        $stmt_user = $pdo->prepare("INSERT INTO users (username, password, prenom, nom, email, grade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_user->execute([$usernames[$i], $hashed_password, $prenoms[$i], $noms[$i], $emails[$i], $grades[$i]]);
        
        $new_user_id = $pdo->lastInsertId();

        // Link user to team
        $stmt_member = $pdo->prepare("INSERT INTO team_members (team_id, usr_id) VALUES (?, ?)");
        $stmt_member->execute([$new_team_id, $new_user_id]);
    }

    // All operations succeeded, commit the transaction
    $pdo->commit();
    sendResponse(true, '');

} catch (Exception $e) {
    // Any error occurred, rollback all changes
    $pdo->rollBack();
    
    $error_message = '';
    // Check for duplicate entry error (username/email already exists)
    if ($e->getCode() == 23000) { // Integrity constraint violation
        $error_message = 'Failed to create. A username, email, or team name you entered already exists.';
    } else {
        $error_message = $e->getMessage();
    }
    
    sendResponse(false, $error_message);
}
exit;