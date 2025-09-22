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

    // --- Data Validation ---
    $log_date_str = trim($_POST["log_date"]);
    $start_times = $_POST["start_time"];
    $end_times = $_POST["end_time"];
    $user_id = $_SESSION["id"];

    // Basic validation checks
    if (empty($log_date_str) || empty($start_times) || empty($end_times) || count($start_times) !== count($end_times)) {
        header("location: ../public/index.php?error=validation");
        exit;
    }

    // Convert Jalali date to Gregorian DateTime object
    $gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_str);

    if ($gregorian_date_obj === false) {
        // Handle invalid date format
        header("location: ../public/index.php?error=invalid_date");
        exit;
    }
    $gregorian_date_str = $gregorian_date_obj->format('Y-m-d');

    // Prepare SQL statement for insertion
    $sql = "INSERT INTO time_logs (user_id, log_date, start_time, end_time) VALUES (:user_id, :log_date, :start_time, :end_time)";

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare($sql);

        // Loop through each time interval and insert it into the database
        for ($i = 0; $i < count($start_times); $i++) {
            $start = $start_times[$i];
            $end = $end_times[$i];

            // More validation
            if (empty($start) || empty($end) || strtotime($end) <= strtotime($start)) {
                // If any interval is invalid, roll back and show an error
                $pdo->rollBack();
                header("location: ../public/index.php?error=invalid_interval");
                exit;
            }

            // Bind parameters and execute
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":log_date", $gregorian_date_str, PDO::PARAM_STR);
            $stmt->bindParam(":start_time", $start, PDO::PARAM_STR);
            $stmt->bindParam(":end_time", $end, PDO::PARAM_STR);

            $stmt->execute();
        }

        // If all insertions were successful, commit the transaction
        $pdo->commit();

        // Redirect back with a success message
        header("location: ../public/index.php?success=1");

    } catch (Exception $e) {
        // If an error occurred, roll back the transaction
        $pdo->rollBack();
        // Log the error for debugging: error_log($e->getMessage());
        header("location: ../public/index.php?error=db_error");
    } finally {
        // Close the statement and connection
        unset($stmt);
        unset($pdo);
    }

} else {
    // If not a POST request, redirect to the main page
    header("location: ../public/index.php");
    exit;
}
?>
