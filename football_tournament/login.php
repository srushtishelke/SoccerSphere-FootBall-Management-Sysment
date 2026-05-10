<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Invalid username or password.</div>";
        }
    } elseif (isset($_POST['forgot_password'])) {
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $token = bin2hex(random_bytes(32)); // Generate a secure token
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour
            $stmt = $pdo->prepare("INSERT INTO reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
            $stmt->execute([$user['user_id'], $token, $expiry]);
            // Simulate email (replace with SMTP in production)
            $reset_link = "http://localhost/football_tournament/reset_password.php?token=" . $token;
            $subject = "Password Reset for SoccerSphere";
            $message = "Click this link to reset your password: $reset_link\nToken expires in 1 hour.";
            $headers = "From: no-reply@soccersphere.com";
            // Uncomment and configure below for real email (requires mail server or SMTP)
            // mail($email, $subject, $message, $headers);
            $msg = "<div class='alert alert-success'>A password reset link has been sent to $email. (Simulated for demo)</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Email not found.</div>";
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Login</h2>
                <?php echo $msg; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Login</button>
                    <div class="text-center">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Enter your email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" name="forgot_password" class="btn btn-primary">Send Reset Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>