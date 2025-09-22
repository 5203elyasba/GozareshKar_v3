<?php
// Initialize the session and check if the user is logged in
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../public/login.php");
    exit;
}

// Include dependencies
require_once "../config/database.php";
require_once "../includes/JalaliDate.php";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Data Retrieval ---
    $log_date_str = trim($_POST["log_date"]);
    $user_id = $_SESSION["id"];

    // Work intervals
    $work_start_times = $_POST["start_time"] ?? [];
    $work_end_times = $_POST["end_time"] ?? [];

    // Break intervals
    $break_start_times = $_POST["break_start_time"] ?? [];
    $break_end_times = $_POST["break_end_time"] ?? [];

    // --- Validation ---
    if (empty($log_date_str) || empty($work_start_times) || count($work_start_times) !== count($work_end_times) || count($break_start_times) !== count($break_end_times)) {
        header("location: ../public/index.php?error=validation");
        exit;
    }

    $gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_str);
    if ($gregorian_date_obj === false) {
        header("location: ../public/index.php?error=invalid_date");
        exit;
    }
    $gregorian_date_str = $gregorian_date_obj->format('Y-m-d');

    // --- Database Insertion ---
    $sql = "INSERT INTO time_logs (user_id, log_date, start_time, end_time, log_type) VALUES (:user_id, :log_date, :start_time, :end_time, :log_type)";

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($sql);

        // Process WORK intervals
        for ($i = 0; $i < count($work_start_times); $i++) {
            $start = $work_start_times[$i];
            $end = $work_end_times[$i];
            if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) {
                throw new Exception("Invalid work interval.");
            }
            $stmt->execute([
                ':user_id' => $user_id,
                ':log_date' => $gregorian_date_str,
                ':start_time' => $start,
                ':end_time' => $end,
                ':log_type' => 'work'
            ]);
        }

        // Process BREAK intervals
        for ($i = 0; $i < count($break_start_times); $i++) {
            $start = $break_start_times[$i];
            $end = $break_end_times[$i];
            if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) {
                // If a break interval is invalid, we can choose to ignore it or fail the whole transaction.
                // For now, we'll ignore it to be more user-friendly.
                continue;
            }
            $stmt->execute([
                ':user_id' => $user_id,
                ':log_date' => $gregorian_date_str,
                ':start_time' => $start,
                ':end_time' => $end,
                ':log_type' => 'break'
            ]);
        }

        $pdo->commit();
        header("location: ../public/index.php?success=1");

    } catch (Exception $e) {
        $pdo->rollBack();
        // error_log($e->getMessage());
        header("location: ../public/index.php?error=db_error");
    } finally {
        unset($stmt);
        unset($pdo);
    }

} else {
    header("location: ../public/index.php");
    exit;
}
?>
