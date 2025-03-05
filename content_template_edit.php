<?php
// content_template_edit.php - ویرایش قالب محتوایی
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    redirect('login.php');
}

// دریافت شناسه قالب
$templateId = isset($_GET['id']) && is_numeric($_GET['id']) ? clean($_GET['id']) : null;

if (!$templateId) {
    redirect('content_templates.php');
}

// دریافت اطلاعات قالب
$stmt = $pdo->prepare("SELECT * FROM content_templates WHERE id = ?");
$stmt->execute([$templateId]);
$template = $stmt->fetch();

if (!$template) {
    redirect('content_templates.php');
}

// بررسی دسترسی به قالب شرکت
$companyId = isAdmin() ? $template['company_id'] : $_SESSION['company_id'];
if ($template['company_id'] != $companyId) {
    redirect('content_templates.php');
}

// دریافت اطلاعات مورد نیاز
$topics = $pdo->prepare("SELECT * FROM content_topics WHERE company_id = ? ORDER BY name");
$topics->execute([$companyId]);
$topics = $topics->fetchAll();

$audiences = $pdo->prepare("SELECT * FROM content_audiences WHERE company_id = ? ORDER BY name");
$audiences->execute([$companyId]);
$audiences = $audiences->fetchAll();

$platforms = $pdo->prepare("SELECT * FROM content_platforms WHERE company_id = ? ORDER BY name");
$platforms->execute([$companyId]);
$platforms = $platforms->fetchAll();

$types = $pdo->prepare("SELECT * FROM content_types WHERE company_id = ? ORDER BY name");
$types->execute([$companyId]);
$types = $types->fetchAll();

$tasks = $pdo->prepare("SELECT * FROM content_tasks WHERE company_id = ? ORDER BY name");
$tasks->execute([$companyId]);
$tasks = $tasks->fetchAll();

$personnel = $pdo->prepare("SELECT * FROM personnel WHERE company_id = ? ORDER BY full_name");
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

// دریافت موضوعات انتخاب شده
$stmt = $pdo->prepare("SELECT topic_id FROM content_template_topic_relations WHERE template_id = ?");
$stmt->execute([$templateId]);
$selectedTopics = $stmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت مخاطبین انتخاب شده
$stmt = $pdo->prepare("SELECT audience_id FROM content_template_audience_relations WHERE template_id = ?");
$stmt->execute([$templateId]);
$selectedAudiences = $stmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت پلتفرم‌های انتخاب شده
$stmt = $pdo->prepare("SELECT platform_id FROM content_template_platform_relations WHERE template_id = ?");
$stmt->execute([$templateId]);
$selectedPlatforms = $stmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت انواع محتوای انتخاب شده
$stmt = $pdo->prepare("SELECT type_id FROM content_template_type_relations WHERE template_id = ?");
$stmt->execute([$templateId]);
$selectedTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت وظایف و مسئولین
$stmt = $pdo->prepare("SELECT task_id, responsible_id FROM content_template_task_relations WHERE template_id = ?");
$stmt->execute([$templateId]);
$taskRelations = $stmt->fetchAll();

// دریافت فرآیندهای پس از انتشار
$stmt = $pdo->prepare("SELECT * FROM content_template_post_publish_processes WHERE template_id = ?");
$stmt->execute([$templateId]);
$postPublishProcesses = $stmt->fetchAll();

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // به‌روزرسانی اطلاعات اصلی قالب
        $stmt = $pdo->prepare("UPDATE content_templates SET 
            name = ?, 
            description = ?,
            production_status_id = ?,
            publish_status_id = ?
            WHERE id = ?");
        
        $stmt->execute([
            clean($_POST['name']),
            $_POST['description'],
            clean($_POST['production_status']),
            clean($_POST['publish_status']),
            $templateId
        ]);

        // به‌روزرسانی موضوعات
        $stmt = $pdo->prepare("DELETE FROM content_template_topic_relations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['topics']) && is_array($_POST['topics'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_topic_relations (template_id, topic_id) VALUES (?, ?)");
            foreach ($_POST['topics'] as $topicId) {
                $stmt->execute([$templateId, clean($topicId)]);
            }
        }

        // به‌روزرسانی مخاطبین
        $stmt = $pdo->prepare("DELETE FROM content_template_audience_relations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['audiences']) && is_array($_POST['audiences'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_audience_relations (template_id, audience_id) VALUES (?, ?)");
            foreach ($_POST['audiences'] as $audienceId) {
                $stmt->execute([$templateId, clean($audienceId)]);
            }
        }

        // به‌روزرسانی پلتفرم‌ها
        $stmt = $pdo->prepare("DELETE FROM content_template_platform_relations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['platforms']) && is_array($_POST['platforms'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_platform_relations (template_id, platform_id) VALUES (?, ?)");
            foreach ($_POST['platforms'] as $platformId) {
                $stmt->execute([$templateId, clean($platformId)]);
            }
        }

        // به‌روزرسانی انواع محتوا
        $stmt = $pdo->prepare("DELETE FROM content_template_type_relations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['types']) && is_array($_POST['types'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_type_relations (template_id, type_id) VALUES (?, ?)");
            foreach ($_POST['types'] as $typeId) {
                $stmt->execute([$templateId, clean($typeId)]);
            }
        }

        // به‌روزرسانی وظایف و مسئولین
        $stmt = $pdo->prepare("DELETE FROM content_template_task_relations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['tasks']) && is_array($_POST['tasks'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_task_relations (template_id, task_id, responsible_id) VALUES (?, ?, ?)");
            foreach ($_POST['tasks'] as $taskId => $responsibleId) {
                $stmt->execute([$templateId, clean($taskId), clean($responsibleId)]);
            }
        }

        // به‌روزرسانی فرآیندهای پس از انتشار
        $stmt = $pdo->prepare("DELETE FROM content_template_post_publish_processes WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (isset($_POST['post_publish']) && is_array($_POST['post_publish'])) {
            $stmt = $pdo->prepare("INSERT INTO content_template_post_publish_processes 
                (template_id, format_id, platform_ids, days_after, publish_time) 
                VALUES (?, ?, ?, ?, ?)");
            
            foreach ($_POST['post_publish'] as $process) {
                $stmt->execute([
                    $templateId,
                    clean($process['format']),
                    json_encode($process['platforms']),
                    clean($process['days_after']),
                    clean($process['publish_time'])
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = 'قالب با موفقیت به‌روزرسانی شد.';
        redirect('content_templates.php?company=' . $companyId);

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در به‌روزرسانی قالب: ' . $e->getMessage();
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>ویرایش قالب محتوایی</h1>
    <div>
        <a href="content_templates.php?company=<?php echo $companyId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <!-- نام قالب -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">نام قالب</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $template['name']; ?>" required>
                </div>

                <!-- موضوعات کلی -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">موضوعات کلی</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($topics as $topic): ?>
                            <div class="form-check">
                                <input type="checkbox" name="topics[]" value="<?php echo $topic['id']; ?>" 
                                       class="form-check-input" id="topic_<?php echo $topic['id']; ?>"
                                       <?php echo in_array($topic['id'], $selectedTopics) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="topic_<?php echo $topic['id']; ?>">
                                    <?php echo $topic['name']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- مخاطبین هدف -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">مخاطبین هدف</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($audiences as $audience): ?>
                            <div class="form-check">
                                <input type="checkbox" name="audiences[]" value="<?php echo $audience['id']; ?>" 
                                       class="form-check-input" id="audience_<?php echo $audience['id']; ?>"
                                       <?php echo in_array($audience['id'], $selectedAudiences) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="audience_<?php echo $audience['id']; ?>">
                                    <?php echo $audience['name']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- نوع محتوا -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">نوع محتوا</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($types as $type): ?>
                            <div class="form-check">
                                <input type="checkbox" name="types[]" value="<?php echo $type['id']; ?>" 
                                       class="form-check-input" id="type_<?php echo $type['id']; ?>"
                                       <?php echo in_array($type['id'], $selectedTypes) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="type_<?php echo $type['id']; ?>">
                                    <?php echo $type['name']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- پلتفرم انتشار -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">پلتفرم انتشار</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($platforms as $platform): ?>
                            <div class="form-check">
                                <input type="checkbox" name="platforms[]" value="<?php echo $platform['id']; ?>" 
                                       class="form-check-input" id="platform_<?php echo $platform['id']; ?>"
                                       <?php echo in_array($platform['id'], $selectedPlatforms) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="platform_<?php echo $platform['id']; ?>">
                                    <?php echo $platform['name']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- وظایف و مسئولین -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">وظایف و مسئولین</label>
                    <div class="border rounded p-3">
                        <?php foreach ($tasks as $task): ?>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="tasks[<?php echo $task['id']; ?>]" 
                                               class="form-check-input task-checkbox" 
                                               id="task_<?php echo $task['id']; ?>"
                                               <?php echo isset($taskRelations[$task['id']]) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="task_<?php echo $task['id']; ?>">
                                            <?php echo $task['name']; ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select name="responsible[<?php echo $task['id']; ?>]" 
                                            class="form-select responsible-select" 
                                            <?php echo !isset($taskRelations[$task['id']]) ? 'disabled' : ''; ?>>
                                        <option value="">انتخاب مسئول</option>
                                        <?php foreach ($personnel as $person): ?>
                                            <option value="<?php echo $person['id']; ?>"
                                                    <?php echo isset($taskRelations[$task['id']]) && 
                                                           $taskRelations[$task['id']]['responsible_id'] == $person['id'] ? 
                                                           'selected' : ''; ?>>
                                                <?php echo $person['full_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- وضعیت تولید -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت تولید</label>
                    <select name="production_status" class="form-select" required>
                        <?php foreach ($productionStatuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>"
                                    <?php echo $template['production_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                                <?php echo $status['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- وضعیت انتشار -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت انتشار</label>
                    <select name="publish_status" class="form-select" required>
                        <?php foreach ($publishStatuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>"
                                    <?php echo $template['publish_status_id'] == $status['id'] ? 'selected' : ''; ?>>
                                <?php echo $status['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- توضیحات -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo $template['description']; ?></textarea>
                </div>

                <!-- فرآیندهای پس از انتشار -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">فرآیندهای پس از انتشار</label>
                    <div id="post-publish-processes">
                        <?php foreach ($postPublishProcesses as $index => $process): ?>
                            <div class="post-publish-process border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">فرمت محتوایی</label>
                                        <select name="post_publish[<?php echo $index; ?>][format]" class="form-select" required>
                                            <?php foreach ($formats as $format): ?>
                                                <option value="<?php echo $format['id']; ?>"
                                                        <?php echo $process['format_id'] == $format['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $format['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">پلتفرم‌ها</label>
                                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                            <?php 
                                            $processPlatforms = json_decode($process['platform_ids'], true);
                                            foreach ($platforms as $platform): 
                                            ?>
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           name="post_publish[<?php echo $index; ?>][platforms][]" 
                                                           value="<?php echo $platform['id']; ?>" 
                                                           class="form-check-input"
                                                           <?php echo in_array($platform['id'], $processPlatforms) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">
                                                        <?php echo $platform['name']; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">تعداد روز بعد</label>
                                        <input type="number" name="post_publish[<?php echo $index; ?>][days_after]" 
                                               class="form-control" value="<?php echo $process['days_after']; ?>" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">ساعت انتشار</label>
                                        <input type="time" name="post_publish[<?php echo $index; ?>][publish_time]" 
                                               class="form-control" value="<?php echo $process['publish_time']; ?>" required>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-process">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-success" id="add-process">
                        <i class="fas fa-plus"></i> افزودن فرآیند جدید
                    </button>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // فعال/غیرفعال کردن انتخاب مسئول بر اساس چک باکس وظیفه
    $('.task-checkbox').change(function() {
        $(this).closest('.row').find('.responsible-select').prop('disabled', !this.checked);
    });

    // افزودن فرآیند جدید پس از انتشار
    $('#add-process').click(function() {
        var index = $('.post-publish-process').length;
        var template = `
            <div class="post-publish-process border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">فرمت محتوایی</label>
                        <select name="post_publish[${index}][format]" class="form-select" required>
                            <?php foreach ($formats as $format): ?>
                                <option value="<?php echo $format['id']; ?>"><?php echo $format['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">پلتفرم‌ها</label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($platforms as $platform): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="post_publish[${index}][platforms][]" 
                                           value="<?php echo $platform['id']; ?>" class="form-check-input">
                                    <label class="form-check-label"><?php echo $platform['name']; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">تعداد روز بعد</label>
                        <input type="number" name="post_publish[${index}][days_after]" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">ساعت انتشار</label>
                        <input type="time" name="post_publish[${index}][publish_time]" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-process">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#post-publish-processes').append(template);
    });

    // حذف فرآیند پس از انتشار
    $(document).on('click', '.remove-process', function() {
        $(this).closest('.post-publish-process').remove();
    });
});
</script>

<?php include 'footer.php'; ?> 