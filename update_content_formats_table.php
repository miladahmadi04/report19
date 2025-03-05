<?php
require_once 'config/db_connect.php';

try {
    // غیرفعال کردن بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // اضافه کردن ستون content_format_id به جدول contents
    $pdo->exec("ALTER TABLE contents ADD COLUMN content_format_id INT NULL AFTER publish_status_id");
    $pdo->exec("ALTER TABLE contents ADD FOREIGN KEY (content_format_id) REFERENCES content_formats(id) ON DELETE RESTRICT");

    // فعال کردن مجدد بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "ستون content_format_id با موفقیت به جدول contents اضافه شد.\n";
    
} catch(PDOException $e) {
    die("خطا در به‌روزرسانی جدول: " . $e->getMessage());
} 