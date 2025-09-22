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
$break_logs = [];
$is_editing = !empty($log_date_jalali);

if ($is_editing) {
    $gregorian_date_obj = JalaliDate::fromJalaliToDateTime($log_date_jalali);
    if ($gregorian_date_obj) {
        $gregorian_date_str = $gregorian_date_obj->format('Y-m-d');
        $user_id = $_SESSION['id'];

        $sql = "SELECT start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id AND log_date = :log_date ORDER BY start_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':log_date' => $gregorian_date_str]);
        $all_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_logs as $log) {
            $log_data = [
                'start' => date('H:i', strtotime($log['start_time'])),
                'end' => date('H:i', strtotime($log['end_time']))
            ];
            if ($log['log_type'] === 'work') {
                $work_logs[] = $log_data;
            } else {
                $break_logs[] = $log_data;
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
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css"/>
    <style> body { background-color: #f8f9fa; } .container { max-width: 800px; } </style>
</head>
<body>
    <div class="container mt-5">
        <nav class="navbar navbar-expand-sm navbar-light bg-light mb-4 rounded">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">خوش آمدید, <?php echo htmlspecialchars($_SESSION['username']); ?>!</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="reports.php">مشاهده گزارش‌ها</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin.php">پنل مدیریت</a></li>
                        <?php endif; ?>
                    </ul>
                    <a href="logout.php" class="btn btn-danger">خروج</a>
                </div>
            </div>
        </nav>

        <h2 class="mb-4"><?php echo $is_editing ? 'ویرایش گزارش روز ' . htmlspecialchars($log_date_jalali) : 'ثبت گزارش جدید'; ?></h2>
        <div class="card" id="log-form-card">
            <div class="card-header"><?php echo $is_editing ? 'ویرایش اطلاعات' : 'فرم ثبت ساعت کاری'; ?></div>
            <div class="card-body">
                <form action="submit_log.php" method="post">
                    <div class="mb-3">
                        <label for="log_date" class="form-label">تاریخ</label>
                        <input type="text" class="form-control" id="log_date" name="log_date" value="<?php echo htmlspecialchars($log_date_jalali); ?>" placeholder="برای انتخاب تاریخ کلیک کنید" required>
                    </div>
                    <hr>
                    <h5>بازه های زمانی کاری</h5>
                    <div id="time-intervals-container">
                        <?php if (empty($work_logs)): ?>
                            <div class="row mb-2"><div class="col"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" placeholder="HH:MM" required></div><div class="col"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" placeholder="HH:MM" required></div><div class="col-auto d-flex align-items-end"></div></div>
                        <?php else: foreach ($work_logs as $log): ?>
                            <div class="row mb-2"><div class="col"><label class="form-label">ساعت شروع</label><input type="text" class="form-control time-input" name="start_time[]" value="<?php echo $log['start']; ?>" required></div><div class="col"><label class="form-label">ساعت پایان</label><input type="text" class="form-control time-input" name="end_time[]" value="<?php echo $log['end']; ?>" required></div><div class="col-auto d-flex align-items-end"><button type="button" class="btn btn-danger remove-interval">-</button></div></div>
                        <?php endforeach; endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-success mt-2" id="add-interval">افزودن بازه کاری +</button>
                    <hr>
                    <h5>زمان‌های استراحت / غیرکاری</h5>
                    <div id="break-intervals-container">
                        <?php foreach ($break_logs as $log): ?>
                            <div class="row mb-2"><div class="col"><label class="form-label">شروع استراحت</label><input type="text" class="form-control time-input" name="break_start_time[]" value="<?php echo $log['start']; ?>"></div><div class="col"><label class="form-label">پایان استراحت</label><input type="text" class="form-control time-input" name="break_end_time[]" value="<?php echo $log['end']; ?>"></div><div class="col-auto d-flex align-items-end"><button type="button" class="btn btn-danger remove-interval">-</button></div></div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-warning mt-2" id="add-break-interval">افزودن زمان استراحت +</button>
                    <hr>
                    <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><?php echo $is_editing ? 'ذخیره تغییرات' : 'ثبت گزارش'; ?></button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="main.js"></script>
</body>
</html>
