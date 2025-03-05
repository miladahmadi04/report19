<?php
// content_list.php - نمایش لیست محتواها
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    redirect('login.php');
}

// دریافت شناسه شرکت
$companyId = isAdmin() ? 
    (isset($_GET['company']) && is_numeric($_GET['company']) ? clean($_GET['company']) : null) : 
    $_SESSION['company_id'];

if (!$companyId) {
    redirect('dashboard.php');
}

// حذف محتوا
if (isset($_POST['delete_content']) && isset($_POST['content_id'])) {
    try {
        $contentId = clean($_POST['content_id']);
        
        // بررسی دسترسی برای حذف
        if (!isAdmin() && !isCEO()) {
            throw new Exception('شما دسترسی لازم برای حذف محتوا را ندارید.');
        }
        
        $pdo->beginTransaction();
        
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
        $pdo->prepare("DELETE FROM contents WHERE id = ? AND company_id = ?")->execute([$contentId, $companyId]);
        
        $pdo->commit();
        $message = showSuccess('محتوا با موفقیت حذف شد.');
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = showError('خطا در حذف محتوا: ' . $e->getMessage());
    }
}

// دریافت لیست محتواها
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        CONCAT(p.first_name, ' ', p.last_name) as creator_name,
        ps.name as production_status,
        pbs.name as publish_status
    FROM contents c
    LEFT JOIN personnel p ON c.created_by = p.id
    LEFT JOIN content_production_statuses ps ON c.production_status_id = ps.id
    LEFT JOIN content_publish_statuses pbs ON c.publish_status_id = pbs.id
    WHERE c.company_id = ?
    ORDER BY c.id DESC
");
$stmt->execute([$companyId]);
$contents = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>لیست محتواها</h1>
    <a href="content_add.php?company=<?php echo $companyId; ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> افزودن محتوای جدید
    </a>
</div>

<?php if (isset($message)) echo $message; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>ایجاد کننده</th>
                        <th>تاریخ انتشار</th>
                        <th>وضعیت تولید</th>
                        <th>وضعیت انتشار</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contents)): ?>
                        <tr>
                            <td colspan="6" class="text-center">هیچ محتوایی یافت نشد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contents as $content): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($content['title']); ?></td>
                                <td><?php echo htmlspecialchars($content['creator_name']); ?></td>
                                <td>
                                    <?php 
                                    echo $content['publish_date']; 
                                    if ($content['publish_time']) {
                                        echo ' ساعت ' . $content['publish_time'];
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($content['production_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($content['publish_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="content_view.php?id=<?php echo $content['id']; ?>&company=<?php echo $companyId; ?>" 
                                           class="btn btn-sm btn-info" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isAdmin() || isCEO() || $_SESSION['user_id'] == $content['created_by']): ?>
                                            <a href="content_edit.php?id=<?php echo $content['id']; ?>&company=<?php echo $companyId; ?>" 
                                               class="btn btn-sm btn-warning" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('آیا از حذف این محتوا اطمینان دارید؟');">
                                                <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                                                <button type="submit" name="delete_content" 
                                                        class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>