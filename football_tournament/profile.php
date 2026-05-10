<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect players to their dedicated profile page
if ($_SESSION['role'] == 'player') {
    header("Location: player_profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    try {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$email, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $stmt->execute([$email, $user_id]);
        }
        $msg = "<div class='alert alert-success'>Profile updated successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate email)
            $msg = "<div class='alert alert-danger'>Email already exists!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error updating profile: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch user info
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Your Profile (<?php echo ucfirst($role); ?>)</h2>
            </div>
            <div class="card-body">
                <?php echo $msg; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Username cannot be changed.</small>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password (leave blank to keep current):</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Enter new password">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profile</button>
                    <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
