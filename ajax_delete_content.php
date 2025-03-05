<?php
// ajax_delete_content.php - عملیات حذف محتوا با AJAX
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

    // بررسی درخواست POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
        throw new Exception('درخواست نامعتبر');
    }

    // دریافت شناسه محتوا
    $contentId = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;

    if ($contentId <= 0) {
        throw new Exception('شناسه محتوا نامعتبر است');
    }

    // بررسی اینکه آیا محتوا وجود دارد
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
    $stmt->execute([$contentId]);
    $content = $stmt->fetch();

    if (!$content) {
        throw new Exception('محتوا یافت نشد');
    }

    // بررسی دسترسی برای حذف
    if (!isAdmin() && !isCEO() && $_SESSION['user_id'] != $content['created_by']) {
        throw new Exception('شما اجازه حذف این محتوا را ندارید');
    }

    // بررسی آیا این محتوا فرآیند پس از انتشار است
    $isPostPublishProcess = false;
    $stmt = $pdo->prepare("SELECT id FROM content_post_publish_processes WHERE id = ?");
    $stmt->execute([$contentId]);
    if ($stmt->rowCount() > 0) {
        $isPostPublishProcess = true;
    }

    // شروع تراکنش
    $pdo->beginTransaction();
    
    if ($isPostPublishProcess) {
        // اگر فرآیند پس از انتشار است، فقط همان فرآیند حذف شود
        
        // حذف روابط پلتفرم برای این فرآیند
        $pdo->prepare("DELETE FROM post_publish_platform_relations WHERE process_id = ?")->execute([$contentId]);
        
        // حذف خود فرآیند
        $pdo->prepare("DELETE FROM content_post_publish_processes WHERE id = ?")->execute([$contentId]);
        
    } else {
        // اگر فرآیند پس از انتشار نیست، حذف کامل محتوا و روابط

        // حذف روابط
        $pdo->prepare("DELETE FROM content_topic_relations WHERE content_id = ?")->execute([$contentId]);
        $pdo->prepare("DELETE FROM content_audience_content WHERE content_id = ?")->execute([$contentId]);
        $pdo->prepare("DELETE FROM content_type_relations WHERE content_id = ?")->execute([$contentId]);
        $pdo->prepare("DELETE FROM content_platform_relations WHERE content_id = ?")->execute([$contentId]);
        $pdo->prepare("DELETE FROM content_task_assignments WHERE content_id = ?")->execute([$contentId]);
        
        // حذف فرآیندهای پس از انتشار
        $processes = $pdo->prepare("SELECT id FROM content_post_publish_processes WHERE content_id = ?");
        $processes->execute([$contentId]);
        foreach ($processes->fetchAll() as $process) {
            $pdo->prepare("DELETE FROM post_publish_platform_relations WHERE process_id = ?")->execute([$process['id']]);
        }
        $pdo->prepare("DELETE FROM content_post_publish_processes WHERE content_id = ?")->execute([$contentId]);
        
        // حذف محتوا
        $pdo->prepare("DELETE FROM contents WHERE id = ?")->execute([$contentId]);
    }
    
    $pdo->commit();
    
    // پاک کردن خروجی بافر
    ob_end_clean();
    
    // ارسال پاسخ موفقیت
    echo json_encode([
        'success' => true, 
        'message' => $isPostPublishProcess ? 'فرآیند پس از انتشار با موفقیت حذف شد' : 'محتوا با موفقیت حذف شد'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // در صورت خطا، تراکنش را برگردان
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // پاک کردن خروجی بافر
    ob_end_clean();
    
    // ارسال پیام خطا
    echo json_encode(['success' => false, 'message' => 'خطا در حذف محتوا: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    
    // ثبت خطا در لاگ
    error_log('Delete Content Error: ' . $e->getMessage());
}