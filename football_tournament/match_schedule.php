<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'manager') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournament_id = $_POST['tournament_id'] ?? '';
    $team1_id = $_POST['team1_id'] ?? '';
    $team2_id = $_POST['team2_id'] ?? '';
    $match_date = $_POST['match_date'] ?? '';
    $venue = $_POST['venue'] ?? '';

    if (empty($match_date)) {
        $error = "Please fill out the match date field.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO matches (tournament_id, team1_id, team2_id, match_date, venue) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tournament_id, $team1_id, $team2_id, $match_date, $venue]);
            echo '<div class="alert alert-success">Match scheduled!</div>';
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error scheduling match: ' . $e->getMessage() . '</div>';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM tournaments WHERE organizer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tournaments = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM teams");
$teams = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h2>Schedule a Match</h2>
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="tournament_id">Tournament:</label>
                        <select class="form-select" name="tournament_id" id="tournament_id" required>
                            <?php foreach ($tournaments as $tournament): ?>
                                <option value="<?php echo $tournament['tournament_id']; ?>"><?php echo $tournament['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="team1_id">Team 1:</label>
                        <select class="form-select" name="team1_id" id="team1_id" required>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['team_id']; ?>"><?php echo $team['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="team2_id">Team 2:</label>
                        <select class="form-select" name="team2_id" id="team2_id" required>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['team_id']; ?>"><?php echo $team['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="match_date">Match Date:</label>
                        <input type="datetime-local" class="form-control" name="match_date" id="match_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="venue">Venue:</label>
                        <input type="text" class="form-control" name="venue" id="venue" placeholder="Venue" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Schedule Match</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>