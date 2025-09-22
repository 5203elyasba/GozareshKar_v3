<?php
require_once '../templates/header.php';
require_once '../config/database.php';
require_once '../includes/JalaliDate.php';

// --- Data Fetching ---
$user_id = $_SESSION['id'];
$sql = "SELECT log_date, start_time, end_time FROM time_logs WHERE user_id = :user_id ORDER BY log_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // A simple error handling
    die('<div class="alert alert-danger">خطا در دریافت اطلاعات از دیتابیس.</div>');
}


// --- Data Processing ---
$daily_reports = [];
foreach ($logs as $log) {
    $date = $log['log_date'];
    if (!isset($daily_reports[$date])) {
        $daily_reports[$date] = [
            'intervals' => [],
            'total_seconds' => 0
        ];
    }
    // Using strtotime is simple but effective for TIME format
    $start_ts = strtotime($log['start_time']);
    $end_ts = strtotime($log['end_time']);

    // Ensure end is after start
    if ($end_ts > $start_ts) {
        $diff = $end_ts - $start_ts;
        $daily_reports[$date]['intervals'][] = [
            'start' => date('H:i', $start_ts),
            'end' => date('H:i', $end_ts)
        ];
        $daily_reports[$date]['total_seconds'] += $diff;
    }
}

// Standard work hours (8 hours in seconds)
define('STANDARD_WORK_SECONDS', 8 * 3600);

// Helper function to format seconds into a H:i format (e.g., 08:30)
function format_seconds_to_hours($seconds) {
    $sign = $seconds < 0 ? '-' : '';
    $seconds = abs($seconds);
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    return sprintf("%s%02d:%02d", $sign, $h, $m);
}
?>

<h2 class="mb-4">گزارش ساعات کاری</h2>

<div class="card">
    <div class="card-header">
        خلاصه گزارش روزانه
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th>تاریخ</th>
                        <th>مجموع ساعات کاری</th>
                        <th>کسری / اضافه کار (نسبت به ۸ ساعت)</th>
                        <th>بازه های زمانی ثبت شده</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daily_reports)): ?>
                        <tr>
                            <td colspan="4" class="text-center p-4">هیچ گزارشی برای نمایش وجود ندارد. لطفاً ابتدا ساعات کاری خود را ثبت کنید.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daily_reports as $date => $report): ?>
                            <tr>
                                <td class="align-middle"><?php echo JalaliDate::toJalali($date); ?></td>
                                <td class="align-middle fw-bold"><?php echo format_seconds_to_hours($report['total_seconds']); ?></td>
                                <td class="align-middle">
                                    <?php
                                    $deficit = $report['total_seconds'] - STANDARD_WORK_SECONDS;
                                    $deficit_formatted = format_seconds_to_hours($deficit);
                                    $color = $deficit < 0 ? 'text-danger' : 'text-success';
                                    echo "<span class='fw-bold {$color}'>{$deficit_formatted}</span>";
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <?php
                                    $intervals_str = [];
                                    foreach ($report['intervals'] as $interval) {
                                        $intervals_str[] = "{$interval['start']} - {$interval['end']}";
                                    }
                                    echo implode('<br>', $intervals_str);
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
