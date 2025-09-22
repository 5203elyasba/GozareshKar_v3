<?php
// config.php is assumed to be included by the parent file before this partial.
?>
<nav class="navbar navbar-expand-lg navbar-light mb-4 rounded">
     <div class="container-fluid">
        <a class="navbar-brand" href="index.php">ثبت گزارش روزانه</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="reports.php">گزارش‌ها</a></li>
                <li class="nav-item"><a class="nav-link" href="leave.php">مرخصی</a></li>
                <li class="nav-item"><a class="nav-link" href="change_password.php">تغییر رمز</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin.php">پنل مدیریت</a></li>
                <?php endif; ?>
            </ul>
            <a href="logout.php" class="btn btn-danger">خروج</a>
        </div>
    </div>
</nav>
