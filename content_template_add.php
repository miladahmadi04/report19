<?php
// content_template_add.php - ایجاد قالب محتوایی جدید
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
    redirect('content_management.php');
}

$message = '';

// افزودن قالب جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_template'])) {
    try {
        // شروع تراکنش
        $pdo->beginTransaction();
        
        // دریافت اطلاعات فرم
        $name = clean($_POST['name']);
        $description = clean($_POST['description']);
        $scenario = clean($_POST['scenario']);
        $topicIds = isset($_POST['topics']) ? $_POST['topics'] : [];
        $audienceIds = isset($_POST['audiences']) ? $_POST['audiences'] : [];
        $typeIds = isset($_POST['types']) ? $_POST['types'] : [];
        $platformIds = isset($_POST['platforms']) ? $_POST['platforms'] : [];
        $taskIds = isset($_POST['tasks']) ? $_POST['tasks'] : [];

        // اعتبارسنجی داده‌ها
        if (empty($name)) {
            throw new Exception('نام قالب الزامی است.');
        }

        // درج قالب
        $stmt = $pdo->prepare("INSERT INTO content_templates 
            (company_id, name, description, scenario, created_by) 
            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $companyId, 
            $name, 
            $description, 
            $scenario, 
            $_SESSION['user_id']
        ]);
        
        $templateId = $pdo->lastInsertId();

        // درج موضوعات
        if (!empty($topicIds)) {
            $topicStmt = $pdo->prepare("INSERT INTO template_topic_relations (template_id, topic_id) VALUES (?, ?)");
            foreach ($topicIds as $topicId) {
                $topicStmt->execute([$templateId, $topicId]);
            }
        }

        // درج مخاطبین هدف
        if (!empty($audienceIds)) {
            $audienceStmt = $pdo->prepare("INSERT INTO template_audience_relations (template_id, audience_id) VALUES (?, ?)");
            foreach ($audienceIds as $audienceId) {
                $audienceStmt->execute([$templateId, $audienceId]);
            }
        }

        // درج انواع محتوا
        if (!empty($typeIds)) {
            $typeStmt = $pdo->prepare("INSERT INTO template_type_relations (template_id, type_id) VALUES (?, ?)");
            foreach ($typeIds as $typeId) {
                $typeStmt->execute([$templateId, $typeId]);
            }
        }

        // درج پلتفرم‌های انتشار
        if (!empty($platformIds)) {
            $platformStmt = $pdo->prepare("INSERT INTO template_platform_relations (template_id, platform_id) VALUES (?, ?)");
            foreach ($platformIds as $platformId) {
                $platformStmt->execute([$templateId, $platformId]);
            }
        }

        // درج وظایف
        if (!empty($taskIds)) {
            $taskStmt = $pdo->prepare("INSERT INTO template_task_relations (template_id, task_id) VALUES (?, ?)");
            foreach ($taskIds as $taskId) {
                $taskStmt->execute([$templateId, $taskId]);
            }
        }

        $pdo->commit();
        
        // هدایت به صفحه قالب‌ها
        redirect('content_templates.php?company=' . $companyId);
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = showError('خطا: ' . $e->getMessage());
    }
}

// دریافت اطلاعات برای فرم‌ها
$topics = $pdo->prepare("SELECT * FROM content_topics WHERE company_id = ? ORDER BY name");
$topics->execute([$companyId]);
$topics = $topics->fetchAll();

$audiences = $pdo->prepare("SELECT * FROM content_target_audiences WHERE company_id = ? ORDER BY name");
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

// دریافت نام شرکت
$stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$companyName = $stmt->fetchColumn();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>ایجاد قالب محتوایی جدید</h1>
    <a href="content_templates.php?company=<?php echo $companyId; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت به قالب‌های محتوایی
    </a>
</div>

<?php echo $message; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    ایجاد قالب محتوایی جدید برای شرکت <strong><?php echo $companyName; ?></strong>
    <p class="mb-0 mt-2">از این قالب می‌توانید برای ایجاد سریع‌تر محتواهای مشابه استفاده کنید.</p>
</div>

<form method="POST" action="">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">اطلاعات اصلی قالب</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">نام قالب *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">نامی توصیفی که نوع محتوای این قالب را مشخص کند.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">توضیحات قالب</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <div class="form-text">توضیحاتی درباره این قالب و موارد استفاده آن.</div>
                    </div>

                    <div class="mb-3">
                        <label for="scenario" class="form-label">سناریو</label>
                        <textarea class="form-control" id="scenario" name="scenario" rows="5"></textarea>
                        <div class="form-text">سناریو پیش‌فرض برای محتواهایی که با این قالب ایجاد می‌شوند.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">راهنما</h5>
                </div>
                <div class="card-body">
                    <h6>قالب محتوایی چیست؟</h6>
                    <p>قالب محتوایی مجموعه‌ای از تنظیمات پیش‌فرض برای ایجاد محتواهای مشابه است.</p>
                    
                    <h6>نکات مهم:</h6>
                    <ul>
                        <li>فیلدهای انتخاب شده به عنوان پیش‌فرض در زمان ایجاد محتوا انتخاب می‌شوند.</li>
                        <li>سناریو و توضیحات به عنوان متن پیش‌نویس در فرم ایجاد محتوا وارد می‌شوند.</li>
                        <li>قالب‌ها تنها پیش‌نویس هستند و در زمان ایجاد محتوا قابل تغییر هستند.</li>
                    </ul>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        توجه: فیلدهای تاریخ و ساعت انتشار باید در زمان ایجاد محتوا وارد شوند و در قالب قابل تنظیم نیستند.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <button type="submit" name="add_template" class="btn btn-primary px-5">
            <i class="fas fa-save"></i> ذخیره قالب
        </button>
    </div>
</form>

<?php include 'footer.php'; ?></div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">تنظیمات پیش‌فرض</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">موضوعات کلی</label>
                            <div class="row">
                                <?php foreach ($topics as $topic): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="topics[]" 
                                                   id="topic_<?php echo $topic['id']; ?>" 
                                                   value="<?php echo $topic['id']; ?>"
                                                   <?php echo $topic['is_default'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="topic_<?php echo $topic['id']; ?>">
                                                <?php echo $topic['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">مخاطبین هدف</label>
                            <div class="row">
                                <?php foreach ($audiences as $audience): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="audiences[]" 
                                                   id="audience_<?php echo $audience['id']; ?>" 
                                                   value="<?php echo $audience['id']; ?>"
                                                   <?php echo $audience['is_default'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="audience_<?php echo $audience['id']; ?>">
                                                <?php echo $audience['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">نوع محتوا</label>
                            <div class="row">
                                <?php foreach ($types as $type): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="types[]" 
                                                   id="type_<?php echo $type['id']; ?>" 
                                                   value="<?php echo $type['id']; ?>"
                                                   <?php echo $type['is_default'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_<?php echo $type['id']; ?>">
                                                <?php echo $type['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">پلتفرم‌های انتشار</label>
                            <div class="row">
                                <?php foreach ($platforms as $platform): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="platforms[]" 
                                                   id="platform_<?php echo $platform['id']; ?>" 
                                                   value="<?php echo $platform['id']; ?>"
                                                   <?php echo $platform['is_default'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="platform_<?php echo $platform['id']; ?>">
                                                <?php echo $platform['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">وظایف</label>
                            <div class="row">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="tasks[]" 
                                                   id="task_<?php echo $task['id']; ?>" 
                                                   value="<?php echo $task['id']; ?>"
                                                   <?php echo $task['is_default'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="task_<?php echo $task['id']; ?>">
                                                <?php echo $task['name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>