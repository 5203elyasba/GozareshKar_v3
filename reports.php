<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// --- Fetch user's specific work hour goal ---
$user_id = $_SESSION['id'];
$user_work_hours_goal = 8; // Default value
try {
    $sql_user = "SELECT daily_hours_goal FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([':user_id' => $user_id]);
    $user_result = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($user_result) {
        $user_work_hours_goal = (float)$user_result['daily_hours_goal'];
    }
} catch (PDOException $e) { /* Silently fail and use default */ }
$standard_work_seconds = $user_work_hours_goal * 3600;

// --- Fetch Time Logs ---
$sql_logs = "SELECT log_date, start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id ORDER BY log_date DESC, start_time ASC";
try {
    $stmt_logs = $pdo->prepare($sql_logs);
    $stmt_logs->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_logs->execute();
    $logs = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die('<div class="alert alert-danger">خطا در دریافت اطلاعات از دیتابیس.</div>'); }

// --- Data Processing ---
$daily_reports = [];
foreach ($logs as $log) {
    $date = $log['log_date'];
    if (!isset($daily_reports[$date])) $daily_reports[$date] = ['intervals' => [], 'total_seconds' => 0];
    $start_ts = strtotime($log['start_time']);
    $end_ts = strtotime($log['end_time']);
    if ($end_ts > $start_ts) {
        $diff = $end_ts - $start_ts;
        $daily_reports[$date]['total_seconds'] += ($log['log_type'] === 'work' ? $diff : -$diff);
        $daily_reports[$date]['intervals'][] = ['start' => date('H:i', $start_ts), 'end' => date('H:i', $end_ts), 'type' => $log['log_type']];
    }
}

$grand_total_deficit_seconds = 0;
foreach ($daily_reports as $report) {
    $grand_total_deficit_seconds += ($report['total_seconds'] - $standard_work_seconds);
}

function format_seconds_to_hours($seconds) {
    $sign = $seconds < 0 ? '-' : '';
    $seconds = abs($seconds);
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    return sprintf("%s%02d:%02d", $sign, $h, $m);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش ساعات کاری</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style> body { background-color: #f8f9fa; } .container { max-width: 900px; } </style>
</head>
<body>
<div class="container mt-5">
    <nav class="navbar navbar-expand-sm navbar-light bg-light mb-4 rounded">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">کاربر: <?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">ثبت/ویرایش گزارش</a></li>
                     <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">پنل مدیریت</a></li>
                    <?php endif; ?>
                </ul>
                <a href="logout.php" class="btn btn-danger">خروج</a>
            </div>
        </div>
    </nav>

    <h2 class="mb-4">گزارش ساعات کاری</h2>
    <div class="card mb-4">
        <div class="card-header fw-bold">خلاصه کل (بر اساس روزی <?php echo $user_work_hours_goal; ?> ساعت)</div>
        <div class="card-body">
            مجموع اضافه کاری / کسری کار شما در کل دوره:
            <?php
                $total_formatted = format_seconds_to_hours($grand_total_deficit_seconds);
                $total_color = $grand_total_deficit_seconds < 0 ? 'text-danger' : 'text-success';
                echo "<strong class=\"fs-5 {$total_color}\">{$total_formatted}</strong>";
            ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header">گزارش روزانه</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover text-center">
                    <thead class="table-dark">
                        <tr><th>تاریخ</th><th>ساعات مفید</th><th>کسری/اضافه کار</th><th>بازه های زمانی</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daily_reports)): ?>
                            <tr><td colspan="4" class="text-center p-4">هیچ گزارشی برای نمایش وجود ندارد.</td></tr>
                        <?php else: foreach ($daily_reports as $date => $report): ?>
                            <tr>
                                <td class="align-middle"><a href="index.php?date=<?php echo JalaliDate::toJalali($date); ?>"><?php echo JalaliDate::toJalali($date); ?></a></td>
                                <td class="align-middle fw-bold"><?php echo format_seconds_to_hours($report['total_seconds']); ?></td>
                                <td class="align-middle">
                                    <?php
                                    $deficit = $report['total_seconds'] - $standard_work_seconds;
                                    echo "<span class='fw-bold " . ($deficit < 0 ? 'text-danger' : 'text-success') . "'>" . format_seconds_to_hours($deficit) . "</span>";
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <?php foreach ($report['intervals'] as $interval) {
                                        echo "<div>{$interval['start']} - {$interval['end']} <span class='badge bg-" . ($interval['type'] === 'work' ? 'success' : 'warning text-dark') . "'>" . ($interval['type'] === 'work' ? 'کاری' : 'استراحت') . "</span></div>";
                                    } ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
