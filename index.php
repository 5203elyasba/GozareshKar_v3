<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// --- Data Fetching for Edit Mode ---
$log_date_jalali = $_GET['date'] ?? '';
$work_logs = [];
$total_break_minutes = 0;
$is_editing = !empty($log_date_jalali);

// Default date values to today
$jalali_today = JalaliDate::toJalali(date('Y-m-d'));
$today_parts = explode('/', $jalali_today);
$log_date_year = $today_parts[0];
$log_date_month = $today_parts[1];
$log_date_day = $today_parts[2];

if ($is_editing) {
    // If editing, overwrite defaults with the date from URL
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
        $all_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_logs as $log) {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-4">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 rounded">
             <div class="container-fluid">
                <a class="navbar-brand" href="index.php">خوش آمدید, <?php echo htmlspecialchars($_SESSION['username']); ?>!</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="main-nav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="reports.php">گزارش‌ها</a></li>
                        <li class="nav-item"><a class="nav-link" href="leave.php">مدیریت مرخصی</a></li>
                        <li class="nav-item"><a class="nav-link" href="change_password.php">تغییر رمز</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?><li class="nav-item"><a class="nav-link" href="admin.php">پنل مدیریت</a></li><?php endif; ?>
                    </ul>
                    <a href="logout.php" class="btn btn-danger">خروج</a>
                </div>
            </div>
        </nav>

        <div class="card" id="log-form-card">
            <div class="card-header fs-5"><?php echo $is_editing ? 'ویرایش گزارش روز ' . htmlspecialchars($log_date_jalali) : 'ثبت گزارش روزانه'; ?></div>
            <div class="card-body">
                <form action="submit_log.php" method="post" id="log-form">
                    <div class="mb-3">
                        <label class="form-label">تاریخ</label>
                        <div class="row g-2 align-items-center">
                            <div class="col"><input type="number" class="form-control" name="log_day" placeholder="روز" min="1" max="31" value="<?php echo $log_date_day; ?>" required></div>
                            <div class="col">
                                <select class="form-select" name="log_month" required>
                                    <option value="1" <?php if($log_date_month == 1) echo 'selected'; ?>>فروردین</option>
                                    <option value="2" <?php if($log_date_month == 2) echo 'selected'; ?>>اردیبهشت</option>
                                    <option value="3" <?php if($log_date_month == 3) echo 'selected'; ?>>خرداد</option>
                                    <option value="4" <?php if($log_date_month == 4) echo 'selected'; ?>>تیر</option>
                                    <option value="5" <?php if($log_date_month == 5) echo 'selected'; ?>>مرداد</option>
                                    <option value="6" <?php if($log_date_month == 6) echo 'selected'; ?>>شهریور</option>
                                    <option value="7" <?php if($log_date_month == 7) echo 'selected'; ?>>مهر</option>
                                    <option value="8" <?php if($log_date_month == 8) echo 'selected'; ?>>آبان</option>
                                    <option value="9" <?php if($log_date_month == 9) echo 'selected'; ?>>آذر</option>
                                    <option value="10" <?php if($log_date_month == 10) echo 'selected'; ?>>دی</option>
                                    <option value="11" <?php if($log_date_month == 11) echo 'selected'; ?>>بهمن</option>
                                    <option value="12" <?php if($log_date_month == 12) echo 'selected'; ?>>اسفند</option>
                                </select>
                            </div>
                            <div class="col"><input type="number" class="form-control" name="log_year" placeholder="سال" min="1400" max="1500" value="<?php echo $log_date_year; ?>" required></div>
                            <div class="col-auto"><button type="button" id="fetch-date-btn" class="btn btn-secondary">بررسی</button></div>
                            <div class="col-auto"><button type="button" id="log-now-btn" class="btn btn-info">همین الان</button></div>
                        </div>
                    </div>
                    <hr>
                    <h5>زمان های حضور</h5>
                    <div id="time-intervals-container">
                        <?php if (empty($work_logs)): ?>
                            <div class="row g-2 mb-2 align-items-end"><div class="col-md"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div><div class="col-md"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div><div class="col-md-auto"></div></div>
                        <?php else: foreach ($work_logs as $log): ?>
                            <div class="row g-2 mb-2 align-items-end"><div class="col-md"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" value="<?php echo $log['start']; ?>" required></div><div class="col-md"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" value="<?php echo $log['end']; ?>" required></div><div class="col-md-auto"><button type="button" class="btn btn-danger remove-interval">-</button></div></div>
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
