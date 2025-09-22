<?php
require_once 'config.php';

// Role check and authentication
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_ids = $_POST['user_ids'] ?? [];
    $daily_hours = $_POST['daily_hours'] ?? [];

    if (count($user_ids) !== count($daily_hours)) {
        die("Data mismatch. Please try again.");
    }

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE users SET daily_hours_goal = :daily_hours WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);

        for ($i = 0; $i < count($user_ids); $i++) {
            $user_id = (int)$user_ids[$i];
            $hours = (float)$daily_hours[$i];

            // Basic validation: ensure hours are positive, default to 8 otherwise.
            if ($hours <= 0) {
                $hours = 8;
            }

            $stmt->execute([':daily_hours' => $hours, ':user_id' => $user_id]);
        }

        $pdo->commit();
        header("Location: admin.php?success=1");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error updating records: " . $e->getMessage());
    }
} else {
    // Redirect if not a POST request
    header("Location: admin.php");
    exit;
}
?>
