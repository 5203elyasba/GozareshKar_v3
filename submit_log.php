<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Authenticate user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: index.php");
    exit;
}

// --- Data Retrieval & Date Construction ---
$day = str_pad((int)$_POST['log_day'], 2, '0', STR_PAD_LEFT);
$month = str_pad((int)$_POST['log_month'], 2, '0', STR_PAD_LEFT);
$year = (int)$_POST['log_year'];
$log_date_jalali = "{$year}/{$month}/{$day}";

$user_id = $_SESSION["id"];
$work_start_times = $_POST["start_time"] ?? [];
$work_end_times = $_POST["end_time"] ?? [];
$total_break_minutes = (int)($_POST['total_break_minutes'] ?? 0);

// --- Validation ---
if (!checkdate((int)$month, (int)$day, (int)$year)) { // Basic check, not Jalali-aware but good enough for format
    die("Invalid date parts provided. Please ensure day, month, and year are filled correctly.");
}
$gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_jalali);
if ($gregorian_date_obj === false) { die("Invalid Jalali date."); }
$gregorian_date_str = $gregorian_date_obj->format('Y-m-d');

// --- Database Operation (Transaction) ---
try {
    $pdo->beginTransaction();

    // Delete existing logs for this user on this date
    $delete_sql = "DELETE FROM time_logs WHERE user_id = :user_id AND log_date = :log_date";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str]);

    // Insert new work intervals
    $insert_sql = "INSERT INTO time_logs (user_id, log_date, start_time, end_time, log_type) VALUES (:user_id, :log_date, :start_time, :end_time, :log_type)";
    $insert_stmt = $pdo->prepare($insert_sql);
    for ($i = 0; $i < count($work_start_times); $i++) {
        $start = $work_start_times[$i];
        $end = $work_end_times[$i];
        if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) continue;
        $insert_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str, ':start_time' => $start, ':end_time' => $end, ':log_type' => 'work']);
    }

    // Insert total break minutes as a single log entry
    if ($total_break_minutes > 0) {
        $break_start_time = '00:00:00';
        $break_end_time = date('H:i:s', strtotime("+$total_break_minutes minutes", strtotime($break_start_time)));
        $insert_stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str, ':start_time' => $break_start_time, ':end_time' => $break_end_time, ':log_type' => 'break']);
    }

    $pdo->commit();
    header("location: reports.php?success=1");

} catch (Exception $e) {
    $pdo->rollBack();
    header("location: index.php?date={$log_date_jalali}&error=db_error");
}
?>
