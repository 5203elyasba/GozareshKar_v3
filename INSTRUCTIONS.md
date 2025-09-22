# راهنمای به‌روزرسانی پروژه (نسخه جدید)

سلام مجدد. بر اساس بازخورد شما، فایل راهنما را به‌روز و اسکریپت دیتابیس را هوشمندتر کردم تا اگر چند بار اجرا شود، خطا ندهد.

**لطفاً ابتدا فایل‌های پروژه را با آخرین نسخه‌ای که دریافت می‌کنید، جایگزین کنید.** سپس مراحل زیر را دنبال کنید.

## به‌روزرسانی دیتابیس (روش جدید و امن)

1.  وارد **phpMyAdmin** شوید (`http://localhost/phpmyadmin`).
2.  دیتابیس `employee_tracker` را از منوی سمت چپ انتخاب کنید.
3.  به تب **SQL** بروید.
4.  کد زیر را کپی و اجرا کنید. این کد جدول `settings` را در صورت عدم وجود ایجاد کرده و مقدار اولیه را به آن اضافه می‌کند:

    ```sql
    CREATE TABLE IF NOT EXISTS `settings` (
      `setting_key` varchar(50) NOT NULL,
      `setting_value` text DEFAULT NULL,
      PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('daily_work_hours_standard', '8');
    ```
5.  پس از اجرای موفقیت‌آمیز، **دوباره به تب SQL بروید** و این بار کد زیر را اجرا کنید تا ستون مربوط به زمان استراحت اضافه شود:

    ```sql
    ALTER TABLE `time_logs`
    ADD COLUMN `log_type` ENUM('work','break') NOT NULL DEFAULT 'work' AFTER `end_time`;
    ```
    *   **نکته مهم:** اگر این دستور دوم را اجرا کردید و با خطای "Duplicate column name" مواجه شدید، کاملاً طبیعی است و جای نگرانی نیست. این خطا یعنی این ستون قبلاً اضافه شده است. می‌توانید این خطا را نادیده بگیرید.

**پایان!**

پروژه شما اکنون با آخرین تغییرات آماده استفاده است.
