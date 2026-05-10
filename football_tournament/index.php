<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';
$current_date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT t.tournament_id, t.name, t.start_date, t.end_date, u.username AS organizer FROM tournaments t JOIN users u ON t.organizer_id = u.user_id");
$stmt->execute();
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <div class="jumbotron text-white text-center py-5 mb-4 rounded" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/football-bg.jpg') no-repeat center center; background-size: cover;">
            <h1 class="display-4">Welcome to SoccerSphere</h1>
            <p class="lead">Organize, join, and track soccer tournaments with ease.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-light btn-lg mt-3 me-2">Get Started</a>
                <a href="login.php" class="btn btn-outline-light btn-lg mt-3">Login</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-light btn-lg mt-3">Go to Dashboard</a>
            <?php endif; ?>
        </div>
        <h2 class="text-center mb-4">Tournaments</h2>
        <?php if (empty($tournaments)): ?>
            <p class="text-muted text-center">No tournaments available at the moment. Check back later!</p>
        <?php else: ?>
            <div class="row justify-content-center">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($tournament['name']); ?></h5>
                                <p class="card-text">
                                    <strong>Organizer:</strong> <?php echo htmlspecialchars($tournament['organizer']); ?><br>
                                    <strong>Start Date:</strong> <?php echo $tournament['start_date']; ?><br>
                                    <strong>End Date:</strong> <?php echo $tournament['end_date']; ?><br>
                                    <strong>Status:</strong> 
                                    <?php 
                                        if ($tournament['start_date'] > $current_date) {
                                            echo "Upcoming";
                                        } elseif ($tournament['end_date'] < $current_date) {
                                            echo "Completed";
                                        } else {
                                            echo "Active";
                                        }
                                    ?>
                                </p>
                                <a href="tournament_details.php?id=<?php echo $tournament['tournament_id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <h2 class="text-center mt-5 mb-4">Features</h2>
        <div class="row text-center justify-content-center">
            <div class="col-md-4 mb-4"><div class="card"><div class="card-body"><h5 class="card-title">Tournament Management</h5><p class="card-text">Create and manage tournaments with custom rules and schedules.</p></div></div></div>
            <div class="col-md-4 mb-4"><div class="card"><div class="card-body"><h5 class="card-title">Live Scores</h5><p class="card-text">Real-time updates on match scores and standings.</p></div></div></div>
            <div class="col-md-4 mb-4"><div class="card"><div class="card-body"><h5 class="card-title">Player Profiles</h5><p class="card-text">Track your stats and tournament history.</p></div></div></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>