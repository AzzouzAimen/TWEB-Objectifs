<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin.php?error=Invalid request");
    exit;
}

// --- Get all form data for the new user ---
$team_id = intval($_POST['team_id']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$prenom = trim($_POST['prenom']);
$nom = trim($_POST['nom']);
$email = trim($_POST['email']);
$grade = trim($_POST['grade']);
$role = trim($_POST['role']);

// --- Basic Validation ---
if (empty($team_id) || empty($username) || empty($password) || empty($prenom) || empty($nom) || empty($email)) {
    header("Location: ../admin.php?error=Please fill all required fields for the new member.");
    exit;
}

// --- Start Database Transaction ---
// A transaction ensures that both database operations succeed, or none do.
$mysqli->begin_transaction();

try {
    // --- Step 1: Create the new user in the 'users' table ---

    // CRITICAL: Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_user = $mysqli->prepare("INSERT INTO users (username, password, prenom, nom, email, grade, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_user->bind_param("sssssss", $username, $hashed_password, $prenom, $nom, $email, $grade, $role);
    
    // Execute the user creation
    if (!$stmt_user->execute()) {
        // If it fails (e.g., duplicate username), throw an exception to trigger the rollback
        throw new Exception($mysqli->error);
    }

    // Get the ID of the user we just created
    $new_user_id = $mysqli->insert_id;

    // --- Step 2: Assign the new user to the team in 'team_members' table ---
    $stmt_member = $mysqli->prepare("INSERT INTO team_members (team_id, usr_id) VALUES (?, ?)");
    $stmt_member->bind_param("ii", $team_id, $new_user_id);
    
    // Execute the team assignment
    if (!$stmt_member->execute()) {
        // If this fails, throw an exception
        throw new Exception($mysqli->error);
    }

    // --- If both steps were successful, commit the transaction ---
    $mysqli->commit();
    header("Location: ../admin.php?success=New member was created and added to the team successfully.");

} catch (Exception $e) {
    // --- If any step failed, roll back all changes ---
    $mysqli->rollback();
    
    // Provide a specific error for duplicate entries
    if ($mysqli->errno === 1062) {
        header("Location: ../admin.php?error=Failed to add member. The username or email already exists.");
    } else {
        header("Location: ../admin.php?error=An error occurred: " . $e->getMessage());
    }
}

exit;
?>