<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>
<div class="row">
    <div class="col-md-12">
        <h2>Admin Dashboard</h2>
        <div class="card">
            <div class="card-body">
                <h3>Manage Users</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int)$user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
