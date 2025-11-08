<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = intval($_POST['id_user']);
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $grade = trim($_POST['grade']);

    if ($id_user > 0 && !empty($prenom) && !empty($nom) && !empty($grade)) {
        $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ?, grade = ? WHERE id_user = ?");
        
        if ($stmt->execute([$prenom, $nom, $grade, $id_user])) {
            sendResponse(true, '', [
                'member' => [
                    'id_user' => $id_user,
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'grade' => $grade
                ]
            ]);
        } else {
            sendResponse(false, 'Failed to update member.');
        }
    } else {
        sendResponse(false, 'Invalid data provided.');
    }
}
exit;