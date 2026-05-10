<?php
session_start();
require_once '../includes/db_connect.php';
if ($_SESSION['role'] != 'manager' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit;
}
$match_id = (int)$_POST['match_id'];
$score_team1 = $_POST['score_team1'] ?? '';
$score_team2 = $_POST['score_team2'] ?? '';
$status = $_POST['status'] ?? '';
$allowed_statuses = ['scheduled', 'ongoing', 'completed'];
if (!is_numeric($score_team1) || !is_numeric($score_team2) || (int)$score_team1 < 0 || (int)$score_team2 < 0 || !in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}
$stmt = $pdo->prepare("UPDATE matches SET score_team1 = ?, score_team2 = ?, status = ? WHERE match_id = ?");
$stmt->execute([(int)$score_team1, (int)$score_team2, $status, $match_id]);
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
