<?php
// تنظیمات اتصال به پایگاه داده
$host = 'localhost';
$dbname = 'company_management';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    // ایجاد اتصال PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    
    // تنظیم حالت خطایابی
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // اضافه کردن فیلدهای جدید به جدول personnel
    $pdo->exec("ALTER TABLE personnel 
                ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female') NOT NULL DEFAULT 'male' AFTER last_name,
                ADD COLUMN IF NOT EXISTS email VARCHAR(100) NOT NULL UNIQUE AFTER gender,
                ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) NOT NULL AFTER email,
                ADD COLUMN IF NOT EXISTS username VARCHAR(50) NOT NULL UNIQUE AFTER mobile,
                ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL AFTER username");

    // اضافه کردن فیلد role_id به جدول personnel
    $pdo->exec("ALTER TABLE personnel 
                ADD COLUMN IF NOT EXISTS role_id INT NOT NULL AFTER company_id,
                ADD FOREIGN KEY IF NOT EXISTS (role_id) REFERENCES roles(id) ON DELETE CASCADE");

    // اضافه کردن فیلد company_id به جدول content_audiences
    $pdo->exec("ALTER TABLE content_audiences 
                ADD COLUMN IF NOT EXISTS company_id INT NOT NULL AFTER id,
                ADD FOREIGN KEY IF NOT EXISTS (company_id) REFERENCES companies(id) ON DELETE CASCADE");

    // اضافه کردن فیلدهای جدید به جدول contents
    $pdo->exec("ALTER TABLE contents 
                ADD COLUMN IF NOT EXISTS scenario TEXT NULL AFTER description,
                ADD COLUMN IF NOT EXISTS publish_date DATE NOT NULL AFTER scenario,
                ADD COLUMN IF NOT EXISTS publish_time TIME NOT NULL AFTER publish_date,
                ADD COLUMN IF NOT EXISTS production_status_id INT NOT NULL AFTER publish_time,
                ADD COLUMN IF NOT EXISTS publish_status_id INT NOT NULL AFTER production_status_id,
                ADD FOREIGN KEY IF NOT EXISTS (production_status_id) REFERENCES content_production_statuses(id),
                ADD FOREIGN KEY IF NOT EXISTS (publish_status_id) REFERENCES content_publish_statuses(id)");

    echo "به‌روزرسانی‌های پایگاه داده با موفقیت انجام شد.";

} catch(PDOException $e) {
    die("خطا در به‌روزرسانی پایگاه داده: " . $e->getMessage());
} 