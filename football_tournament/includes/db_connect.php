<?php
// Secure Database Connection using Environment Variables
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'football_tournament';

try {
    // Set secure session parameters before starting the session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    // Hide exact database errors in production
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>