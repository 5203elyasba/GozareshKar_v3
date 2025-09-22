<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['id'];
    $leave_date_jalali = trim($_POST['leave_date']);
    $reason = trim($_POST['reason']);

    // --- Validation ---
    if (empty($leave_date_jalali)) {
        header("Location: leave.php?error=Date is required.");
        exit;
    }

    $gregorian_date_obj = JalaliDate::fromJalaliToDateTime($leave_date_jalali);
    if ($gregorian_date_obj === false) {
        header("Location: leave.php?error=Invalid date format.");
        exit;
    }
    $gregorian_date_str = $gregorian_date_obj->format('Y-m-d');

    try {
        // Check if leave already requested for this date
        $sql_check = "SELECT id FROM leave_logs WHERE user_id = :user_id AND leave_date = :leave_date";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':user_id' => $user_id, ':leave_date' => $gregorian_date_str]);
        if ($stmt_check->rowCount() > 0) {
            header("Location: leave.php?error=Leave already requested for this date.");
            exit;
        }

        // Insert the new leave log
        $sql_insert = "INSERT INTO leave_logs (user_id, leave_date, reason) VALUES (:user_id, :leave_date, :reason)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            ':user_id' => $user_id,
            ':leave_date' => $gregorian_date_str,
            ':reason' => $reason
        ]);

        header("Location: leave.php?success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: leave.php?error=Database error: " . $e->getMessage());
        exit;
    }

} else {
    header("Location: leave.php");
    exit;
}
?>
