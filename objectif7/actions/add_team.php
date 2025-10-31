<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $chef_id = !empty($_POST['chef_id']) ? intval($_POST['chef_id']) : null;

    if (!empty($nom)) {
        $stmt = $mysqli->prepare("INSERT INTO teams (nom, description, chef_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nom, $description, $chef_id);
        if ($stmt->execute()) {
            header("Location: ../admin.php?success=Équipe ajoutée");
        } else {
            header("Location: ../admin.php?error=Erreur lors de l'ajout");
        }
    } else {
        header("Location: ../admin.php?error=Le nom de l'équipe est requis");
    }
}
exit;
?>