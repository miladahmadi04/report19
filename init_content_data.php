<?php
require_once 'config/db_connect.php';

try {
    // دریافت لیست شرکت‌ها
    $stmt = $pdo->query("SELECT id FROM companies");
    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($companies as $companyId) {
        // موضوعات پیش‌فرض
        $defaultTopics = [
            'آموزشی',
            'خبری',
            'سرگرمی',
            'تبلیغاتی',
            'اطلاع‌رسانی'
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO content_topics (company_id, name) VALUES (?, ?)");
        foreach ($defaultTopics as $topic) {
            $stmt->execute([$companyId, $topic]);
        }

        // مخاطبین هدف پیش‌فرض
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

        $stmt = $pdo->prepare("INSERT IGNORE INTO content_target_audiences (company_id, name) VALUES (?, ?)");
        foreach ($defaultAudiences as $audience) {
            $stmt->execute([$companyId, $audience]);
        }

        // پلتفرم‌های انتشار پیش‌فرض
        $defaultPlatforms = [
            'اینستاگرام',
            'تلگرام',
            'لینکدین',
            'توییتر',
            'یوتیوب',
            'وب‌سایت',
            'واتساپ'
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO content_platforms (company_id, name) VALUES (?, ?)");
        foreach ($defaultPlatforms as $platform) {
            $stmt->execute([$companyId, $platform]);
        }

        // وظایف محتوایی پیش‌فرض
        $defaultTasks = [
            'تولید محتوا',
            'ویرایش محتوا',
            'بازبینی محتوا',
            'تأیید نهایی',
            'انتشار محتوا',
            'پیگیری بازخوردها'
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO content_tasks (company_id, name) VALUES (?, ?)");
        foreach ($defaultTasks as $task) {
            $stmt->execute([$companyId, $task]);
        }
    }

    echo "مقادیر پیش‌فرض با موفقیت اضافه شدند.\n";
    
} catch(PDOException $e) {
    die("خطا در درج مقادیر پیش‌فرض: " . $e->getMessage());
} 