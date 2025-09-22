-- =================================================================
-- اسکریپت به‌روزرسانی دیتابیس
-- =================================================================
-- این اسکریپت ساختار دیتابیس شما را برای افزودن قابلیت "مدیریت مرخصی" به‌روز می‌کند.
-- نکته: اگر هنگام اجرای هر یک از دستورات با خطای "Duplicate" (تکراری)
-- مواجه شدید، یعنی آن تغییر قبلاً اعمال شده است و می‌توانید خطا را نادیده بگیرید.
-- =================================================================


-- اضافه کردن ستون برای ذخیره روزهای مرخصی سالانه هر کاربر
ALTER TABLE `users`
ADD COLUMN `annual_leave_days` INT(11) NOT NULL DEFAULT 26 AFTER `daily_hours_goal`;


-- ایجاد جدول جدید برای ثبت تاریخ مرخصی‌های استفاده شده
CREATE TABLE IF NOT EXISTS `leave_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_leave_date` (`user_id`,`leave_date`),
  CONSTRAINT `leave_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
