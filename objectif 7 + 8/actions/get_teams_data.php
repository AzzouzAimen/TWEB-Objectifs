<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $teams = [];
    $teamResult = $pdo->query("SELECT * FROM teams ORDER BY nom ASC");
    
    // For each team, fetch its members (N+1 query pattern for nested data)
    while ($team = $teamResult->fetch()) {
        $sql = "SELECT u.id_user, u.nom, u.prenom, u.grade "
          . "FROM users u "
          . "JOIN team_members tm ON u.id_user = tm.usr_id "
          . "WHERE tm.team_id = ? "
          . "ORDER BY u.nom";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$team['id_team']]);
        $team['members'] = $stmt->fetchAll(); // Nest members array inside team
        $teams[] = $team;
    }

    echo json_encode([
        'success' => true,
        'teams' => $teams,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching teams data'
    ]);
}
