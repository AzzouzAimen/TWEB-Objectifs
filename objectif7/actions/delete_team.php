<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$id_team = intval($_GET['id']);
if ($id_team > 0) {
    $stmt = $mysqli->prepare("DELETE FROM teams WHERE id_team = ?");
    $stmt->bind_param("i", $id_team);
    if ($stmt->execute()) {
        header("Location: ../admin.php?success=Équipe supprimée");
    } else {
        header("Location: ../admin.php?error=Erreur de suppression");
    }
}
exit;
?>