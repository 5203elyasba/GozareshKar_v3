<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Authentication
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$user_full_name = $_SESSION['full_name'] ?? 'کاربر';
$logs_by_date = [];

try {
    // Fetch all time logs for the user
    $logs_stmt = $pdo->prepare("SELECT log_date, start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id ORDER BY log_date DESC, start_time ASC");
    $logs_stmt->execute(['user_id' => $user_id]);
    $all_logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group logs by date
    foreach ($all_logs as $log) {
        $jalali_date = JalaliDate::toJalali($log['log_date']);
        if (!isset($logs_by_date[$jalali_date])) {
            $logs_by_date[$jalali_date] = ['work_hours' => 0, 'break_minutes' => 0, 'entries' => []];
        }

        $start = new DateTime($log['start_time']);
        $end = new DateTime($log['end_time']);
        $diff_seconds = $end->getTimestamp() - $start->getTimestamp();

        if ($log['log_type'] === 'work') {
            $logs_by_date[$jalali_date]['work_hours'] += $diff_seconds / 3600;
            $logs_by_date[$jalali_date]['entries'][] = date('H:i', $start->getTimestamp()) . ' - ' . date('H:i', $end->getTimestamp());
        } else {
            $logs_by_date[$jalali_date]['break_minutes'] += $diff_seconds / 60;
        }
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارشات دقیق شما</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-5">
        <?php if(file_exists('nav.php')) { require_once 'nav.php'; } ?>

        <h3 class="mb-4">گزارشات دقیق روزانه شما</h3>

        <?php if (empty($logs_by_date)): ?>
            <div class="alert alert-info">هیچ گزارشی برای شما ثبت نشده است.</div>
        <?php else: ?>
            <div class="accordion" id="reports-accordion">
                <?php foreach ($logs_by_date as $date => $data): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo str_replace('/', '-', $date); ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo str_replace('/', '-', $date); ?>" aria-expanded="false">
                                <div class="w-100 d-flex justify-content-between pe-3">
                                    <strong><?php echo $date; ?></strong>
                                    <span>مجموع ساعت کاری: <?php echo round($data['work_hours'], 2); ?></span>
                                    <span>استراحت: <?php echo round($data['break_minutes']); ?> دقیقه</span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo str_replace('/', '-', $date); ?>" class="accordion-collapse collapse" data-bs-parent="#reports-accordion">
                            <div class="accordion-body">
                                <h6>بازه های زمانی حضور:</h6>
                                <ul>
                                    <?php foreach($data['entries'] as $entry): ?>
                                        <li><?php echo $entry; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
