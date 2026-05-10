<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle tournament creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_tournament'])) {
    $name = $_POST['name'];
    $rules = $_POST['rules'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $pdo->prepare("INSERT INTO tournaments (name, organizer_id, rules, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $user_id, $rules, $start_date, $end_date]);
    echo '<div class="alert alert-success">Tournament created successfully!</div>';
}

// Handle tournament deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_tournament'])) {
    $tournament_id = $_POST['tournament_id'];

    // Verify ownership or admin role
    $stmt = $pdo->prepare("SELECT organizer_id FROM tournaments WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tournament && ($tournament['organizer_id'] == $user_id || $_SESSION['role'] == 'admin')) {
        $stmt = $pdo->prepare("DELETE FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);
        echo '<div class="alert alert-success">Tournament deleted successfully!</div>';
    } else {
        echo "<div class='alert alert-danger'>You don't have permission to delete this tournament.</div>";
    }
}

// Fetch existing tournaments for the user (or all for admin)
if ($_SESSION['role'] == 'admin') {
    $stmt = $pdo->query("SELECT t.tournament_id, t.name, t.start_date, t.end_date, u.username AS organizer 
                         FROM tournaments t 
                         JOIN users u ON t.organizer_id = u.user_id");
} else {
    $stmt = $pdo->prepare("SELECT tournament_id, name, start_date, end_date 
                           FROM tournaments 
                           WHERE organizer_id = ?");
    $stmt->execute([$user_id]);
}
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mt-3">Tournament Management</h2>
        <div class="card mt-3">
            <div class="card-body">
                <!-- Create Tournament Form -->
                <h3>Create a New Tournament</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="name">Tournament Name:</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Tournament Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="rules">Rules:</label>
                        <textarea class="form-control" name="rules" id="rules" placeholder="Tournament Rules" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="start_date">Start Date:</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date">End Date:</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" required>
                    </div>
                    <button type="submit" name="create_tournament" class="btn btn-primary w-100">Create Tournament</button>
                </form>

                <!-- Delete Tournament Section -->
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
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <th>Organizer</th>
                                <?php endif; ?>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tournaments as $tournament): ?>
                                <tr>
                                    <td><?php echo $tournament['tournament_id']; ?></td>
                                    <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                                    <td><?php echo $tournament['start_date']; ?></td>
                                    <td><?php echo $tournament['end_date']; ?></td>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <td><?php echo htmlspecialchars($tournament['organizer']); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this tournament?');">
                                            <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                            <button type="submit" name="delete_tournament" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
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