<?php require_once '../templates/header.php'; ?>

<h2 class="mb-4">ثبت گزارش روزانه</h2>

<?php
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">گزارش با موفقیت ثبت شد.</div>';
}
if (isset($_GET['error'])) {
    // A more specific error message could be passed in the URL if needed
    echo '<div class="alert alert-danger">خطا در ثبت گزارش. لطفاً همه فیلدها را به درستی پر کنید.</div>';
}
?>

<div class="card">
    <div class="card-header">
      فرم ثبت ساعت کاری
    </div>
    <div class="card-body">
        <form action="../src/handle_time_log.php" method="post">
            <div class="mb-3">
                <label for="log_date" class="form-label">تاریخ</label>
                <input type="text" class="form-control" id="log_date" name="log_date" placeholder="مثال: 1404/05/21" required>
                <div class="form-text">لطفاً تاریخ را به فرمت شمسی (سال/ماه/روز) وارد کنید. در آینده یک تقویم شمسی در اینجا قرار خواهد گرفت.</div>
            </div>

            <hr>

            <h5>بازه های زمانی کاری</h5>
            <p class="form-text text-muted">برای ثبت مرخصی ساعتی، یک بازه جدید برای زمان کاری بعد از بازگشت اضافه کنید.</p>

            <div id="time-intervals-container">
                <!-- Initial time interval row -->
                <div class="row mb-2 time-interval-row">
                    <div class="col">
                        <label class="form-label">ساعت شروع</label>
                        <input type="time" class="form-control" name="start_time[]" required>
                    </div>
                    <div class="col">
                        <label class="form-label">ساعت پایان</label>
                        <input type="time" class="form-control" name="end_time[]" required>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <!-- This column is for the remove button, intentionally left empty for the first row -->
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-success mt-2" id="add-interval">افزودن بازه جدید +</button>

            <hr>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">ثبت گزارش</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
