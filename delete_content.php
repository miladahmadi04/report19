<?php
// delete_content.php - حذف محتوا
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    die(json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']));
}

// دریافت شناسه محتوا
$contentId = isset($_POST['id']) && is_numeric($_POST['id']) ? clean($_POST['id']) : null;

if (!$contentId) {
    die(json_encode(['success' => false, 'message' => 'شناسه محتوا نامعتبر است']));
}

// دریافت اطلاعات محتوا
$stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
$stmt->execute([$contentId]);
$content = $stmt->fetch();

if (!$content) {
    die(json_encode(['success' => false, 'message' => 'محتوا یافت نشد']));
}

// بررسی دسترسی به محتوای شرکت
$companyId = isAdmin() ? $content['company_id'] : $_SESSION['company_id'];
if ($content['company_id'] != $companyId) {
    die(json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']));
}

// بررسی دسترسی کاربر به حذف محتوا
if (!isAdmin() && !isMainTaskResponsible($companyId)) {
    die(json_encode(['success' => false, 'message' => 'شما دسترسی لازم برای حذف این محتوا را ندارید']));
}

try {
    $pdo->beginTransaction();

    // حذف روابط موضوعات
    $stmt = $pdo->prepare("DELETE FROM content_topic_relations WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف روابط مخاطبین
    $stmt = $pdo->prepare("DELETE FROM content_audience_relations WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف روابط پلتفرم‌ها
    $stmt = $pdo->prepare("DELETE FROM content_platform_relations WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف روابط انواع محتوا
    $stmt = $pdo->prepare("DELETE FROM content_type_relations WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف روابط وظایف
    $stmt = $pdo->prepare("DELETE FROM content_task_relations WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف فرآیندهای پس از انتشار
    $stmt = $pdo->prepare("DELETE FROM content_post_publish_processes WHERE content_id = ?");
    $stmt->execute([$contentId]);

    // حذف محتوا
    $stmt = $pdo->prepare("DELETE FROM contents WHERE id = ?");
    $stmt->execute([$contentId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'محتوا با موفقیت حذف شد']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'خطا در حذف محتوا: ' . $e->getMessage()]);
} 