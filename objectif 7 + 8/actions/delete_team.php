<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id_team = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id_team > 0) {
    $stmt = $pdo->prepare("DELETE FROM teams WHERE id_team = ?");
    if ($stmt->execute([$id_team])) {
        sendResponse(true, 'Équipe supprimée', ['id_team' => $id_team]);
    } else {
        sendResponse(false, 'Erreur de suppression');
    }
} else {
    sendResponse(false, 'Invalid team ID.');
}
exit;
?>