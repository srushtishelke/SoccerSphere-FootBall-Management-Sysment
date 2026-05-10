<?php
// Secure PostgreSQL Database Connection using Environment Variables
$host     = getenv('DB_HOST') ?: 'ep-purple-art-apr65ix0-pooler.c-7.us-east-1.aws.neon.tech';
$user     = getenv('DB_USER') ?: 'neondb_owner';
$password = getenv('DB_PASS') ?: 'npg_z1nN0SwKWfvZ';
$dbname   = getenv('DB_NAME') ?: 'neondb';

try {
    $dsn = "pgsql:host=$host;dbname=$dbname;sslmode=require";

    // Neon requires endpoint in options for older libpq (SNI fix)
    $endpoint = explode('.', $host)[0];
    $dsn .= ";options='endpoint=$endpoint'";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>