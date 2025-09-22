<?php
require_once 'config.php';

// Role check and authentication
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    die("Access Denied.");
}

// Fetch all users
$users = [];
try {
    $sql = "SELECT id, username, full_name, role, daily_hours_goal, annual_leave_days FROM users ORDER BY id";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-4">
        <h2 class="mb-4">پنل مدیریت کاربران</h2>
        <p>در این صفحه می‌توانید تنظیمات مربوط به هر کاربر را مدیریت کرده و گزارش‌های آن‌ها را مشاهده کنید.</p>

        <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-3">
             <a href="add_user.php" class="btn btn-success">افزودن کاربر جدید +</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">تغییرات با موفقیت ذخیره شد.</div>
        <?php endif; ?>

        <form action="update_users.php" method="post">
            <div class="table-responsive">
                <table class="table table-striped table-bordered small">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>نام کاربری</th>
                            <th>نام کامل</th>
                            <th>نقش</th>
                            <th>ساعات کاری</th>
                            <th>مرخصی سالانه</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <input type="hidden" name="user_ids[]" value="<?php echo $user['id']; ?>">
                                <input type="number" step="0.1" class="form-control form-control-sm" name="daily_hours[]" value="<?php echo htmlspecialchars($user['daily_hours_goal']); ?>" required>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="annual_leave[]" value="<?php echo htmlspecialchars($user['annual_leave_days']); ?>" required>
                            </td>
                            <td>
                                <a href="reports.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">مشاهده گزارش</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <a href="index.php" class="btn btn-secondary">بازگشت به صفحه اصلی</a>
                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
