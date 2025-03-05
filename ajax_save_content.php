<?php
// ajax_save_content.php - ذخیره محتوا به صورت AJAX
// فعال کردن بافر خروجی برای جلوگیری از خروجی غیر JSON
ob_start();

// تنظیم گزارش خطای PHP
error_reporting(E_ALL);
ini_set('display_errors', 0); // خطاها نمایش داده نشوند

try {
    require_once 'database.php';
    require_once 'functions.php';
    require_once 'auth.php';

    // تنظیم هدر پاسخ به JSON
    header('Content-Type: application/json; charset=utf-8');

    // بررسی دسترسی کاربر
    if (!isLoggedIn()) {
        throw new Exception('دسترسی غیرمجاز');
    }

    // فقط روش POST پذیرفته می‌شود
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('روش نامعتبر');
    }

    // شروع تراکنش
    $pdo->beginTransaction();
    
    // دریافت اطلاعات فرم
    $contentId = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : null;
    $companyId = isset($_POST['company_id']) && is_numeric($_POST['company_id']) ? intval($_POST['company_id']) : null;
    
    // بررسی دسترسی به شرکت
    if (!$companyId || (!isAdmin() && $_SESSION['company_id'] != $companyId)) {
        throw new Exception('شناسه شرکت نامعتبر است یا شما به این شرکت دسترسی ندارید.');
    }
    
    // بررسی دسترسی به محتوا در صورت ویرایش
    if ($contentId) {
        $stmt = $pdo->prepare("SELECT company_id, created_by FROM contents WHERE id = ?");
        $stmt->execute([$contentId]);
        $existingContent = $stmt->fetch();
        
        if (!$existingContent) {
            throw new Exception('محتوای مورد نظر یافت نشد.');
        }
        
        if ($existingContent['company_id'] != $companyId) {
            throw new Exception('این محتوا متعلق به شرکت دیگری است.');
        }
        
        if (!isAdmin() && !isCEO() && $existingContent['created_by'] != $_SESSION['user_id']) {
            throw new Exception('شما اجازه ویرایش این محتوا را ندارید.');
        }
    }
    
    // دریافت سایر اطلاعات فرم
    $title = clean($_POST['title']);
    $publishDate = clean($_POST['publish_date']);
    $publishTime = clean($_POST['publish_time']);
    $productionStatusId = clean($_POST['production_status_id']);
    $publishStatusId = clean($_POST['publish_status_id']);
    $scenario = isset($_POST['scenario']) ? clean($_POST['scenario']) : '';
    $description = isset($_POST['description']) ? clean($_POST['description']) : '';
    $topicIds = isset($_POST['topics']) ? $_POST['topics'] : [];
    $audienceIds = isset($_POST['audiences']) ? $_POST['audiences'] : [];
    $typeIds = isset($_POST['types']) ? $_POST['types'] : [];
    $platformIds = isset($_POST['platforms']) ? $_POST['platforms'] : [];
    $taskAssignments = isset($_POST['tasks']) ? $_POST['tasks'] : [];
    
    // اعتبارسنجی داده‌های ورودی
    if (empty($title)) {
        throw new Exception('لطفاً عنوان محتوا را وارد کنید.');
    }
    
    if (empty($publishDate)) {
        throw new Exception('لطفاً تاریخ انتشار را وارد کنید.');
    }
    
    if (empty($productionStatusId)) {
        throw new Exception('لطفاً وضعیت تولید را انتخاب کنید.');
    }
    
    if (empty($publishStatusId)) {
        throw new Exception('لطفاً وضعیت انتشار را انتخاب کنید.');
    }
    
    // درج یا به‌روزرسانی محتوا
    if ($contentId) {
        // به‌روزرسانی محتوای موجود
        $stmt = $pdo->prepare("UPDATE contents SET 
            title = ?, 
            publish_date = ?, 
            publish_time = ?, 
            production_status_id = ?, 
            publish_status_id = ?, 
            scenario = ?, 
            description = ?, 
            updated_at = NOW()
            WHERE id = ?");
        
        $stmt->execute([
            $title,
            $publishDate,
            $publishTime,
            $productionStatusId,
            $publishStatusId,
            $scenario,
            $description,
            $contentId
        ]);
    } else {
        // درج محتوای جدید
        $stmt = $pdo->prepare("INSERT INTO contents 
            (company_id, title, publish_date, publish_time, production_status_id, publish_status_id, 
            scenario, description, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->execute([
            $companyId,
            $title,
            $publishDate,
            $publishTime,
            $productionStatusId,
            $publishStatusId,
            $scenario,
            $description,
            $_SESSION['user_id']
        ]);
        
        $contentId = $pdo->lastInsertId();
    }
    
    // به‌روزرسانی موضوعات
    $pdo->prepare("DELETE FROM content_topic_relations WHERE content_id = ?")->execute([$contentId]);
    if (!empty($topicIds)) {
        $topicStmt = $pdo->prepare("INSERT INTO content_topic_relations (content_id, topic_id) VALUES (?, ?)");
        foreach ($topicIds as $topicId) {
            $topicStmt->execute([$contentId, $topicId]);
        }
    }
    
    // به‌روزرسانی مخاطبین هدف
    $pdo->prepare("DELETE FROM content_audience_content WHERE content_id = ?")->execute([$contentId]);
    if (!empty($audienceIds)) {
        $audienceStmt = $pdo->prepare("INSERT INTO content_audience_content (content_id, audience_id) VALUES (?, ?)");
        foreach ($audienceIds as $audienceId) {
            $audienceStmt->execute([$contentId, $audienceId]);
        }
    }
    
    // به‌روزرسانی انواع محتوا
    $pdo->prepare("DELETE FROM content_type_relations WHERE content_id = ?")->execute([$contentId]);
    if (!empty($typeIds)) {
        $typeStmt = $pdo->prepare("INSERT INTO content_type_relations (content_id, type_id) VALUES (?, ?)");
        foreach ($typeIds as $typeId) {
            $typeStmt->execute([$contentId, $typeId]);
        }
    }
    
    // به‌روزرسانی پلتفرم‌های انتشار
    $pdo->prepare("DELETE FROM content_platform_relations WHERE content_id = ?")->execute([$contentId]);
    if (!empty($platformIds)) {
        $platformStmt = $pdo->prepare("INSERT INTO content_platform_relations (content_id, platform_id) VALUES (?, ?)");
        foreach ($platformIds as $platformId) {
            $platformStmt->execute([$contentId, $platformId]);
        }
    }
    
    // به‌روزرسانی تخصیص وظایف
    $pdo->prepare("DELETE FROM content_task_assignments WHERE content_id = ?")->execute([$contentId]);
    if (!empty($taskAssignments)) {
        $taskStmt = $pdo->prepare("INSERT INTO content_task_assignments (content_id, task_id, personnel_id) VALUES (?, ?, ?)");
        foreach ($taskAssignments as $taskId => $personnelId) {
            if (!empty($personnelId)) {
                $taskStmt->execute([$contentId, $taskId, $personnelId]);
            }
        }
    }
    
    // به‌روزرسانی فرآیندهای پس از انتشار
    // حذف فرآیندهای قبلی و روابط آنها با پلتفرم‌ها
    $processStmt = $pdo->prepare("SELECT id FROM content_post_publish_processes WHERE content_id = ?");
    $processStmt->execute([$contentId]);
    $processIds = $processStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($processIds)) {
        $placeholders = implode(',', array_fill(0, count($processIds), '?'));
        $pdo->prepare("DELETE FROM post_publish_platform_relations WHERE process_id IN ($placeholders)")->execute($processIds);
    }
    
    $pdo->prepare("DELETE FROM content_post_publish_processes WHERE content_id = ?")->execute([$contentId]);
    
    // افزودن فرآیندهای جدید
    if (isset($_POST['post_publish_format']) && is_array($_POST['post_publish_format'])) {
        $processStmt = $pdo->prepare("INSERT INTO content_post_publish_processes 
            (content_id, format_id, days_after, publish_time) VALUES (?, ?, ?, ?)");
            
        $platformRelationStmt = $pdo->prepare("INSERT INTO post_publish_platform_relations 
            (process_id, platform_id) VALUES (?, ?)");
            
        foreach ($_POST['post_publish_format'] as $index => $formatId) {
            if (!empty($formatId)) {
                $daysAfter = isset($_POST['post_publish_days'][$index]) ? intval($_POST['post_publish_days'][$index]) : 0;
                $time = isset($_POST['post_publish_time'][$index]) ? $_POST['post_publish_time'][$index] : '10:00';
                
                $processStmt->execute([$contentId, $formatId, $daysAfter, $time]);
                $processId = $pdo->lastInsertId();
                
                // درج پلتفرم‌های فرآیند
                if (isset($_POST['post_publish_platforms'][$index]) && is_array($_POST['post_publish_platforms'][$index])) {
                    foreach ($_POST['post_publish_platforms'][$index] as $platformId) {
                        $platformRelationStmt->execute([$processId, $platformId]);
                    }
                }
            }
        }
    }
    
    // ثبت تراکنش
    $pdo->commit();
    
    // پاک کردن بافر خروجی
    ob_end_clean();
    
    // ارسال پاسخ موفقیت
    echo json_encode([
        'success' => true, 
        'message' => 'محتوا با موفقیت ذخیره شد.',
        'content_id' => $contentId
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // بازگشت تراکنش در صورت خطا
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // پاک کردن بافر خروجی
    ob_end_clean();
    
    // ارسال پیام خطا
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
    // ثبت خطا در لاگ
    error_log('Save Content Error: ' . $e->getMessage());
}