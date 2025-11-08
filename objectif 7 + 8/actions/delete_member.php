<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id_user = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id_user > 0) {
    // Because of ON DELETE CASCADE, deleting a user will also remove them
    // from the team_members table automatically.
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
    
    if ($stmt->execute([$id_user])) {
        sendResponse(true, 'User has been deleted successfully.', ['id_user' => $id_user]);
    } else {
        sendResponse(false, 'Failed to delete user.');
    }
} else {
    sendResponse(false, 'Invalid user ID.');
}
exit;