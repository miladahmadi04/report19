<?php
require_once 'config/db_connect.php';

try {
    // غیرفعال کردن بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // جدول موضوعات محتوا
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول مخاطبین هدف
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_target_audiences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول پلتفرم‌های انتشار
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_platforms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول وظایف محتوایی
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول انتصاب وظایف
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_task_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        task_id INT NOT NULL,
        personnel_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (task_id) REFERENCES content_tasks(id) ON DELETE CASCADE,
        FOREIGN KEY (personnel_id) REFERENCES personnel(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول رابطه محتوا و موضوعات
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_topic_relations (
        content_id INT NOT NULL,
        topic_id INT NOT NULL,
        PRIMARY KEY (content_id, topic_id),
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (topic_id) REFERENCES content_topics(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول رابطه محتوا و مخاطبین هدف
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_audience_relations (
        content_id INT NOT NULL,
        audience_id INT NOT NULL,
        PRIMARY KEY (content_id, audience_id),
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (audience_id) REFERENCES content_target_audiences(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول رابطه محتوا و پلتفرم‌ها
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_platform_relations (
        content_id INT NOT NULL,
        platform_id INT NOT NULL,
        PRIMARY KEY (content_id, platform_id),
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (platform_id) REFERENCES content_platforms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول فرآیندهای پس از انتشار
    $pdo->exec("CREATE TABLE IF NOT EXISTS content_post_publish_processes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        format_id INT NOT NULL,
        days_after INT NOT NULL DEFAULT 0,
        publish_time TIME DEFAULT '10:00:00',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (format_id) REFERENCES content_formats(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // جدول رابطه فرآیندهای پس از انتشار و پلتفرم‌ها
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_publish_platform_relations (
        process_id INT NOT NULL,
        platform_id INT NOT NULL,
        PRIMARY KEY (process_id, platform_id),
        FOREIGN KEY (process_id) REFERENCES content_post_publish_processes(id) ON DELETE CASCADE,
        FOREIGN KEY (platform_id) REFERENCES content_platforms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");

    // فعال کردن مجدد بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "جداول مورد نیاز با موفقیت ایجاد شدند.\n";
    
} catch(PDOException $e) {
    die("خطا در ایجاد جداول: " . $e->getMessage());
} 