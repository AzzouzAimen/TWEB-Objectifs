<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$id_user = intval($_GET['id'] ?? 0);

if ($id_user > 0) {
    // Because of ON DELETE CASCADE, deleting a user will also remove them
    // from the team_members table automatically.
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    
    if ($stmt->execute()) {
        header("Location: ../admin.php?success=User has been deleted successfully.");
    } else {
        header("Location: ../admin.php?error=Failed to delete user.");
    }
} else {
    header("Location: ../admin.php?error=Invalid user ID.");
}
exit;