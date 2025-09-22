<?php
require_once 'config.php';
require_once 'JalaliDate.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ header("location: login.php"); exit; }

$log_date_jalali = $_GET['date'] ?? '';
$work_logs = [];
$total_break_minutes = 0;
$is_editing = !empty($log_date_jalali);

$jalali_today = JalaliDate::toJalali(date('Y-m-d'));
$today_parts = explode('/', $jalali_today);
$log_date_year = $today_parts[0];
$log_date_month = $today_parts[1];
$log_date_day = $today_parts[2];

if ($is_editing) {
    $date_parts = explode('/', $log_date_jalali);
    if(count($date_parts) === 3) {
        $log_date_year = $date_parts[0];
        $log_date_month = $date_parts[1];
        $log_date_day = $date_parts[2];
    }
    $gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_jalali);
    if ($gregorian_date_obj) {
        $gregorian_date_str = $gregorian_date_obj->format('Y-m-d');
        $user_id = $_SESSION['id'];
        $sql = "SELECT start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id AND log_date = :log_date ORDER BY start_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $log) {
            if ($log['log_type'] === 'work') {
                $work_logs[] = ['start' => date('H:i', strtotime($log['start_time'])), 'end' => date('H:i', strtotime($log['end_time']))];
            } else {
                $diff = (new DateTime($log['end_time']))->getTimestamp() - (new DateTime($log['start_time']))->getTimestamp();
                $total_break_minutes += round($diff / 60);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'ویرایش' : 'ثبت'; ?> گزارش</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-4">
        <?php require_once 'nav.php'; ?>

        <div class="card" id="log-form-card">
            <div class="card-body text-center">
                <a href="quick_log.php" class="btn btn-success btn-lg px-5">ورود سریع (ثبت ساعت کنونی)</a>
                <hr>
                <p class="text-muted small">یا فرم زیر را به صورت دستی پر کنید</p>
            </div>
            <div class="card-header fs-5 fw-bold"><?php echo $is_editing ? 'ویرایش گزارش روز ' . htmlspecialchars($log_date_jalali) : 'ثبت گزارش روزانه'; ?></div>
            <div class="card-body">
                <form action="submit_log.php" method="post" id="log-form">
                    <div class="mb-3">
                        <label class="form-label">تاریخ</label>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <div class="custom-number-input"><button type="button" class="btn btn-light btn-decrement" data-input="log_day">-</button><input type="text" inputmode="numeric" class="form-control" name="log_day" value="<?php echo $log_date_day; ?>" required><button type="button" class="btn btn-light btn-increment" data-input="log_day">+</button></div>
                            </div>
                            <div class="col-5">
                                <select class="form-select" name="log_month" required>
                                    <?php for($m=1; $m<=12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php if($log_date_month == $m) echo 'selected'; ?>><?php echo ["فروردین","اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"][$m-1]; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col">
                                <div class="custom-number-input"><button type="button" class="btn btn-light btn-decrement" data-input="log_year">-</button><input type="text" inputmode="numeric" class="form-control" name="log_year" value="<?php echo $log_date_year; ?>" required><button type="button" class="btn btn-light btn-increment" data-input="log_year">+</button></div>
                            </div>
                            <div class="col-auto"><button type="button" id="fetch-date-btn" class="btn btn-secondary">بررسی</button></div>
                            <div class="col-auto"><button type="button" id="log-now-btn" class="btn btn-info">همین الان</button></div>
                        </div>
                    </div>
                    <hr>
                    <h5>زمان های حضور</h5>
                    <div id="time-intervals-container">
                        <?php if (empty($work_logs)): ?>
                            <div class="row g-2 mb-2 align-items-end"><div class="col-md"><label class="form-label">ساعت ورود</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div><div class="col-md"><label class="form-label">ساعت خروج</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div><div class="col-md-auto"></div></div>
                        <?php else: foreach ($work_logs as $log): ?>
                            <div class="row g-2 mb-2 align-items-end"><div class="col-md"><label class="form-label">ساعت ورود</label><input type="text" class="form-control time-input" name="start_time[]" value="<?php echo $log['start']; ?>" required></div><div class="col-md"><label class="form-label">ساعت خروج</label><input type="text" class="form-control time-input" name="end_time[]" value="<?php echo $log['end']; ?>" required></div><div class="col-md-auto"><button type="button" class="btn btn-danger remove-interval">-</button></div></div>
                        <?php endforeach; endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-success mt-2" id="add-interval">افزودن بازه حضور جدید +</button>
                    <hr>
                    <h5>زمان استراحت</h5>
                    <div class="mb-3">
                        <label for="total_break_minutes" class="form-label">مجموع زمان استراحت در این روز (به دقیقه)</label>
                        <input type="number" class="form-control" id="total_break_minutes" name="total_break_minutes" value="<?php echo $total_break_minutes; ?>" placeholder="مثلا: 30">
                    </div>
                    <hr>
                    <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><?php echo $is_editing ? 'ذخیره تغییرات' : 'ثبت گزارش'; ?></button></div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="main.js"></script>
</body>
</html>
