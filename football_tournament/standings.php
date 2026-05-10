<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$tournament_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tournament_id <= 0) {
    die('Invalid tournament ID');
}

// Fetch tournament details
$stmt = $pdo->prepare("SELECT name, organizer_id, start_date, end_date, status FROM tournaments WHERE tournament_id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    die('Tournament not found');
}

// Fetch organizer name
$organizer_stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$organizer_stmt->execute([$tournament['organizer_id']]);
$organizer_name = $organizer_stmt->fetchColumn();

// Fetch matches for this tournament
$stmt = $pdo->prepare("SELECT m.match_id, m.team1_id, m.team2_id, t1.name AS team1, t2.name AS team2, m.score_team1, m.score_team2, m.status, r.winner_id 
                       FROM matches m 
                       JOIN teams t1 ON m.team1_id = t1.team_id 
                       JOIN teams t2 ON m.team2_id = t2.team_id 
                       LEFT JOIN results r ON m.match_id = r.match_id 
                       WHERE m.tournament_id = ?");
$stmt->execute([$tournament_id]);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate standings (points: 3 for win, 1 for draw, 0 for loss)
$standings = [];
foreach ($matches as $match) {
    if ($match['status'] !== 'completed') continue; // Only count completed matches
    $team1 = $match['team1'];
    $team2 = $match['team2'];
    $team1_score = $match['score_team1'];
    $team2_score = $match['score_team2'];
    $winner_id = $match['winner_id'];

    if (!isset($standings[$team1])) $standings[$team1] = ['points' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0];
    if (!isset($standings[$team2])) $standings[$team2] = ['points' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0];

    if ($team1_score > $team2_score) {
        $standings[$team1]['wins']++;
        $standings[$team1]['points'] += 3;
        $standings[$team2]['losses']++;
    } elseif ($team1_score < $team2_score) {
        $standings[$team2]['wins']++;
        $standings[$team2]['points'] += 3;
        $standings[$team1]['losses']++;
    } else {
        $standings[$team1]['draws']++;
        $standings[$team2]['draws']++;
        $standings[$team1]['points'] += 1;
        $standings[$team2]['points'] += 1;
    }
}
uasort($standings, fn($a, $b) => $b['points'] <=> $a['points']); // Sort by points descending
?>

<div class="container my-5">
    <h2 class="text-center mt-3"><?php echo htmlspecialchars($tournament['name']); ?> Details</h2>
    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Organizer:</strong> <?php echo htmlspecialchars($organizer_name); ?></p>
            <p><strong>Start Date:</strong> <?php echo $tournament['start_date']; ?></p>
            <p><strong>End Date:</strong> <?php echo $tournament['end_date']; ?></p>
            <p><strong>Status:</strong> <?php echo $tournament['status']; ?></p>

            <h4 class="mt-4">Match Results</h4>
            <?php if (empty($matches)): ?>
                <p>No matches available.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team 1</th>
                            <th>Score</th>
                            <th>Team 2</th>
                            <th>Status</th>
                            <th>Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($match['team1']); ?></td>
                                <td><?php echo $match['score_team1'] . ' - ' . $match['score_team2']; ?></td>
                                <td><?php echo htmlspecialchars($match['team2']); ?></td>
                                <td><?php echo $match['status']; ?></td>
                                <td><?php echo $match['winner_id'] ? ($match['team1_id'] == $match['winner_id'] ? $match['team1'] : $match['team2']) : 'Draw'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h4 class="mt-4">League Standings</h4>
            <?php if (empty($standings)): ?>
                <p>No standings available yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Points</th>
                            <th>Wins</th>
                            <th>Draws</th>
                            <th>Losses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($standings as $team => $stats): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($team); ?></td>
                                <td><?php echo $stats['points']; ?></td>
                                <td><?php echo $stats['wins']; ?></td>
                                <td><?php echo $stats['draws']; ?></td>
                                <td><?php echo $stats['losses']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>