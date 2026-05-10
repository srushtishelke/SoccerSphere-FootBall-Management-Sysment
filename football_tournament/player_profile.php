<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'player') {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch player profile
$stmt = $pdo->prepare("
    SELECT p.first_name, p.last_name, p.age, p.position, p.contact_no, p.tournament_history, u.email
    FROM players p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.user_id = ?
");
$stmt->execute([$user_id]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch player's player_id explicitly (assuming one player per user)
$stmt = $pdo->prepare("SELECT player_id FROM players WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$player_id_row = $stmt->fetch(PDO::FETCH_ASSOC);
$player_id = $player_id_row ? $player_id_row['player_id'] : null;

// Fetch player stats using the specific player_id
if ($player_id) {
    $stmt = $pdo->prepare("
        SELECT m.match_date, t.name AS tournament_name, ps.goals_scored
        FROM player_stats ps
        JOIN matches m ON ps.match_id = m.match_id
        JOIN tournaments t ON m.tournament_id = t.tournament_id
        WHERE ps.player_id = ?
    ");
    $stmt->execute([$player_id]);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stats = []; // No player_id found, no stats
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $position = $_POST['position'];
    $contact_no = $_POST['contact_no'];

    try {
        // Check if player profile exists
        $stmt = $pdo->prepare("SELECT player_id FROM players WHERE user_id = ?");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE players SET first_name=?, last_name=?, age=?, position=?, contact_no=? WHERE user_id=?");
            $stmt->execute([$first_name, $last_name, $age, $position, $contact_no, $user_id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO players (user_id, first_name, last_name, age, position, contact_no) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $first_name, $last_name, $age, $position, $contact_no]);
        }
        $save_msg = "<div class='alert alert-success'>Profile saved successfully!</div>";
    } catch (PDOException $e) {
        $save_msg = "<div class='alert alert-danger'>Error saving profile: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // Refresh player data
    $stmt = $pdo->prepare("
        SELECT p.first_name, p.last_name, p.age, p.position, p.contact_no, p.tournament_history, u.email
        FROM players p
        JOIN users u ON p.user_id = u.user_id
        WHERE p.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);

    // Refresh player_id after update
    $stmt = $pdo->prepare("SELECT player_id FROM players WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $player_id_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $player_id = $player_id_row ? $player_id_row['player_id'] : null;

    // Refresh stats
    if ($player_id) {
        $stmt = $pdo->prepare("
            SELECT m.match_date, t.name AS tournament_name, ps.goals_scored
            FROM player_stats ps
            JOIN matches m ON ps.match_id = m.match_id
            JOIN tournaments t ON m.tournament_id = t.tournament_id
            WHERE ps.player_id = ?
        ");
        $stmt->execute([$player_id]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mt-3">
            <div class="card-header"><h2>Your Profile</h2></div>
            <div class="card-body">
                <?php if (isset($save_msg)) echo $save_msg; ?>
                <?php if (!$player): ?>
                    <p class="text-danger">Please complete your profile below.</p>
                <?php endif; ?>
                <form method="POST" id="profileForm">
                    <div class="mb-3">
                        <label for="first_name">First Name:</label>
                        <input type="text" class="form-control" name="first_name" id="first_name" 
                               value="<?php echo htmlspecialchars($player['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name">Last Name:</label>
                        <input type="text" class="form-control" name="last_name" id="last_name" 
                               value="<?php echo htmlspecialchars($player['last_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="email" id="email" 
                               value="<?php echo htmlspecialchars($player['email'] ?? ''); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="age">Age:</label>
                        <input type="number" class="form-control" name="age" id="age" 
                               value="<?php echo htmlspecialchars($player['age'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="position">Position:</label>
                        <input type="text" class="form-control" name="position" id="position" 
                               value="<?php echo htmlspecialchars($player['position'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_no">Contact Number:</label>
                        <input type="text" class="form-control" name="contact_no" id="contact_no" 
                               value="<?php echo htmlspecialchars($player['contact_no'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tournament_history">Tournament History:</label>
                        <textarea class="form-control" name="tournament_history" id="tournament_history" disabled>
                            <?php echo htmlspecialchars($player['tournament_history'] ?? ''); ?>
                        </textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>

                <h3 class="mt-4">Your Stats</h3>
                <?php if (empty($stats)): ?>
                    <p>No stats available yet.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Match Date</th>
                                <th>Tournament</th>
                                <th>Goals Scored</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $stat): ?>
                                <tr>
                                    <td><?php echo $stat['match_date']; ?></td>
                                    <td><?php echo htmlspecialchars($stat['tournament_name']); ?></td>
                                    <td><?php echo $stat['goals_scored']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>