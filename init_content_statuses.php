<?php
require_once 'config/db_connect.php';

// حذف جداول قبلی
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("DROP TABLE IF EXISTS content_type_relations");
$pdo->exec("DROP TABLE IF EXISTS contents");
$pdo->exec("DROP TABLE IF EXISTS content_production_statuses");
$pdo->exec("DROP TABLE IF EXISTS content_publish_statuses");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// ایجاد جداول جدید
$pdo->exec("CREATE TABLE IF NOT EXISTS content_production_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_company_status (company_id, name),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS content_publish_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_company_status (company_id, name),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    scenario TEXT,
    description TEXT,
    production_status_id INT NOT NULL,
    publish_status_id INT NOT NULL,
    created_by INT NOT NULL,
    publish_date DATE NOT NULL,
    publish_time TIME DEFAULT '10:00:00',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (production_status_id) REFERENCES content_production_statuses(id) ON DELETE RESTRICT,
    FOREIGN KEY (publish_status_id) REFERENCES content_publish_statuses(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS content_type_relations (
    content_id INT NOT NULL,
    type_id INT NOT NULL,
    PRIMARY KEY (content_id, type_id),
    FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES content_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

// دریافت لیست همه شرکت‌ها
$stmt = $pdo->query("SELECT id FROM companies");
$companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

// تعریف وضعیت‌های پیش‌فرض تولید
$productionStatuses = [
    ['محتوا تولید نشده', true, true],    // وضعیت پیش‌فرض و سیستمی
    ['محتوا در حال تولید است', false, true],  // وضعیت سیستمی
    ['محتوا تولید شده', false, true]     // وضعیت سیستمی
];

// تعریف وضعیت‌های پیش‌فرض انتشار
$publishStatuses = [
    ['منتشر نشده', true, true],   // وضعیت پیش‌فرض و سیستمی
    ['منتشر شده', false, true]    // وضعیت سیستمی
];

// درج وضعیت‌های پیش‌فرض برای هر شرکت
foreach ($companies as $companyId) {
    // درج وضعیت‌های تولید
    foreach ($productionStatuses as $status) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO content_production_statuses (company_id, name, is_default, is_system) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $status[0], $status[1], $status[2]]);
        } catch (PDOException $e) {
            // اگر رکورد تکراری باشد، از آن رد می‌شویم
            continue;
        }
    }
    
    // درج وضعیت‌های انتشار
    foreach ($publishStatuses as $status) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO content_publish_statuses (company_id, name, is_default, is_system) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $status[0], $status[1], $status[2]]);
        } catch (PDOException $e) {
            // اگر رکورد تکراری باشد، از آن رد می‌شویم
            continue;
        }
    }
}

echo "وضعیت‌های پیش‌فرض محتوا با موفقیت برای همه شرکت‌ها ایجاد شدند.\n"; 