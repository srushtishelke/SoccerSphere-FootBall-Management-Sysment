<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch unread notification count
$stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = FALSE");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetchColumn();

// Fetch recent notifications
$stmt = $pdo->prepare("SELECT notification_id, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read when viewed (optional)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    header("Location: dashboard.php");
    exit;
}

// Handle score update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_score'])) {
    $match_id = $_POST['match_id'];
    $score_team1 = $_POST['score_team1'];
    $score_team2 = $_POST['score_team2'];

    if (is_numeric($score_team1) && is_numeric($score_team2) && $score_team1 >= 0 && $score_team2 >= 0) {
        $stmt = $pdo->prepare("UPDATE matches SET score_team1 = ?, score_team2 = ? WHERE match_id = ? AND tournament_id IN (SELECT tournament_id FROM tournaments WHERE organizer_id = ?)");
        $stmt->execute([$score_team1, $score_team2, $match_id, $user_id]);
        $affected_rows = $stmt->rowCount();
        if ($affected_rows > 0) {
            echo "<div class='alert alert-success mt-3'>Scores updated successfully!</div>";
        } else {
            echo "<div class='alert alert-warning mt-3'>You are not authorized to update this match or no changes were made.</div>";
        }
    } else {
        echo "<div class='alert alert-danger mt-3'>Invalid score values. Please enter non-negative numbers.</div>";
    }
}

// Handle match completion - MUST be before any HTML output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_match'])) {
    $match_id = $_POST['match_id'];
    $stmt = $pdo->prepare("UPDATE matches SET status = 'completed' WHERE match_id = ? AND tournament_id IN (SELECT tournament_id FROM tournaments WHERE organizer_id = ?)");
    $stmt->execute([$match_id, $user_id]);
    header("Location: dashboard.php");
    exit;
}

// Manager-specific data
if ($role == 'manager') {
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE organizer_id = ?");
    $stmt->execute([$user_id]);
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT m.match_id, m.match_date, m.venue, m.score_team1, m.score_team2, m.status, 
               t1.name AS team1_name, t2.name AS team2_name, t.name AS tournament_name
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.team_id
        JOIN teams t2 ON m.team2_id = t2.team_id
        JOIN tournaments t ON m.tournament_id = t.tournament_id
        WHERE t.organizer_id = ?
    ");
    $stmt->execute([$user_id]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mt-3">Welcome to Your Dashboard 
            <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </h2>
        <div class="card mt-3">
            <div class="card-body">
                <?php if ($role == 'player'): ?>
                    <a href="player_profile.php" class="btn btn-primary mb-2">View/Edit Profile</a>
                    <a href="team_enrollment.php" class="btn btn-primary mb-2">Join a Team</a>
                <?php elseif ($role == 'manager'): ?>
                    <a href="profile.php" class="btn btn-primary mb-2">View/Edit Profile</a>
                    <a href="create_tournament.php" class="btn btn-primary mb-2">Create Tournament</a>
                    <a href="team_enrollment.php" class="btn btn-primary mb-2">Manage Team</a>
                    <a href="match_schedule.php" class="btn btn-primary mb-2">Schedule Matches</a>
                <?php elseif ($role == 'admin'): ?>
                    <a href="profile.php" class="btn btn-primary mb-2">View/Edit Profile</a>
                    <a href="admin_dashboard.php" class="btn btn-primary mb-2">Admin Dashboard</a>
                <?php endif; ?>

                <!-- Notifications Section -->
                <h3 class="mt-4">Notifications</h3>
                <?php if (empty($notifications)): ?>
                    <p>No notifications yet.</p>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="mark_read" class="btn btn-secondary btn-sm mb-2">Mark All as Read</button>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($notifications as $notif): ?>
                            <li class="list-group-item <?php echo $notif['is_read'] ? '' : 'list-group-item-warning'; ?>">
                                <?php echo htmlspecialchars($notif['message']); ?> 
                                <small>(<?php echo $notif['created_at']; ?>)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($role == 'manager'): ?>
                    <h3 class="mt-4">Your Tournaments</h3>
                    <?php if (empty($tournaments)): ?>
                        <p>No tournaments created yet.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Rules</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <tr>
                                        <td><?php echo $tournament['tournament_id']; ?></td>
                                        <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                                        <td><?php echo $tournament['start_date']; ?></td>
                                        <td><?php echo $tournament['end_date']; ?></td>
                                        <td><?php echo htmlspecialchars($tournament['rules']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <h3 class="mt-4">Scheduled Matches</h3>
                    <?php if (empty($matches)): ?>
                        <p>No matches scheduled yet.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tournament</th>
                                    <th>Team 1</th>
                                    <th>Team 2</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matches as $match): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['team1_name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['team2_name']); ?></td>
                                        <td><?php echo $match['match_date']; ?></td>
                                        <td><?php echo htmlspecialchars($match['venue']); ?></td>
                                        <td>
                                            <?php echo $match['score_team1'] . ' - ' . $match['score_team2']; ?>
                                            <?php if ($match['status'] !== 'completed'): ?>
                                                <form method="POST" style="display:inline;" class="mt-2">
                                                    <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="score_team1" class="form-control" value="<?php echo $match['score_team1'] ?? 0; ?>" min="0" required>
                                                        <span class="input-group-text">-</span>
                                                        <input type="number" name="score_team2" class="form-control" value="<?php echo $match['score_team2'] ?? 0; ?>" min="0" required>
                                                        <button type="submit" name="update_score" class="btn btn-primary btn-sm">Update</button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $match['status']; ?></td>
                                        <td>
                                            <?php if ($match['status'] !== 'completed'): ?>
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal<?php echo $match['match_id']; ?>">Complete</button>
                                                <!-- Modal -->
                                                <div class="modal fade" id="confirmModal<?php echo $match['match_id']; ?>" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="confirmModalLabel">Confirm Match Completion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to mark this match as completed with the current scores?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="POST" style="display:inline;">
                                                                    <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                                                    <button type="submit" name="complete_match" class="btn btn-success">Yes, Complete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
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