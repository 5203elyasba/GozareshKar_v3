<?php
require_once 'config.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Role check
if(!isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    die("Access Denied. You must be an admin to view this page.");
}

// Fetch all users
$users = [];
try {
    $sql = "SELECT id, username, full_name, role, daily_hours_goal FROM users ORDER BY id";
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
    <style> body { background-color: #f8f9fa; } .container { max-width: 900px; } </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">پنل مدیریت کاربران</h2>
        <p>در این صفحه می‌توانید ساعات کاری موظفی روزانه برای هر کاربر را تنظیم کنید.</p>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">تغییرات با موفقیت ذخیره شد.</div>
        <?php endif; ?>

        <form action="update_users.php" method="post">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>نام کاربری</th>
                        <th>نام کامل</th>
                        <th>نقش</th>
                        <th>ساعات کاری موظفی</th>
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
                            <input type="number" step="0.1" class="form-control" name="daily_hours[]" value="<?php echo htmlspecialchars($user['daily_hours_goal']); ?>" required>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary">بازگشت</a>
                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
</body>
</html>
