<?php
require_once '../includes/db_connect.php';
$stmt = $pdo->query("SELECT m.match_id, t1.name AS team1, t2.name AS team2, m.score_team1, m.score_team2, m.status FROM matches m JOIN teams t1 ON m.team1_id = t1.team_id JOIN teams t2 ON m.team2_id = t2.team_id WHERE m.status = 'ongoing'");
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($matches);
?>
