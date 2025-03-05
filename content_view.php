<?php
// content_view.php - نمایش جزئیات محتوا
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
    WHERE c.id = ? AND c.company_id = ?");
$stmt->execute([$contentId, $companyId]);
$content = $stmt->fetch();

if (!$content) {
    redirect('content_list.php?company=' . $companyId);
}

// بررسی دسترسی برای ویرایش
$canEdit = false;
if (isAdmin()) {
    $canEdit = true;
} elseif (isCEO() || $_SESSION['user_id'] == $content['created_by']) {
    $canEdit = true;
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

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>جزئیات محتوا</h1>
    <div>
        <?php if ($canEdit): ?>
            <a href="content_edit.php?id=<?php echo $contentId; ?>&company=<?php echo $companyId; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> ویرایش محتوا
            </a>
        <?php endif; ?>
        <a href="content_list.php?company=<?php echo $companyId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست محتواها
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">اطلاعات اصلی محتوا</h5>
            </div>
            <div class="card-body">
                <h2 class="mb-4"><?php echo htmlspecialchars($content['title']); ?></h2>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>تاریخ انتشار:</strong> 
                        <?php echo $content['publish_date']; ?> 
                        ساعت 
                        <?php echo $content['publish_time']; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>ایجاد کننده:</strong> 
                        <?php echo $content['creator_name']; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>وضعیت تولید:</strong> 
                        <span class="badge bg-info"><?php echo $content['production_status']; ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>وضعیت انتشار:</strong> 
                        <span class="badge bg-secondary"><?php echo $content['publish_status']; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($topics)): ?>
                    <div class="mb-3">
                        <strong>موضوعات کلی:</strong> 
                        <?php echo implode('، ', $topics); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($audiences)): ?>
                    <div class="mb-3">
                        <strong>مخاطبین هدف:</strong> 
                        <?php echo implode('، ', $audiences); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($types)): ?>
                    <div class="mb-3">
                        <strong>نوع محتوا:</strong> 
                        <?php echo implode('، ', $types); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($platforms)): ?>
                    <div class="mb-3">
                        <strong>پلتفرم‌های انتشار:</strong> 
                        <?php echo implode('، ', $platforms); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">سناریو و توضیحات</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($content['scenario'])): ?>
                    <div class="mb-3">
                        <strong>سناریو:</strong>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($content['scenario'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['description'])): ?>
                    <div class="mb-3">
                        <strong>توضیحات:</strong>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($content['description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <?php if (!empty($tasks)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">وظایف و مسئولان</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo $task['task_name']; ?></h6>
                                        <small class="text-muted">مسئول: <?php echo $task['assigned_person']; ?></small>
                                    </div>
                                    <?php if ($task['is_completed']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i>
                                            <?php echo $task['completion_date']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">در حال انجام</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($postPublishProcesses)): ?>
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="mb-0">فرآیند پس از انتشار</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($postPublishProcesses as $process): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo $process['format_name']; ?></h6>
                                    <div class="text-muted small mb-2">
                                        <?php 
                                        if ($process['days_after'] == 0) {
                                            echo 'همان روز';
                                        } else {
                                            echo $process['days_after'] . ' روز بعد';
                                        }
                                        echo ' - ساعت ' . $process['publish_time'];
                                        ?>
                                    </div>
                                    <?php if (!empty($process['platforms'])): ?>
                                        <div class="small">
                                            <strong>پلتفرم‌ها:</strong> <?php echo $process['platforms']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #6f42c1;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #6f42c1;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 7px;
    top: 15px;
    height: calc(100% + 5px);
    width: 2px;
    background-color: #6f42c1;
}

.bg-purple {
    background-color: #6f42c1;
}
</style>

<?php include 'footer.php'; ?>