<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

$msg = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT user_id FROM reset_tokens WHERE token = ? AND expiry > NOW()");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($token_data) {
        $user_id = $token_data['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$new_password, $user_id]);
        $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $msg = "<div class='alert alert-success'>Password reset successfully! <a href='login.php'>Login here</a>.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
} elseif ($token) {
    $stmt = $pdo->prepare("SELECT user_id FROM reset_tokens WHERE token = ? AND expiry > NOW()");
    $stmt->execute([$token]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $msg = "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Reset Password</h2>
                <?php echo $msg; ?>
                <?php if (!$msg || strpos($msg, 'success') === false): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>