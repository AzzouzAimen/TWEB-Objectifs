<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_team = intval($_POST['id_team']);
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $chef_id = !empty($_POST['chef_id']) ? intval($_POST['chef_id']) : null;

    if (!empty($nom) && $id_team > 0) {
        $stmt = $mysqli->prepare("UPDATE teams SET nom = ?, description = ?, chef_id = ? WHERE id_team = ?");
        $stmt->bind_param("ssii", $nom, $description, $chef_id, $id_team);
        if ($stmt->execute()) {
            header("Location: ../admin.php?success=Équipe mise à jour");
        } else {
            header("Location: ../admin.php?error=Erreur de mise à jour");
        }
    } else {
        header("Location: ../admin.php?error=Données invalides");
    }
}
exit;
?>