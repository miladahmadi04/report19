<?php
require_once 'config/db_connect.php';

try {
    // غیرفعال کردن بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // حذف جداول قدیمی اگر وجود دارند
    $pdo->exec("DROP TABLE IF EXISTS content_audience_relations");
    $pdo->exec("DROP TABLE IF EXISTS content_target_audiences");

    // ایجاد جدول مخاطبین
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_audiences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // ایجاد جدول رابطه محتوا و مخاطبین
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_audience_content (
        content_id INT NOT NULL,
        audience_id INT NOT NULL,
        PRIMARY KEY (content_id, audience_id),
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (audience_id) REFERENCES content_audiences(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // دریافت لیست شرکت‌ها
    $stmt = $pdo->query("SELECT id FROM companies");
    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // درج مخاطبین پیش‌فرض برای هر شرکت
    $defaultAudiences = [
        'عموم مردم',
        'جوانان',
        'نوجوانان',
        'کودکان',
        'بزرگسالان',
        'سالمندان',
        'دانشجویان',
        'دانش‌آموزان',
        'متخصصان'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO content_audiences (company_id, name) VALUES (?, ?)");
    foreach ($companies as $companyId) {
        foreach ($defaultAudiences as $audience) {
            $stmt->execute([$companyId, $audience]);
        }
    }

    // فعال کردن مجدد بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "جداول مخاطبین با موفقیت به‌روزرسانی شدند.\n";
    
} catch(PDOException $e) {
    die("خطا در به‌روزرسانی جداول: " . $e->getMessage());
} 