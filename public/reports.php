<?php
require_once '../templates/header.php';
require_once '../config/database.php';
require_once '../includes/JalaliDate.php';

// --- Fetch Settings ---
$daily_work_hours_standard = 8; // Default value
try {
    $sql_settings = "SELECT setting_value FROM settings WHERE setting_key = 'daily_work_hours_standard'";
    $stmt_settings = $pdo->query($sql_settings);
    $result = $stmt_settings->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $daily_work_hours_standard = (float)$result['setting_value'];
    }
} catch (PDOException $e) { /* Silently fail and use default */ }
$standard_work_seconds = $daily_work_hours_standard * 3600;

// --- Fetch Time Logs ---
$user_id = $_SESSION['id'];
$sql = "SELECT log_date, start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id ORDER BY log_date DESC, start_time ASC";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die('<div class="alert alert-danger">خطا در دریافت اطلاعات از دیتابیس.</div>'); }

// --- Data Processing ---
$daily_reports = [];
$grand_total_deficit_seconds = 0;
foreach ($logs as $log) {
    $date = $log['log_date'];
    if (!isset($daily_reports[$date])) {
        $daily_reports[$date] = ['intervals' => [], 'total_seconds' => 0];
    }

    $start_ts = strtotime($log['start_time']);
    $end_ts = strtotime($log['end_time']);

    if ($end_ts > $start_ts) {
        $diff = $end_ts - $start_ts;
        // Add duration for 'work', subtract for 'break'
        $daily_reports[$date]['total_seconds'] += ($log['log_type'] === 'work' ? $diff : -$diff);

        $daily_reports[$date]['intervals'][] = [
            'start' => date('H:i', $start_ts),
            'end' => date('H:i', $end_ts),
            'type' => $log['log_type']
        ];
    }
}

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

<h2 class="mb-4">گزارش ساعات کاری</h2>

<div class="card mb-4">
    <div class="card-header fw-bold">خلاصه کل</div>
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
                    <tr>
                        <th>تاریخ</th>
                        <th>مجموع ساعات مفید</th>
                        <th>کسری / اضافه کار</th>
                        <th>بازه های زمانی ثبت شده</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daily_reports)): ?>
                        <tr><td colspan="4" class="text-center p-4">هیچ گزارشی برای نمایش وجود ندارد.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daily_reports as $date => $report): ?>
                            <tr>
                                <td class="align-middle"><?php echo JalaliDate::toJalali($date); ?></td>
                                <td class="align-middle fw-bold"><?php echo format_seconds_to_hours($report['total_seconds']); ?></td>
                                <td class="align-middle">
                                    <?php
                                    $deficit = $report['total_seconds'] - $standard_work_seconds;
                                    $deficit_formatted = format_seconds_to_hours($deficit);
                                    $color = $deficit < 0 ? 'text-danger' : 'text-success';
                                    echo "<span class='fw-bold {$color}'>{$deficit_formatted}</span>";
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <?php
                                    foreach ($report['intervals'] as $interval) {
                                        if ($interval['type'] === 'work') {
                                            echo "<div>{$interval['start']} - {$interval['end']} <span class='badge bg-success'>کاری</span></div>";
                                        } else {
                                            echo "<div>{$interval['start']} - {$interval['end']} <span class='badge bg-warning text-dark'>استراحت</span></div>";
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
