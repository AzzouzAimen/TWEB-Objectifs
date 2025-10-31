<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
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

$mysqli->begin_transaction();
try {
    // 1. Insert the new team
    $stmt_team = $mysqli->prepare("INSERT INTO teams (nom, description) VALUES (?, ?)");
    $stmt_team->bind_param("ss", $team_nom, $description);
    if (!$stmt_team->execute()) throw new Exception("Failed to create team.");
    
    $new_team_id = $mysqli->insert_id;

    // 2. Loop through and insert each new member
    for ($i = 0; $i < count($prenoms); $i++) {
        // Basic validation for each member
        if (empty($prenoms[$i]) || empty($noms[$i]) || empty($usernames[$i]) || empty($passwords[$i])) {
            continue; // Skip any empty rows accidentally submitted
        }

        // Hash the password
        $hashed_password = password_hash($passwords[$i], PASSWORD_DEFAULT);

        // --- THIS IS THE CORRECTED INSERT STATEMENT ---
        // It now correctly lists 6 columns and provides 6 placeholders for the values.
        $stmt_user = $mysqli->prepare("INSERT INTO users (username, password, prenom, nom, email, grade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_user->bind_param("ssssss", $usernames[$i], $hashed_password, $prenoms[$i], $noms[$i], $emails[$i], $grades[$i]);
        
        if (!$stmt_user->execute()) throw new Exception("Failed to create user: " . $usernames[$i]);
        
        $new_user_id = $mysqli->insert_id;

        // Link user to team
        $stmt_member = $mysqli->prepare("INSERT INTO team_members (team_id, usr_id) VALUES (?, ?)");
        $stmt_member->bind_param("ii", $new_team_id, $new_user_id);
        if (!$stmt_member->execute()) throw new Exception("Failed to link user to team.");
    }

    $mysqli->commit();
    header("Location: ../admin.php?success=Team and members created successfully.");

} catch (Exception $e) {
    $mysqli->rollback();
    if ($mysqli->errno === 1062) {
        header("Location: ../admin.php?error=Failed to create. A username, email, or team name you entered already exists.");
    } else {
        header("Location: ../admin.php?error=" . urlencode($e->getMessage()));
    }
}
exit;