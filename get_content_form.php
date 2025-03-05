<?php
// get_content_form.php - دریافت فرم ویرایش یا ایجاد محتوا
// فعال کردن بافر خروجی برای جلوگیری از خروجی ناخواسته
ob_start();

// تنظیم گزارش خطای PHP
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'database.php';
    require_once 'functions.php';
    require_once 'auth.php';

    // بررسی دسترسی کاربر
    if (!isLoggedIn()) {
        throw new Exception('دسترسی غیرمجاز');
    }

    // تعیین حالت: ویرایش یا ایجاد
    $isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
    $contentId = $isEdit ? (int)$_GET['id'] : 0;
    $initialDate = isset($_GET['date']) ? clean($_GET['date']) : date('Y-m-d');
    
    // دریافت شناسه شرکت
    if ($isEdit) {
        // در حالت ویرایش، شناسه شرکت را از محتوا دریافت می‌کنیم
        $stmt = $pdo->prepare("SELECT company_id FROM contents WHERE id = ?");
        $stmt->execute([$contentId]);
        $result = $stmt->fetch();
        
        if (!$result) {
            throw new Exception('محتوای مورد نظر یافت نشد');
        }
        
        $companyId = $result['company_id'];
        
        // بررسی دسترسی کاربر به این شرکت
        if (!isAdmin()) {
            // اگر کاربر ادمین نیست، بررسی می‌کنیم که ایجاد کننده محتوا باشد یا مدیر شرکت
            $stmt = $pdo->prepare("SELECT created_by FROM contents WHERE id = ?");
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            // اگر کاربر ایجاد کننده محتوا نیست و مدیر شرکت هم نیست، دسترسی ندارد
            if ($_SESSION['company_id'] != $companyId && 
                $content['created_by'] != $_SESSION['user_id'] && 
                !isCEO()) {
                throw new Exception('شناسه شرکت نامعتبر است یا شما به این شرکت دسترسی ندارید');
            }
        }
    } else {
        // در حالت ایجاد، شناسه شرکت را از پارامتر یا شرکت کاربر دریافت می‌کنیم
        $companyId = isAdmin() ? 
            (isset($_GET['company']) && is_numeric($_GET['company']) ? clean($_GET['company']) : null) : 
            $_SESSION['company_id'];
            
        if (empty($companyId)) {
            throw new Exception('شناسه شرکت نامعتبر است');
        }
        
        // بررسی دسترسی کاربر به این شرکت
        if (!isAdmin() && $_SESSION['company_id'] != $companyId) {
            throw new Exception('شناسه شرکت نامعتبر است یا شما به این شرکت دسترسی ندارید');
        }
    }

    // بررسی وجود شرکت
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE id = ? AND is_active = 1");
    $stmt->execute([$companyId]);
    if (!$stmt->fetch()) {
        throw new Exception('شرکت مورد نظر یافت نشد یا غیرفعال است');
    }

    // دریافت اطلاعات محتوا در حالت ویرایش
    $content = null;
    if ($isEdit) {
        $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
        $stmt->execute([$contentId]);
        $content = $stmt->fetch();
        
        if (!$content) {
            throw new Exception('محتوای مورد نظر یافت نشد');
        }
        
        // بررسی دسترسی برای ویرایش
        if (!isAdmin() && !isCEO() && $_SESSION['user_id'] != $content['created_by']) {
            throw new Exception('شما اجازه ویرایش این محتوا را ندارید');
        }
    }

    // دریافت اطلاعات مورد نیاز برای فرم
    $topics = $pdo->prepare("SELECT * FROM content_topics WHERE company_id = ? ORDER BY name");
    $topics->execute([$companyId]);
    $topics = $topics->fetchAll();

    $audiences = $pdo->prepare("SELECT * FROM content_audiences WHERE company_id = ? ORDER BY name");
    $audiences->execute([$companyId]);
    $audiences = $audiences->fetchAll();

    $types = $pdo->prepare("SELECT * FROM content_types WHERE company_id = ? ORDER BY name");
    $types->execute([$companyId]);
    $types = $types->fetchAll();

    $platforms = $pdo->prepare("SELECT * FROM content_platforms WHERE company_id = ? ORDER BY name");
    $platforms->execute([$companyId]);
    $platforms = $platforms->fetchAll();

    $tasks = $pdo->prepare("SELECT * FROM content_tasks WHERE company_id = ? ORDER BY name");
    $tasks->execute([$companyId]);
    $tasks = $tasks->fetchAll();

    $personnel = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM personnel WHERE company_id = ? AND is_active = 1 ORDER BY CONCAT(first_name, ' ', last_name)");
    $personnel->execute([$companyId]);
    $personnel = $personnel->fetchAll();

    $productionStatuses = $pdo->prepare("SELECT * FROM content_production_statuses WHERE company_id = ? ORDER BY name");
    $productionStatuses->execute([$companyId]);
    $productionStatuses = $productionStatuses->fetchAll();

    $publishStatuses = $pdo->prepare("SELECT * FROM content_publish_statuses WHERE company_id = ? ORDER BY name");
    $publishStatuses->execute([$companyId]);
    $publishStatuses = $publishStatuses->fetchAll();

    $formats = $pdo->prepare("SELECT * FROM content_formats WHERE company_id = ? ORDER BY name");
    $formats->execute([$companyId]);
    $formats = $formats->fetchAll();

    // دریافت اطلاعات اضافی در حالت ویرایش
    $selectedTopics = [];
    $selectedAudiences = [];
    $selectedTypes = [];
    $selectedPlatforms = [];
    $taskAssignments = [];
    $postPublishProcesses = [];

    if ($isEdit) {
        // موضوعات انتخاب شده
        $stmt = $pdo->prepare("SELECT topic_id FROM content_topic_relations WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $selectedTopics = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // مخاطبین انتخاب شده
        $stmt = $pdo->prepare("SELECT audience_id FROM content_audience_content WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $selectedAudiences = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // انواع محتوای انتخاب شده
        $stmt = $pdo->prepare("SELECT type_id FROM content_type_relations WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $selectedTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // پلتفرم‌های انتخاب شده
        $stmt = $pdo->prepare("SELECT platform_id FROM content_platform_relations WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $selectedPlatforms = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // وظایف و مسئولین
        $stmt = $pdo->prepare("SELECT task_id, personnel_id FROM content_task_assignments WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $taskAssignments = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // فرآیندهای پس از انتشار
        $stmt = $pdo->prepare("
            SELECT 
                cpp.*,
                GROUP_CONCAT(pppr.platform_id) as platform_ids
            FROM content_post_publish_processes cpp
            LEFT JOIN post_publish_platform_relations pppr ON cpp.id = pppr.process_id
            WHERE cpp.content_id = ?
            GROUP BY cpp.id
            ORDER BY cpp.days_after");
        $stmt->execute([$contentId]);
        $postPublishProcesses = $stmt->fetchAll();
    }

    // پاک کردن بافر خروجی
    ob_end_clean();

    // شروع ساخت فرم
?>

<form id="content-form" method="POST" action="ajax_save_content.php">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?php echo $contentId; ?>">
        <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
    <?php else: ?>
        <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="title" class="form-label">عنوان محتوا *</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo $isEdit ? htmlspecialchars($content['title']) : ''; ?>" required>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="publish_date" class="form-label">تاریخ انتشار *</label>
            <input type="date" class="form-control" id="publish_date" name="publish_date" value="<?php echo $isEdit ? $content['publish_date'] : $initialDate; ?>" required>
        </div>
        <div class="col-md-6">
            <label for="publish_time" class="form-label">ساعت انتشار</label>
            <input type="time" class="form-control" id="publish_time" name="publish_time" value="<?php echo $isEdit ? $content['publish_time'] : '10:00'; ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="production_status_id" class="form-label">وضعیت تولید *</label>
            <select class="form-select" id="production_status_id" name="production_status_id" required>
                <option value="">انتخاب کنید</option>
                <?php foreach ($productionStatuses as $status): ?>
                    <option value="<?php echo $status['id']; ?>" <?php echo $isEdit && $content['production_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                        <?php echo $status['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="publish_status_id" class="form-label">وضعیت انتشار *</label>
            <select class="form-select" id="publish_status_id" name="publish_status_id" required>
                <option value="">انتخاب کنید</option>
                <?php foreach ($publishStatuses as $status): ?>
                    <option value="<?php echo $status['id']; ?>" <?php echo $isEdit && $content['publish_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                        <?php echo $status['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">موضوعات کلی</label>
        <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto;">
            <div class="row">
                <?php foreach ($topics as $topic): ?>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topics[]" id="topic_<?php echo $topic['id']; ?>" value="<?php echo $topic['id']; ?>" <?php echo $isEdit && in_array($topic['id'], $selectedTopics) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="topic_<?php echo $topic['id']; ?>">
                                <?php echo $topic['name']; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- El resto del formulario permanece igual -->
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // افزودن فرآیند جدید پس از انتشار
    document.getElementById('add_post_publish_process').addEventListener('click', function() {
        var processCount = document.querySelectorAll('.post-publish-process').length;
        var template = `
            <div class="post-publish-process border rounded p-3 mb-3">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">فرمت محتوا</label>
                        <select class="form-select" name="post_publish_format[${processCount}]">
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($formats as $format): ?>
                                <option value="<?php echo $format['id']; ?>"><?php echo $format['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">تعداد روز پس از انتشار</label>
                        <input type="number" class="form-control" name="post_publish_days[${processCount}]" min="0" value="7">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">ساعت انتشار</label>
                        <input type="time" class="form-control" name="post_publish_time[${processCount}]" value="10:00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">پلتفرم‌های انتشار</label>
                        <div class="border rounded p-2" style="max-height: 100px; overflow-y: auto;">
                            <?php foreach ($platforms as $platform): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="post_publish_platforms[${processCount}][]" value="<?php echo $platform['id']; ?>">
                                    <label class="form-check-label">
                                        <?php echo $platform['name']; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm mt-2 remove-process">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
        `;
        
        document.getElementById('post_publish_processes').insertAdjacentHTML('beforeend', template);
    });

    // حذف فرآیند
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-process')) {
            const process = e.target.closest('.post-publish-process');
            process.remove();
        }
    });
});
</script>

<?php
} catch (Exception $e) {
    // پاک کردن خروجی بافر
    ob_end_clean();
    
    // نمایش پیام خطا
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
    
    // ثبت خطا در لاگ
    error_log('Get Content Form Error: ' . $e->getMessage());
}
?>