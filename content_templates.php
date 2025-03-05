<?php
// content_templates.php - مدیریت قالب‌های محتوایی
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    redirect('login.php');
}

// دریافت اطلاعات شرکت کاربر
$companyId = isAdmin() ? 
    (isset($_GET['company']) && is_numeric($_GET['company']) ? clean($_GET['company']) : null) : 
    $_SESSION['company_id'];

// اگر شرکت انتخاب نشده است، به صفحه مدیریت محتوا بازگردیم
if (empty($companyId)) {
    redirect('content_management.php');
}

$message = '';

// حذف قالب
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $templateId = $_GET['delete'];
    
    try {
        // بررسی اینکه آیا این قالب متعلق به شرکت کاربر است
        $canDelete = false;
        if (isAdmin()) {
            $stmt = $pdo->prepare("SELECT company_id FROM content_templates WHERE id = ?");
            $stmt->execute([$templateId]);
            $template = $stmt->fetch();
            $canDelete = ($template && $template['company_id'] == $companyId);
        } else {
            $stmt = $pdo->prepare("SELECT company_id, created_by FROM content_templates WHERE id = ?");
            $stmt->execute([$templateId]);
            $template = $stmt->fetch();
            $canDelete = ($template && $template['company_id'] == $_SESSION['company_id'] && 
                         ($template['created_by'] == $_SESSION['user_id'] || isCEO()));
        }
        
        if ($canDelete) {
            // شروع تراکنش
            $pdo->beginTransaction();
            
            // حذف روابط مربوط به قالب
            $stmt = $pdo->prepare("DELETE FROM template_topic_relations WHERE template_id = ?");
            $stmt->execute([$templateId]);
            
            $stmt = $pdo->prepare("DELETE FROM template_audience_relations WHERE template_id = ?");
            $stmt->execute([$templateId]);
            
            $stmt = $pdo->prepare("DELETE FROM template_type_relations WHERE template_id = ?");
            $stmt->execute([$templateId]);
            
            $stmt = $pdo->prepare("DELETE FROM template_platform_relations WHERE template_id = ?");
            $stmt->execute([$templateId]);
            
            $stmt = $pdo->prepare("DELETE FROM template_task_relations WHERE template_id = ?");
            $stmt->execute([$templateId]);
            
            // حذف قالب
            $stmt = $pdo->prepare("DELETE FROM content_templates WHERE id = ?");
            $stmt->execute([$templateId]);
            
            $pdo->commit();
            $message = showSuccess('قالب محتوا با موفقیت حذف شد.');
        } else {
            $message = showError('شما اجازه حذف این قالب را ندارید.');
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = showError('خطا در حذف قالب: ' . $e->getMessage());
    }
}

// دریافت اطلاعات شرکت
$stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$companyName = $stmt->fetchColumn();

// دریافت لیست قالب‌ها
$query = "SELECT t.*, p.full_name as creator_name 
          FROM content_templates t 
          JOIN personnel p ON t.created_by = p.id 
          WHERE t.company_id = ? 
          ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$companyId]);
$templates = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>مدیریت قالب‌های محتوایی</h1>
    <div>
        <a href="content_template_add.php?company=<?php echo $companyId; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> ایجاد قالب جدید
        </a>
        <a href="content_management.php<?php echo isAdmin() ? '?company=' . $companyId : ''; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به مدیریت محتوا
        </a>
    </div>
</div>

<?php echo $message; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    قالب‌های محتوایی شرکت <strong><?php echo $companyName; ?></strong>
    <p class="mb-0 mt-2">از قالب‌های محتوایی می‌توانید برای ایجاد سریع‌تر محتوای جدید استفاده کنید.</p>
</div>

<?php if (count($templates) > 0): ?>
    <div class="row">
        <?php foreach ($templates as $template): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $template['name']; ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <?php 
                                // نمایش توضیحات خلاصه
                                $description = $template['description'] ?: 'بدون توضیحات';
                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                            ?>
                        </p>
                        
                        <?php 
                            // دریافت موضوعات قالب
                            $stmt = $pdo->prepare("SELECT c.name 
                                                 FROM template_topic_relations ttr 
                                                 JOIN content_topics c ON ttr.topic_id = c.id 
                                                 WHERE ttr.template_id = ?");
                            $stmt->execute([$template['id']]);
                            $topics = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        ?>
                        
                        <?php if (count($topics) > 0): ?>
                            <p class="small mt-2 mb-0"><strong>موضوعات:</strong> 
                                <?php echo implode('، ', $topics); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                ایجاد شده توسط: <?php echo $template['creator_name']; ?>
                            </small>
                            <small class="text-muted">
                                <?php echo $template['created_at']; ?>
                            </small>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <a href="content_add.php?company=<?php echo $companyId; ?>&template=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> ایجاد محتوا
                            </a>
                            <div>
                                <a href="content_template_edit.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> ویرایش
                                </a>
                                <a href="?company=<?php echo $companyId; ?>&delete=<?php echo $template['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('آیا از حذف این قالب اطمینان دارید؟');">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        هیچ قالب محتوایی یافت نشد.
        <p class="mb-0 mt-2">
            برای ایجاد قالب جدید، روی دکمه «ایجاد قالب جدید» کلیک کنید.
        </p>
    </div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">راهنمای استفاده از قالب‌های محتوایی</h5>
    </div>
    <div class="card-body">
        <p>قالب‌های محتوایی به شما کمک می‌کنند تا محتواهایی با ساختار مشابه را سریع‌تر ایجاد کنید.</p>
        
        <h6>مزایای استفاده از قالب‌ها:</h6>
        <ul>
            <li>صرفه‌جویی در زمان با پر شدن خودکار فیلدهای مشترک</li>
            <li>حفظ یکپارچگی محتواها</li>
            <li>استفاده مجدد از سناریوها و توضیحات</li>
        </ul>
        
        <h6>نحوه استفاده:</h6>
        <ol>
            <li>یک قالب جدید ایجاد کنید</li>
            <li>فیلدهای مورد نظر را پر کنید</li>
            <li>برای ایجاد محتوای جدید، از دکمه «ایجاد محتوا» در کارت قالب استفاده کنید</li>
        </ol>
    </div>
</div>

<?php include 'footer.php'; ?>