<?php
require_once 'config.php';

// Role check and authentication
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $daily_hours_goal = (float)$_POST['daily_hours_goal'];

    // Validation
    if (empty($username) || empty($password)) {
        die("Username and password are required.");
    }
    if ($role !== 'employee' && $role !== 'admin') {
        die("Invalid role specified.");
    }
    if ($daily_hours_goal <= 0) {
        $daily_hours_goal = 8;
    }

    // Check if username already exists
    $sql_check = "SELECT id FROM users WHERE username = :username";
    if($stmt_check = $pdo->prepare($sql_check)){
        $stmt_check->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt_check->execute();
        if($stmt_check->rowCount() > 0){
            die("This username is already taken. Please choose another one.");
        }
    }
    unset($stmt_check);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    try {
        $sql_insert = "INSERT INTO users (username, password, full_name, role, daily_hours_goal) VALUES (:username, :password, :full_name, :role, :daily_hours_goal)";
        $stmt_insert = $pdo->prepare($sql_insert);

        $stmt_insert->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':full_name' => $full_name,
            ':role' => $role,
            ':daily_hours_goal' => $daily_hours_goal
        ]);

        // Redirect to admin page with a generic success message that the page checks for
        header("Location: admin.php?success=1");
        exit;

    } catch (PDOException $e) {
        die("Error adding user: " . $e->getMessage());
    }

} else {
    header("Location: admin.php");
    exit;
}
?>
