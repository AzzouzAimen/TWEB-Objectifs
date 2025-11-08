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

// Get the existing team ID and the new member's details
$team_id = intval($_POST['team_id']);
$prenom = trim($_POST['prenom']);
$nom = trim($_POST['nom']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$email = trim($_POST['email']);
$grade = trim($_POST['grade']);

// Validation
if (empty($team_id) || empty($prenom) || empty($nom) || empty($username) || empty($password)) {
    header("Location: ../admin.php?error=Please select a team and fill all member fields.");
    exit;
}

// Start transaction to ensure both user creation and team linking succeed together
$pdo->beginTransaction();
try {
    // 1. Create the new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_user = $pdo->prepare("INSERT INTO users (username, password, prenom, nom, email, grade) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_user->execute([$username, $hashed_password, $prenom, $nom, $email, $grade]);
    
    $new_user_id = $pdo->lastInsertId();

    // 2. Link the new user to the selected team via junction table
    $stmt_member = $pdo->prepare("INSERT INTO team_members (team_id, usr_id) VALUES (?, ?)");
    $stmt_member->execute([$team_id, $new_user_id]);

    // If both succeed, commit the changes
    $pdo->commit();
    
    // Fetch team name for the response
    $team_query = $pdo->prepare("SELECT nom FROM teams WHERE id_team = ?");
    $team_query->execute([$team_id]);
    $team_name = $team_query->fetchColumn();
    
    sendResponse(true, '', [
        'member' => [
            'id_user' => $new_user_id,
            'prenom' => $prenom,
            'nom' => $nom,
            'grade' => $grade,
            'team_id' => $team_id,
            'team_name' => $team_name
        ]
    ]);

} catch (Exception $e) {
    // If anything fails, roll back
    $pdo->rollBack();
    
    $error_message = '';
    if ($e->getCode() == 23000) { // Duplicate entry error
        $error_message = 'Failed to add member. The username or email already exists.';
    } else {
        $error_message = $e->getMessage();
    }
    
    sendResponse(false, $error_message);
}
exit;