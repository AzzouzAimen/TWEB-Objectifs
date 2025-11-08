<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_team = intval($_POST['id_team']);
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $chef_id = !empty($_POST['chef_id']) ? intval($_POST['chef_id']) : null;

    if (!empty($nom) && $id_team > 0) {
        $stmt = $pdo->prepare("UPDATE teams SET nom = ?, description = ?, chef_id = ? WHERE id_team = ?");
        
        if ($stmt->execute([$nom, $description, $chef_id, $id_team])) {
            sendResponse(true, '', [
                'team' => [
                    'id_team' => $id_team,
                    'nom' => $nom,
                    'description' => $description
                ]
            ]);
        } else {
            sendResponse(false, 'Erreur de mise à jour');
        }
    } else {
        sendResponse(false, 'Données invalides');
    }
}
exit;
?>