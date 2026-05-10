<?php
// Use the environment variable if available (e.g. on Render), otherwise fallback to the provided Neon URL
$db_url = getenv("DATABASE_URL") ?: "postgresql://neondb_owner:npg_z1nN0SwKWfvZ@ep-purple-art-apr65ix0-pooler.c-7.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require";

try {
    $db = parse_url($db_url);
    $dsn = "pgsql:host=" . $db['host'] . ";port=" . (isset($db['port']) ? $db['port'] : 5432) . ";dbname=" . ltrim($db['path'], '/');
    $user = $db['user'];
    $password = $db['pass'];
    // Neon requires sslmode=require and sometimes endpoint options for SNI
    $endpoint = explode('.', $db['host'])[0];
    $dsn .= ";sslmode=require;options='endpoint=$endpoint'";
    
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>