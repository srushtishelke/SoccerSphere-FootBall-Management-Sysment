<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'manager'])) {
    header("Location: dashboard.php");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch all teams for players or manager's teams
if ($role == 'player') {
    $stmt = $pdo->query("SELECT team_id, name FROM teams WHERE 1=1"); // Fetch all teams for joining
} else { // manager
    $stmt = $pdo->prepare("
        SELECT t.team_id, t.name AS team_name, p.first_name, p.last_name
        FROM teams t
        LEFT JOIN team_players tp ON t.team_id = tp.team_id
        LEFT JOIN players p ON tp.player_id = p.player_id
        WHERE t.manager_id = ?
    ");
    $stmt->execute([$user_id]);
}
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($teams) && $role == 'player') {
    $no_teams_message = '<p class="text-warning">No teams available to join. Contact a manager to create a team.</p>';
} elseif (empty($teams) && $role == 'manager') {
    $no_teams_message = '<p class="text-warning">No teams created yet.</p>';
}

// Handle player joining a team
if ($role == 'player' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_id = $_POST['team_id'];
    $player_id_stmt = $pdo->prepare("SELECT player_id FROM players WHERE user_id = ? LIMIT 1");
    $player_id_stmt->execute([$user_id]);
    $player = $player_id_stmt->fetch(PDO::FETCH_ASSOC);

    if ($player) {
        try {
            // Check if already enrolled
            $stmt = $pdo->prepare("SELECT 1 FROM team_players WHERE team_id = ? AND player_id = ?");
            $stmt->execute([$team_id, $player['player_id']]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO team_players (team_id, player_id) VALUES (?, ?)");
                $stmt->execute([$team_id, $player['player_id']]);
                echo '<div class="alert alert-success">Successfully joined the team!</div>';
            } else {
                echo '<div class="alert alert-info">You are already a member of this team.</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error joining team: ' . $e->getMessage() . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Please complete your player profile first.</div>';
    }
}

// Handle manager creating a team (simplified)
if ($role == 'manager' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['team_name'])) {
    $team_name = $_POST['team_name'];
    $tournament_id = $_POST['tournament_id'];

    $stmt = $pdo->prepare("INSERT INTO teams (name, manager_id, tournament_id) VALUES (?, ?, ?)");
    $stmt->execute([$team_name, $user_id, $tournament_id]);
    echo '<div class="alert alert-success">Team created successfully!</div>';
}

?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mt-3"><?php echo $role == 'player' ? 'Join a Team' : 'Team Enrollment'; ?></h2>
        <div class="card mt-3">
            <div class="card-body">
                <?php if ($role == 'player'): ?>
                    <?php if (isset($no_teams_message)) echo $no_teams_message; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="team_id">Select Team:</label>
                            <select class="form-select" name="team_id" id="team_id" required>
                                <option value="">Choose a team</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team['team_id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Join Team</button>
                    </form>
                <?php elseif ($role == 'manager'): ?>
                    <h3>Create a New Team</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="team_name">Team Name:</label>
                            <input type="text" class="form-control" name="team_name" id="team_name" placeholder="Team Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="tournament_id">Tournament:</label>
                            <select class="form-select" name="tournament_id" id="tournament_id" required>
                                <option value="">Select a tournament</option>
                                <?php
                                $stmt = $pdo->prepare("SELECT tournament_id, name FROM tournaments WHERE organizer_id = ?");
                                $stmt->execute([$user_id]);
                                $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tournaments as $tournament): ?>
                                    <option value="<?php echo $tournament['tournament_id']; ?>"><?php echo htmlspecialchars($tournament['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Team</button>
                    </form>

                    <h3 class="mt-4">Your Teams and Players</h3>
                    <?php if (isset($no_teams_message)) echo $no_teams_message; ?>
                    <?php if (!empty($teams)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Team Name</th>
                                    <th>Player Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_team = '';
                                foreach ($teams as $row): 
                                    $player_name = $row['first_name'] && $row['last_name'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : 'No players yet';
                                ?>
                                    <tr>
                                        <?php if ($current_team != $row['team_name']): ?>
                                            <td><?php echo htmlspecialchars($row['team_name']); ?></td>
                                            <?php $current_team = $row['team_name']; ?>
                                        <?php else: ?>
                                            <td></td>
                                        <?php endif; ?>
                                        <td><?php echo $player_name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>