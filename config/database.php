<?php
/*
Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password).
*/
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Default XAMPP password is empty
define('DB_NAME', 'employee_tracker');

/* Attempt to connect to MySQL database */
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset to utf8mb4 for full Persian character support
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
