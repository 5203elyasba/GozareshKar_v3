<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Redirect to login if not authenticated
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: index.php");
    exit;
}

// --- Data Retrieval ---
$log_date_jalali = trim($_POST["log_date"]);
$user_id = $_SESSION["id"];

$work_start_times = $_POST["start_time"] ?? [];
$work_end_times = $_POST["end_time"] ?? [];
$break_start_times = $_POST["break_start_time"] ?? [];
$break_end_times = $_POST["break_end_time"] ?? [];

// --- Validation ---
if (empty($log_date_jalali)) {
    die("Date is required.");
}
$gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_jalali);
if ($gregorian_date_obj === false) {
    die("Invalid date format.");
}
$gregorian_date_str = $gregorian_date_obj->format('Y-m-d');

// --- Database Operation (Transaction) ---
try {
    $pdo->beginTransaction();

    // 1. Delete all existing logs for this user on this date
    $delete_sql = "DELETE FROM time_logs WHERE user_id = :user_id AND log_date = :log_date";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str]);

    // 2. Insert the new logs
    $insert_sql = "INSERT INTO time_logs (user_id, log_date, start_time, end_time, log_type) VALUES (:user_id, :log_date, :start_time, :end_time, :log_type)";
    $insert_stmt = $pdo->prepare($insert_sql);

    // Process WORK intervals
    for ($i = 0; $i < count($work_start_times); $i++) {
        $start = $work_start_times[$i];
        $end = $work_end_times[$i];
        if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) continue;
        $insert_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str, ':start_time' => $start, ':end_time' => $end, ':log_type' => 'work']);
    }

    // Process BREAK intervals
    for ($i = 0; $i < count($break_start_times); $i++) {
        $start = $break_start_times[$i];
        $end = $break_end_times[$i];
        if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) continue;
        $insert_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str, ':start_time' => $start, ':end_time' => $end, ':log_type' => 'break']);
    }

    $pdo->commit();
    header("location: reports.php?success=1"); // Redirect to reports page after successful submission

} catch (Exception $e) {
    $pdo->rollBack();
    header("location: index.php?date={$log_date_jalali}&error=db_error");
} finally {
    unset($delete_stmt);
    unset($insert_stmt);
    unset($pdo);
}
?>
