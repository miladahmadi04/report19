<?php
// content_edit.php - ویرایش محتوا
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

// دریافت شناسه محتوا
$contentId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if ($contentId <= 0) {
    redirect('content_list.php?company=' . $companyId);
}

// دریافت اطلاعات محتوا
$stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
$stmt->execute([$contentId]);
$content = $stmt->fetch();

if (!$content) {
    redirect('content_list.php?company=' . $companyId);
}

// بررسی دسترسی برای ویرایش
if (!isAdmin() && !isCEO() && $_SESSION['user_id'] != $content['created_by']) {
    redirect('content_list.php?company=' . $companyId);
}

$message = '';

// ویرایش محتوا
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_content'])) {
    try {
        // شروع تراکنش
        $pdo->beginTransaction();
        
        // دریافت اطلاعات فرم
        $title = clean($_POST['title']);
        $topicIds = isset($_POST['topics']) ? $_POST['topics'] : [];
        $audienceIds = isset($_POST['audiences']) ? $_POST['audiences'] : [];
        $typeIds = isset($_POST['types']) ? $_POST['types'] : [];
        $platformIds = isset($_POST['platforms']) ? $_POST['platforms'] : [];
        $publishDate = clean($_POST['publish_date']);
        $publishTime = clean($_POST['publish_time']);
        $taskAssignments = isset($_POST['tasks']) ? $_POST['tasks'] : [];
        $scenario = clean($_POST['scenario']);
        $productionStatusId = clean($_POST['production_status']);
        $publishStatusId = clean($_POST['publish_status']);
        $description = clean($_POST['description']);

        // اعتبارسنجی داده‌ها
        if (empty($title)) {
            throw new Exception('عنوان محتوا الزامی است.');
        }

        // به‌روزرسانی محتوا
        $stmt = $pdo->prepare("UPDATE contents SET 
            title = ?, 
            scenario = ?, 
            description = ?, 
            production_status_id = ?, 
            publish_status_id = ?, 
            publish_date = ?, 
            publish_time = ?
            WHERE id = ?");
        
        $stmt->execute([
            $title, 
            $scenario, 
            $description, 
            $productionStatusId, 
            $publishStatusId, 
            $publishDate,
            $publishTime,
            $contentId
        ]);

        // به‌روزرسانی موضوعات
        $pdo->exec("DELETE FROM content_topic_relations WHERE content_id = " . $contentId);
        if (!empty($topicIds)) {
            $topicStmt = $pdo->prepare("INSERT INTO content_topic_relations (content_id, topic_id) VALUES (?, ?)");
            foreach ($topicIds as $topicId) {
                $topicStmt->execute([$contentId, $topicId]);
            }
        }

        // به‌روزرسانی مخاطبین هدف
        $pdo->exec("DELETE FROM content_audience_content WHERE content_id = " . $contentId);
        if (!empty($audienceIds)) {
            $audienceStmt = $pdo->prepare("INSERT INTO content_audience_content (content_id, audience_id) VALUES (?, ?)");
            foreach ($audienceIds as $audienceId) {
                $audienceStmt->execute([$contentId, $audienceId]);
            }
        }

        // به‌روزرسانی انواع محتوا
        $pdo->exec("DELETE FROM content_type_relations WHERE content_id = " . $contentId);
        if (!empty($typeIds)) {
            $typeStmt = $pdo->prepare("INSERT INTO content_type_relations (content_id, type_id) VALUES (?, ?)");
            foreach ($typeIds as $typeId) {
                $typeStmt->execute([$contentId, $typeId]);
            }
        }

        // به‌روزرسانی پلتفرم‌های انتشار
        $pdo->exec("DELETE FROM content_platform_relations WHERE content_id = " . $contentId);
        if (!empty($platformIds)) {
            $platformStmt = $pdo->prepare("INSERT INTO content_platform_relations (content_id, platform_id) VALUES (?, ?)");
            foreach ($platformIds as $platformId) {
                $platformStmt->execute([$contentId, $platformId]);
            }
        }

        // به‌روزرسانی وظایف و مسئولان
        $pdo->exec("DELETE FROM content_task_assignments WHERE content_id = " . $contentId);
        foreach ($taskAssignments as $taskId => $personnelId) {
            if (!empty($personnelId)) {
                $checkPersonnel = $pdo->prepare("SELECT id FROM personnel WHERE id = ? AND company_id = ? AND is_active = 1");
                $checkPersonnel->execute([$personnelId, $content['company_id']]);
                if ($checkPersonnel->rowCount() > 0) {
                    $taskStmt = $pdo->prepare("INSERT INTO content_task_assignments 
                        (content_id, task_id, personnel_id) VALUES (?, ?, ?)");
                    $taskStmt->execute([$contentId, $taskId, $personnelId]);
                }
            }
        }

        // به‌روزرسانی فرآیند پس از انتشار
        $pdo->exec("DELETE FROM content_post_publish_processes WHERE content_id = " . $contentId);
        if (isset($_POST['post_publish_format']) && is_array($_POST['post_publish_format'])) {
            foreach ($_POST['post_publish_format'] as $index => $formatId) {
                if (!empty($formatId)) {
                    $postPublishPlatforms = isset($_POST['post_publish_platforms'][$index]) ? $_POST['post_publish_platforms'][$index] : [];
                    $postPublishDays = clean($_POST['post_publish_days'][$index]);
                    $postPublishTime = clean($_POST['post_publish_time'][$index]) ?: '10:00';

                    $postPublishStmt = $pdo->prepare("INSERT INTO content_post_publish_processes 
                        (content_id, format_id, days_after, publish_time) VALUES (?, ?, ?, ?)");
                    $postPublishStmt->execute([
                        $contentId, 
                        $formatId, 
                        $postPublishDays ?: 0, 
                        $postPublishTime
                    ]);

                    if (!empty($postPublishPlatforms)) {
                        $processId = $pdo->lastInsertId();
                        $platformStmt = $pdo->prepare("INSERT INTO post_publish_platform_relations 
                            (process_id, platform_id) VALUES (?, ?)");
                        foreach ($postPublishPlatforms as $platformId) {
                            $platformStmt->execute([$processId, $platformId]);
                        }
                    }
                }
            }
        }

        $pdo->commit();
        redirect('content_view.php?id=' . $contentId . '&company=' . $companyId);
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = showError('خطا: ' . $e->getMessage());
    }
}

// دریافت اطلاعات مرتبط
$topics = $pdo->prepare("SELECT * FROM content_topics WHERE company_id = ? ORDER BY name");
$topics->execute([$content['company_id']]);
$topics = $topics->fetchAll();

$selectedTopics = $pdo->prepare("SELECT topic_id FROM content_topic_relations WHERE content_id = ?");
$selectedTopics->execute([$contentId]);
$selectedTopics = $selectedTopics->fetchAll(PDO::FETCH_COLUMN);

$audiences = $pdo->prepare("SELECT * FROM content_audiences WHERE company_id = ? ORDER BY name");
$audiences->execute([$content['company_id']]);
$audiences = $audiences->fetchAll();

$selectedAudiences = $pdo->prepare("SELECT audience_id FROM content_audience_content WHERE content_id = ?");
$selectedAudiences->execute([$contentId]);
$selectedAudiences = $selectedAudiences->fetchAll(PDO::FETCH_COLUMN);

$types = $pdo->prepare("SELECT * FROM content_types WHERE company_id = ? ORDER BY name");
$types->execute([$content['company_id']]);
$types = $types->fetchAll();

$selectedTypes = $pdo->prepare("SELECT type_id FROM content_type_relations WHERE content_id = ?");
$selectedTypes->execute([$contentId]);
$selectedTypes = $selectedTypes->fetchAll(PDO::FETCH_COLUMN);

$platforms = $pdo->prepare("SELECT * FROM content_platforms WHERE company_id = ? ORDER BY name");
$platforms->execute([$content['company_id']]);
$platforms = $platforms->fetchAll();

$selectedPlatforms = $pdo->prepare("SELECT platform_id FROM content_platform_relations WHERE content_id = ?");
$selectedPlatforms->execute([$contentId]);
$selectedPlatforms = $selectedPlatforms->fetchAll(PDO::FETCH_COLUMN);

$tasks = $pdo->prepare("SELECT * FROM content_tasks WHERE company_id = ? ORDER BY name");
$tasks->execute([$content['company_id']]);
$tasks = $tasks->fetchAll();

$taskAssignments = $pdo->prepare("SELECT task_id, personnel_id FROM content_task_assignments WHERE content_id = ?");
$taskAssignments->execute([$contentId]);
$taskAssignments = $taskAssignments->fetchAll(PDO::FETCH_KEY_PAIR);

$personnel = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM personnel WHERE company_id = ? AND is_active = 1 ORDER BY CONCAT(first_name, ' ', last_name)");
$personnel->execute([$content['company_id']]);
$personnel = $personnel->fetchAll();

$productionStatuses = $pdo->prepare("SELECT * FROM content_production_statuses WHERE company_id = ? ORDER BY name");
$productionStatuses->execute([$content['company_id']]);
$productionStatuses = $productionStatuses->fetchAll();

$publishStatuses = $pdo->prepare("SELECT * FROM content_publish_statuses WHERE company_id = ? ORDER BY name");
$publishStatuses->execute([$content['company_id']]);
$publishStatuses = $publishStatuses->fetchAll();

$formats = $pdo->prepare("SELECT * FROM content_formats WHERE company_id = ? ORDER BY name");
$formats->execute([$content['company_id']]);
$formats = $formats->fetchAll();

$postPublishProcesses = $pdo->prepare("
    SELECT 
        cpp.*,
        GROUP_CONCAT(pppr.platform_id) as platform_ids
    FROM content_post_publish_processes cpp
    LEFT JOIN post_publish_platform_relations pppr ON cpp.id = pppr.process_id
    WHERE cpp.content_id = ?
    GROUP BY cpp.id
    ORDER BY cpp.days_after");
$postPublishProcesses->execute([$contentId]);
$postPublishProcesses = $postPublishProcesses->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>ویرایش محتوا</h1>
    <div>
        <a href="content_view.php?id=<?php echo $contentId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به جزئیات محتوا
        </a>
    </div>
</div>

<?php echo $message; ?>

<form method="POST" action="">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">اطلاعات اصلی محتوا</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="title" class="form-label">عنوان محتوا *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($content['title']); ?>" required>
                        </div>
                    </div>

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
                                                   <?php echo in_array($topic['id'], $selectedTopics) ? 'checked' : ''; ?>>
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
                                                   <?php echo in_array($audience['id'], $selectedAudiences) ? 'checked' : ''; ?>>
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
                            <label class="form-label">انواع محتوا</label>
                            <div class="row">
                                <?php foreach ($types as $type): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="types[]" 
                                                   id="type_<?php echo $type['id']; ?>" 
                                                   value="<?php echo $type['id']; ?>"
                                                   <?php echo in_array($type['id'], $selectedTypes) ? 'checked' : ''; ?>>
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
                                                   <?php echo in_array($platform['id'], $selectedPlatforms) ? 'checked' : ''; ?>>
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
                        <div class="col-md-6">
                            <label for="publish_date" class="form-label">تاریخ انتشار *</label>
                            <input type="date" class="form-control" id="publish_date" name="publish_date" 
                                   value="<?php echo $content['publish_date']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="publish_time" class="form-label">ساعت انتشار</label>
                            <input type="time" class="form-control" id="publish_time" name="publish_time" 
                                   value="<?php echo $content['publish_time']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="production_status" class="form-label">وضعیت تولید *</label>
                            <select class="form-select" id="production_status" name="production_status" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($productionStatuses as $status): ?>
                                    <option value="<?php echo $status['id']; ?>" 
                                            <?php echo $content['production_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                                        <?php echo $status['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="publish_status" class="form-label">وضعیت انتشار *</label>
                            <select class="form-select" id="publish_status" name="publish_status" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($publishStatuses as $status): ?>
                                    <option value="<?php echo $status['id']; ?>" 
                                            <?php echo $content['publish_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                                        <?php echo $status['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="scenario" class="form-label">سناریو محتوا</label>
                        <textarea class="form-control" id="scenario" name="scenario" rows="4"><?php echo htmlspecialchars($content['scenario']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($content['description']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">وظایف و مسئولان</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($tasks as $task): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><?php echo $task['name']; ?></label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-select" name="tasks[<?php echo $task['id']; ?>]">
                                    <option value="">انتخاب مسئول</option>
                                    <?php foreach ($personnel as $person): ?>
                                        <option value="<?php echo $person['id']; ?>" 
                                                <?php echo isset($taskAssignments[$task['id']]) && $taskAssignments[$task['id']] == $person['id'] ? 'selected' : ''; ?>>
                                            <?php echo $person['full_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">فرآیند پس از انتشار</h5>
                </div>
                <div class="card-body">
                    <div id="post_publish_processes">
                        <?php if (count($postPublishProcesses) > 0): ?>
                            <!-- Procesos existentes -->
                            <?php foreach ($postPublishProcesses as $index => $process): ?>
                                <div class="post-publish-process mb-4">
                                    <?php if ($index > 0): ?>
                                        <hr class="my-4">
                                    <?php endif; ?>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">فرمت محتوا</label>
                                            <select class="form-select" name="post_publish_format[]">
                                                <option value="">انتخاب کنید</option>
                                                <?php foreach ($formats as $format): ?>
                                                    <option value="<?php echo $format['id']; ?>" 
                                                            <?php echo $process['format_id'] == $format['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $format['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">تعداد روز پس از انتشار</label>
                                            <input type="number" class="form-control" name="post_publish_days[]" 
                                                   min="0" value="<?php echo $process['days_after']; ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">ساعت انتشار</label>
                                            <input type="time" class="form-control" name="post_publish_time[]" 
                                                   value="<?php echo $process['publish_time']; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">پلتفرم‌های انتشار</label>
                                            <div class="row">
                                                <?php 
                                                $selectedPlatformIds = explode(',', $process['platform_ids']);
                                                foreach ($platforms as $platform): 
                                                ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="post_publish_platforms[<?php echo $index; ?>][]" 
                                                                   value="<?php echo $platform['id']; ?>"
                                                                   <?php echo in_array($platform['id'], $selectedPlatformIds) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">
                                                                <?php echo $platform['name']; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($index > 0): ?>
                                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-process">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Formulario vacío cuando no hay procesos existentes -->
                            <div class="post-publish-process mb-4">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">فرمت محتوا</label>
                                        <select class="form-select" name="post_publish_format[]">
                                            <option value="">انتخاب کنید</option>
                                            <?php foreach ($formats as $format): ?>
                                                <option value="<?php echo $format['id']; ?>">
                                                    <?php echo $format['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">تعداد روز پس از انتشار</label>
                                        <input type="number" class="form-control" name="post_publish_days[]" min="0" value="7">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">ساعت انتشار</label>
                                        <input type="time" class="form-control" name="post_publish_time[]" value="10:00">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">پلتفرم‌های انتشار</label>
                                        <div class="row">
                                            <?php foreach ($platforms as $platform): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="post_publish_platforms[0][]" 
                                                               value="<?php echo $platform['id']; ?>">
                                                        <label class="form-check-label">
                                                            <?php echo $platform['name']; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn btn-success" id="add_post_publish_process">
                        <i class="fas fa-plus"></i> افزودن فرآیند جدید
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <button type="submit" name="edit_content" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-save"></i> ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // افزودن فرآیند جدید
    document.getElementById('add_post_publish_process').addEventListener('click', function() {
        const processCount = document.querySelectorAll('.post-publish-process').length;
        const firstProcess = document.querySelector('.post-publish-process');
        const newProcess = firstProcess.cloneNode(true);
        
        // پاک کردن مقادیر قبلی
        newProcess.querySelector('input[type="number"]').value = '7';
        newProcess.querySelector('input[type="time"]').value = '10:00';
        newProcess.querySelector('select').value = '';
        
        // به‌روزرسانی نام‌های فیلدها
        newProcess.querySelector('input[name^="post_publish_days"]').name = 'post_publish_days[]';
        newProcess.querySelector('input[name^="post_publish_time"]').name = 'post_publish_time[]';
        newProcess.querySelector('select[name^="post_publish_format"]').name = 'post_publish_format[]';
        
        // به‌روزرسانی نام پلتفرم‌ها
        const platformCheckboxes = newProcess.querySelectorAll('input[type="checkbox"][name^="post_publish_platforms"]');
        platformCheckboxes.forEach(checkbox => {
            checkbox.name = 'post_publish_platforms[' + processCount + '][]';
            checkbox.checked = false;
        });
        
        // افزودن دکمه حذف
        let removeButton = newProcess.querySelector('.remove-process');
        if (!removeButton) {
            removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-danger btn-sm mt-2 remove-process';
            removeButton.innerHTML = '<i class="fas fa-trash"></i> حذف';
            newProcess.appendChild(removeButton);
        }
        
        // افزودن خط جداکننده
        const divider = document.createElement('hr');
        divider.className = 'my-4';
        document.getElementById('post_publish_processes').appendChild(divider);
        
        document.getElementById('post_publish_processes').appendChild(newProcess);
    });

    // حذف فرآیند
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-process')) {
            const process = e.target.closest('.post-publish-process');
            const nextElement = process.nextElementSibling;
            const prevElement = process.previousElementSibling;
            
            // حذف خط جداکننده اگر وجود دارد
            if (nextElement && nextElement.tagName === 'HR') {
                nextElement.remove();
            } else if (prevElement && prevElement.tagName === 'HR') {
                prevElement.remove();
            }
            
            process.remove();
        }
    });
});
</script>

<?php include 'footer.php'; ?>