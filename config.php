<?php
// --- Database Credentials ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'employee_tracker');

// --- PDO Database Connection ---
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}

// --- Start Session ---
// It's good practice to start the session in a central config file
// that is included on all secure pages.
session_start();
?>
