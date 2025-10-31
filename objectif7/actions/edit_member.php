<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = intval($_POST['id_user']);
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $grade = trim($_POST['grade']);

    if ($id_user > 0 && !empty($prenom) && !empty($nom) && !empty($grade)) {
        $stmt = $mysqli->prepare("UPDATE users SET prenom = ?, nom = ?, grade = ? WHERE id_user = ?");
        $stmt->bind_param("sssi", $prenom, $nom, $grade, $id_user);
        
        if ($stmt->execute()) {
            header("Location: ../admin.php?success=Member updated successfully.");
        } else {
            header("Location: ../admin.php?error=Failed to update member.");
        }
    } else {
        header("Location: ../admin.php?error=Invalid data provided.");
    }
}
exit;