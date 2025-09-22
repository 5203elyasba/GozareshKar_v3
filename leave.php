<?php
require_once 'config.php';
require_once 'JalaliDate.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// --- Fetch User's Leave Data ---
$annual_leave_total = 0;
$leave_taken = [];
try {
    // Get total annual leave days
    $sql_user = "SELECT annual_leave_days FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([':user_id' => $user_id]);
    $user_result = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($user_result) {
        $annual_leave_total = (int)$user_result['annual_leave_days'];
    }

    // Get leave days already taken
    $sql_leave = "SELECT leave_date, reason FROM leave_logs WHERE user_id = :user_id ORDER BY leave_date DESC";
    $stmt_leave = $pdo->prepare($sql_leave);
    $stmt_leave->execute([':user_id' => $user_id]);
    $leave_taken = $stmt_leave->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching leave data: " . $e->getMessage());
}

$leave_used_count = count($leave_taken);
$leave_remaining = $annual_leave_total - $leave_used_count;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مرخصی</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-4">مدیریت مرخصی</h2>

    <!-- Leave Balance Summary -->
    <div class="row text-center mb-4 g-3">
        <div class="col-md-4"><div class="card"><div class="card-body"><h5 class="card-title">مرخصی کل</h5><p class="fs-4 fw-bold"><?php echo $annual_leave_total; ?> روز</p></div></div></div>
        <div class="col-md-4"><div class="card bg-warning"><div class="card-body"><h5 class="card-title">استفاده شده</h5><p class="fs-4 fw-bold"><?php echo $leave_used_count; ?> روز</p></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white"><div class="card-body"><h5 class="card-title">باقیمانده</h5><p class="fs-4 fw-bold"><?php echo $leave_remaining; ?> روز</p></div></div></div>
    </div>

    <!-- Leave Request Form -->
    <div class="card mb-4">
        <div class="card-header">ثبت درخواست مرخصی جدید</div>
        <div class="card-body">
             <?php if(isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
             <?php if(isset($_GET['success'])): ?><div class="alert alert-success">مرخصی با موفقیت ثبت شد.</div><?php endif; ?>
            <form action="handle_leave_request.php" method="post">
                <div class="row g-2">
                    <div class="col-md">
                        <label for="leave_date" class="form-label">تاریخ مرخصی (با فرمت روز/ماه/سال)</label>
                        <div class="row g-2">
                            <div class="col"><input type="number" class="form-control" name="leave_day" placeholder="روز" min="1" max="31" required></div>
                            <div class="col"><input type="number" class="form-control" name="leave_month" placeholder="ماه" min="1" max="12" required></div>
                            <div class="col"><input type="number" class="form-control" name="leave_year" placeholder="سال" min="1400" required></div>
                        </div>
                    </div>
                    <div class="col-md">
                         <label for="reason" class="form-label">توضیح (اختیاری)</label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                    <div class="col-md-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">ثبت مرخصی</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Leave History -->
    <div class="card">
        <div class="card-header">تاریخچه مرخصی‌های ثبت شده</div>
        <div class="card-body">
            <?php if(empty($leave_taken)): ?>
                <p class="text-center">موردی برای نمایش وجود ندارد.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach($leave_taken as $leave): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo JalaliDate::toJalali($leave['leave_date']); ?>
                            <span class="text-muted small"><?php echo htmlspecialchars($leave['reason']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
     <div class="mt-3"><a href="index.php" class="btn btn-secondary">بازگشت به صفحه اصلی</a></div>
</div>

<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script>
    // This page doesn't use main.js, so we initialize its datepicker here.
    // However, the logic was moved to main.js. Let's link main.js instead for consistency.
</script>
<script src="main.js"></script>
</body>
</html>
