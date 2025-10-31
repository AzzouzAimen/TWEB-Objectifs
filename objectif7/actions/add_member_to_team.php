<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
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

// Start transaction
$mysqli->begin_transaction();
try {
    // 1. Create the new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_user = $mysqli->prepare("INSERT INTO users (username, password, prenom, nom, email, grade) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_user->bind_param("ssssss", $username, $hashed_password, $prenom, $nom, $email, $grade);
    if (!$stmt_user->execute()) {
        throw new Exception("Failed to create the new user.");
    }
    
    $new_user_id = $mysqli->insert_id;

    // 2. Link the new user to the selected team
    $stmt_member = $mysqli->prepare("INSERT INTO team_members (team_id, usr_id) VALUES (?, ?)");
    $stmt_member->bind_param("ii", $team_id, $new_user_id);
    if (!$stmt_member->execute()) {
        throw new Exception("Failed to assign the user to the team.");
    }

    // If both succeed, commit the changes
    $mysqli->commit();
    header("Location: ../admin.php?success=New member created and assigned to the team successfully.");

} catch (Exception $e) {
    // If anything fails, roll back
    $mysqli->rollback();
    if ($mysqli->errno === 1062) { // Duplicate entry error
        header("Location: ../admin.php?error=Failed to add member. The username or email already exists.");
    } else {
        header("Location: ../admin.php?error=" . urlencode($e->getMessage()));
    }
}
exit;