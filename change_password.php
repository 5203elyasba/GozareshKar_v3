<?php
require_once 'config.php';

// Redirect to login if not authenticated
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر رمز عبور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style> body { background-color: #f8f9fa; } .container { max-width: 600px; } </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">تغییر رمز عبور</h2>
        <div class="card">
            <div class="card-body">
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">رمز عبور با موفقیت تغییر کرد.</div>
                <?php endif; ?>
                <form action="handle_change_password.php" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">رمز عبور فعلی</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">رمز عبور جدید</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تکرار رمز عبور جدید</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                         <a href="index.php" class="btn btn-secondary">بازگشت</a>
                        <button type="submit" class="btn btn-primary">تغییر رمز</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
