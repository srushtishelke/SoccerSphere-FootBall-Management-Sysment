<?php
// =============================================
// Neon PostgreSQL Connection
// URL: postgresql://neondb_owner:***@ep-purple-art-apr65ix0-pooler.c-7.us-east-1.aws.neon.tech/neondb
// =============================================
$pg_host     = 'ep-purple-art-apr65ix0-pooler.c-7.us-east-1.aws.neon.tech';
$pg_user     = 'neondb_owner';
$pg_password = 'npg_z1nN0SwKWfvZ';
$pg_dbname   = 'neondb';
$pg_port     = 5432;

try {
    // Neon PostgreSQL requires sslmode=require and endpoint option for SNI
    $endpoint = explode('.', $pg_host)[0]; // ep-purple-art-apr65ix0-pooler

    $dsn = "pgsql:"
         . "host=$pg_host;"
         . "port=$pg_port;"
         . "dbname=$pg_dbname;"
         . "sslmode=require;"
         . "options='endpoint=$endpoint'";

    $pdo = new PDO($dsn, $pg_user, $pg_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    error_log("DB Connection Failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>