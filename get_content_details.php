<?php
// get_content_details.php - نمایش جزئیات محتوا برای مودال تقویم
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    exit('خطا: دسترسی غیرمجاز');
}

// دریافت شناسه محتوا
$contentId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if ($contentId <= 0) {
    exit('خطا: شناسه محتوا نامعتبر است.');
}

// دریافت اطلاعات محتوا با تمام جزئیات مرتبط
$stmt = $pdo->prepare("SELECT c.*, 
    CONCAT(p.first_name, ' ', p.last_name) as creator_name, 
    ps.name as production_status, 
    pbs.name as publish_status,
    comp.name as company_name
    FROM contents c
    LEFT JOIN personnel p ON c.created_by = p.id
    LEFT JOIN content_production_statuses ps ON c.production_status_id = ps.id
    LEFT JOIN content_publish_statuses pbs ON c.publish_status_id = pbs.id
    LEFT JOIN companies comp ON c.company_id = comp.id
    WHERE c.id = ?");
$stmt->execute([$contentId]);
$content = $stmt->fetch();

if (!$content) {
    exit('خطا: محتوای مورد نظر یافت نشد.');
}

// بررسی دسترسی کاربر به شرکت
if (!isAdmin() && $content['company_id'] != $_SESSION['company_id']) {
    exit('خطا: شما به این محتوا دسترسی ندارید.');
}

// دریافت موضوعات مرتبط
$topicStmt = $pdo->prepare("SELECT t.name 
    FROM content_topics t
    JOIN content_topic_relations ctr ON t.id = ctr.topic_id
    WHERE ctr.content_id = ?");
$topicStmt->execute([$contentId]);
$topics = $topicStmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت مخاطبین هدف
$audienceStmt = $pdo->prepare("SELECT a.name 
    FROM content_audiences a
    JOIN content_audience_content cac ON a.id = cac.audience_id
    WHERE cac.content_id = ?");
$audienceStmt->execute([$contentId]);
$audiences = $audienceStmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت انواع محتوا
$typeStmt = $pdo->prepare("SELECT t.name 
    FROM content_types t
    JOIN content_type_relations ctr ON t.id = ctr.type_id
    WHERE ctr.content_id = ?");
$typeStmt->execute([$contentId]);
$types = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت پلتفرم‌های انتشار
$platformStmt = $pdo->prepare("SELECT p.name 
    FROM content_platforms p
    JOIN content_platform_relations cpr ON p.id = cpr.platform_id
    WHERE cpr.content_id = ?");
$platformStmt->execute([$contentId]);
$platforms = $platformStmt->fetchAll(PDO::FETCH_COLUMN);

// دریافت وظایف و مسئولان
$taskStmt = $pdo->prepare("SELECT 
    ct.name as task_name, 
    CONCAT(p.first_name, ' ', p.last_name) as assigned_person,
    cta.is_completed,
    cta.completion_date
    FROM content_task_assignments cta
    JOIN content_tasks ct ON cta.task_id = ct.id
    JOIN personnel p ON cta.personnel_id = p.id
    WHERE cta.content_id = ?");
$taskStmt->execute([$contentId]);
$tasks = $taskStmt->fetchAll();

// دریافت فرآیندهای پس از انتشار
$postPublishStmt = $pdo->prepare("SELECT 
    cpp.id,
    cf.name as format_name,
    cpp.days_after,
    cpp.publish_time,
    GROUP_CONCAT(cp.name SEPARATOR '، ') as platforms
    FROM content_post_publish_processes cpp
    LEFT JOIN content_formats cf ON cpp.format_id = cf.id
    LEFT JOIN post_publish_platform_relations pppr ON cpp.id = pppr.process_id
    LEFT JOIN content_platforms cp ON pppr.platform_id = cp.id
    WHERE cpp.content_id = ?
    GROUP BY cpp.id
    ORDER BY cpp.days_after");
$postPublishStmt->execute([$contentId]);
$postPublishProcesses = $postPublishStmt->fetchAll();

// تاریخ انتشار به شمسی (اختیاری)
$publishDate = $content['publish_date'];
$publishTime = $content['publish_time'] ?? '00:00';

// کد HTML خروجی
?>
<div class="content-details">
    <h3 class="mb-4"><?php echo htmlspecialchars($content['title']); ?></h3>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-2">
                <strong>تاریخ انتشار:</strong> 
                <span><?php echo $publishDate; ?></span>
                <?php if (!empty($publishTime)): ?>
                    <span>ساعت <?php echo $publishTime; ?></span>
                <?php endif; ?>
            </div>
            <div class="mb-2">
                <strong>وضعیت تولید:</strong> 
                <span class="badge bg-info"><?php echo $content['production_status']; ?></span>
            </div>
            <div class="mb-2">
                <strong>وضعیت انتشار:</strong> 
                <span class="badge bg-secondary"><?php echo $content['publish_status']; ?></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <strong>ایجاد کننده:</strong> 
                <span><?php echo $content['creator_name']; ?></span>
            </div>
            <div class="mb-2">
                <strong>تاریخ ایجاد:</strong> 
                <span><?php echo $content['created_at']; ?></span>
            </div>
            <?php if (!empty($content['updated_at'])): ?>
                <div class="mb-2">
                    <strong>آخرین بروزرسانی:</strong> 
                    <span><?php echo $content['updated_at']; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <?php if (!empty($topics)): ?>
                <div class="mb-2">
                    <strong>موضوعات:</strong> 
                    <?php foreach ($topics as $topic): ?>
                        <span class="badge bg-secondary me-1"><?php echo $topic; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($audiences)): ?>
                <div class="mb-2">
                    <strong>مخاطبین هدف:</strong> 
                    <?php foreach ($audiences as $audience): ?>
                        <span class="badge bg-info me-1"><?php echo $audience; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($types)): ?>
                <div class="mb-2">
                    <strong>نوع محتوا:</strong> 
                    <?php foreach ($types as $type): ?>
                        <span class="badge bg-success me-1"><?php echo $type; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($platforms)): ?>
                <div class="mb-2">
                    <strong>پلتفرم‌های انتشار:</strong> 
                    <?php foreach ($platforms as $platform): ?>
                        <span class="badge bg-warning text-dark me-1"><?php echo $platform; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($content['scenario']) || !empty($content['description'])): ?>
        <div class="mb-3">
            <div class="accordion" id="contentDetailsAccordion">
                <?php if (!empty($content['scenario'])): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="scenarioHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#scenarioCollapse">
                                سناریو محتوا
                            </button>
                        </h2>
                        <div id="scenarioCollapse" class="accordion-collapse collapse" data-bs-parent="#contentDetailsAccordion">
                            <div class="accordion-body">
                                <?php echo nl2br(htmlspecialchars($content['scenario'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['description'])): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="descriptionHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#descriptionCollapse">
                                توضیحات
                            </button>
                        </h2>
                        <div id="descriptionCollapse" class="accordion-collapse collapse" data-bs-parent="#contentDetailsAccordion">
                            <div class="accordion-body">
                                <?php echo nl2br(htmlspecialchars($content['description'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php if (!empty($tasks)): ?>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">وظایف و مسئولان</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($tasks as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $task['task_name']; ?></strong><br>
                                        <small><?php echo $task['assigned_person']; ?></small>
                                    </div>
                                    <?php if ($task['is_completed']): ?>
                                        <span class="badge bg-success rounded-pill" title="<?php echo $task['completion_date']; ?>">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($postPublishProcesses)): ?>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">فرآیند پس از انتشار</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($postPublishProcesses as $process): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo $process['format_name']; ?></strong>
                                        <span class="text-muted">
                                            <?php 
                                            if ($process['days_after'] == 0) {
                                                echo 'همان روز';
                                            } else {
                                                echo $process['days_after'] . ' روز بعد';
                                            }
                                            echo ' - ساعت ' . $process['publish_time'];
                                            ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($process['platforms'])): ?>
                                        <div class="mt-1">
                                            <small class="text-muted">پلتفرم‌ها: <?php echo $process['platforms']; ?></small>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>