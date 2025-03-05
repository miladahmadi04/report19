<?php
require_once 'config/db_connect.php';

try {
    // حذف ستون description اگر وجود دارد
    $stmt = $pdo->prepare("SHOW COLUMNS FROM content_formats LIKE 'description'");
    $stmt->execute();
    $description_exists = $stmt->fetch();

    if ($description_exists) {
        $pdo->exec("ALTER TABLE content_formats DROP COLUMN description");
        echo "ستون description با موفقیت حذف شد.<br>";
    }

    // بررسی وجود ستون is_system
    $stmt = $pdo->prepare("SHOW COLUMNS FROM content_formats LIKE 'is_system'");
    $stmt->execute();
    $system_exists = $stmt->fetch();

    if (!$system_exists) {
        // اضافه کردن ستون is_system
        $pdo->exec("ALTER TABLE content_formats ADD COLUMN is_system TINYINT(1) DEFAULT 0");
        echo "ستون is_system با موفقیت اضافه شد.<br>";

        // به‌روزرسانی فرمت‌های پیش‌فرض
        $defaultFormats = [
            'خلاصه',
            'کامل',
            'تیزر'
        ];

        // حذف فرمت‌های قبلی با همین نام‌ها
        $stmt = $pdo->prepare("DELETE FROM content_formats WHERE name IN (?, ?, ?)");
        $stmt->execute($defaultFormats);

        // افزودن فرمت‌های پیش‌فرض جدید
        $stmt = $pdo->prepare("INSERT INTO content_formats (company_id, name, is_system) VALUES (?, ?, 1)");
        
        // دریافت لیست شرکت‌ها
        $companies = $pdo->query("SELECT id FROM companies")->fetchAll(PDO::FETCH_COLUMN);
        
        // افزودن فرمت‌های پیش‌فرض برای هر شرکت
        foreach ($companies as $companyId) {
            foreach ($defaultFormats as $format) {
                $stmt->execute([$companyId, $format]);
            }
        }
        
        echo "فرمت‌های پیش‌فرض با موفقیت ایجاد شدند.<br>";
    }

    echo "<br>عملیات با موفقیت انجام شد. می‌توانید به صفحه مدیریت فرمت‌ها بازگردید.";
    echo "<br><a href='content_formats.php' class='btn btn-primary'>بازگشت به صفحه فرمت‌ها</a>";

} catch (PDOException $e) {
    echo "خطا در به‌روزرسانی جدول: " . $e->getMessage();
}
?> 