<?php
// coach_report_view.php - نمایش جزئیات گزارش کوچ
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی وجود شناسه گزارش
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('coach_report_list.php');
}

$reportId = clean($_GET['id']);

// دریافت اطلاعات گزارش
$stmt = $pdo->prepare("SELECT cr.*, 
                      CONCAT(coach.first_name, ' ', coach.last_name) as coach_name,
                      CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
                      c.name as company_name
                      FROM coach_reports cr
                      JOIN personnel coach ON cr.coach_id = coach.id
                      JOIN personnel receiver ON cr.receiver_id = receiver.id
                      JOIN companies c ON cr.company_id = c.id
                      WHERE cr.id = ?");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    redirect('coach_report_list.php');
}

// بررسی دسترسی
$hasAccess = false;

if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')) {
    // مدیر سیستم (میلاد احمدی) دسترسی دارد
    $hasAccess = true;
} else if ($report['receiver_id'] == $_SESSION['user_id']) {
    // دریافت کننده گزارش دسترسی دارد
    $hasAccess = true;
}

if (!$hasAccess) {
    redirect('index.php');
}

// بررسی ستون‌های موجود در جدول coach_reports
$tableInfoQuery = "DESCRIBE coach_reports";
$tableInfoStmt = $pdo->query($tableInfoQuery);
$tableColumns = $tableInfoStmt->fetchAll(PDO::FETCH_COLUMN);

// ثبت بازخورد توسط دریافت کننده
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback']) && $report['receiver_id'] == $_SESSION['user_id']) {
    $feedback = clean($_POST['feedback']);
    
    try {
        // بررسی وجود ستون‌های مختلف برای بازخورد
        if (in_array('receiver_feedback', $tableColumns)) {
            $feedbackColumn = 'receiver_feedback';
        } elseif (in_array('feedback', $tableColumns)) {
            $feedbackColumn = 'feedback';
        } elseif (in_array('ceo_feedback', $tableColumns)) {
            $feedbackColumn = 'ceo_feedback';
        } else {
            // اگر هیچ ستون بازخوردی وجود نداشت، خطا نمایش بده
            throw new Exception('ستون مناسب برای ذخیره بازخورد در دیتابیس یافت نشد.');
        }
        
        // بررسی وجود ستون تاریخ بازخورد
        $dateColumn = '';
        if (in_array('feedback_date', $tableColumns)) {
            $dateColumn = ', feedback_date = NOW()';
        } elseif (in_array('reviewed_at', $tableColumns)) {
            $dateColumn = ', reviewed_at = NOW()';
        }
        
        // ذخیره بازخورد در ستون مناسب
        $stmt = $pdo->prepare("UPDATE coach_reports SET {$feedbackColumn} = ?{$dateColumn} WHERE id = ?");
        $stmt->execute([$feedback, $reportId]);
        
        $message = showSuccess('بازخورد شما با موفقیت ثبت شد.');
        
        // بروزرسانی اطلاعات گزارش
        $stmt = $pdo->prepare("SELECT cr.*, 
                              CONCAT(coach.first_name, ' ', coach.last_name) as coach_name,
                              CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
                              c.name as company_name
                              FROM coach_reports cr
                              JOIN personnel coach ON cr.coach_id = coach.id
                              JOIN personnel receiver ON cr.receiver_id = receiver.id
                              JOIN companies c ON cr.company_id = c.id
                              WHERE cr.id = ?");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();
    } catch (PDOException $e) {
        $message = showError('خطا در ثبت بازخورد: ' . $e->getMessage());
    } catch (Exception $e) {
        $message = showError($e->getMessage());
    }
}

// دریافت پرسنل مرتبط با این گزارش
$stmt = $pdo->prepare("SELECT cp.*, 
                      CONCAT(p.first_name, ' ', p.last_name) as personnel_name,
                      JSON_EXTRACT(cp.statistics_json, '$.report_count') as report_count
                      FROM coach_report_personnel cp
                      JOIN personnel p ON cp.personnel_id = p.id
                      WHERE cp.coach_report_id = ?");
$stmt->execute([$reportId]);
$reportPersonnel = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>جزئیات گزارش کوچ</h1>
    <a href="coach_report_list.php" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت به لیست گزارش‌ها
    </a>
</div>

<?php echo $message; ?>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">اطلاعات کلی گزارش</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <p><strong>تاریخ گزارش:</strong> <?php echo $report['report_date']; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>دوره گزارش:</strong> از <?php echo $report['date_from']; ?> تا <?php echo $report['date_to']; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>شرکت:</strong> <?php echo $report['company_name']; ?></p>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <p><strong>کوچ:</strong> <?php echo $report['coach_name']; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>دریافت کننده:</strong> <?php echo $report['receiver_name']; ?></p>
            </div>
        </div>
        
        <?php if (!empty($report['general_comments'])): ?>
        <div class="row mb-3">
            <div class="col-md-12">
                <p><strong>توضیحات کلی:</strong></p>
                <div class="card">
                    <div class="card-body bg-light">
                        <?php echo nl2br(htmlspecialchars($report['general_comments'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($reportPersonnel as $person): ?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">گزارش عملکرد: <?php echo $person['personnel_name']; ?></h5>
    </div>
    <div class="card-body">
        <?php
        // دریافت آمار از JSON
        $statistics = json_decode($person['statistics_json'], true);
        $topCategories = isset($statistics['top_categories']) ? $statistics['top_categories'] : [];
        $categories = isset($statistics['categories']) ? $statistics['categories'] : [];
        ?>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>تعداد گزارش‌های ثبت شده:</strong> <?php echo $person['report_count']; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>امتیاز عملکرد:</strong> <?php echo $person['coach_score']; ?> از 10</p>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <p><strong>دسته‌بندی‌های استفاده شده:</strong> 
                    <?php echo empty($categories) ? 'موردی یافت نشد' : implode('، ', $categories); ?>
                </p>
            </div>
        </div>
        
        <?php if (!empty($topCategories)): ?>
        <div class="row mb-3">
            <div class="col-md-12">
                <p><strong>5 دسته‌بندی پر تکرار:</strong></p>
                <ul>
                    <?php foreach ($topCategories as $category): ?>
                        <li><?php echo $category['name']; ?> (<?php echo $category['count']; ?> بار)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($person['coach_comment'])): ?>
        <div class="row mb-3">
            <div class="col-md-12">
                <p><strong>نظر کوچ:</strong></p>
                <div class="card">
                    <div class="card-body bg-light">
                        <?php echo nl2br(htmlspecialchars($person['coach_comment'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php
// تعیین ستون بازخورد
$feedbackColumn = null;
if (in_array('receiver_feedback', $tableColumns) && isset($report['receiver_feedback'])) {
    $feedbackColumn = 'receiver_feedback';
} elseif (in_array('feedback', $tableColumns) && isset($report['feedback'])) {
    $feedbackColumn = 'feedback';
} elseif (in_array('ceo_feedback', $tableColumns) && isset($report['ceo_feedback'])) {
    $feedbackColumn = 'ceo_feedback';
}

// تعیین ستون تاریخ بازخورد
$dateColumn = null;
if (in_array('feedback_date', $tableColumns) && isset($report['feedback_date'])) {
    $dateColumn = 'feedback_date';
} elseif (in_array('reviewed_at', $tableColumns) && isset($report['reviewed_at'])) {
    $dateColumn = 'reviewed_at';
}

// نمایش بازخورد اگر وجود داشته باشد
if ($feedbackColumn && !empty($report[$feedbackColumn])): 
?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">بازخورد دریافت کننده</h5>
    </div>
    <div class="card-body">
        <div class="card">
            <div class="card-body bg-light">
                <?php echo nl2br(htmlspecialchars($report[$feedbackColumn])); ?>
            </div>
        </div>
        <?php if ($dateColumn && !empty($report[$dateColumn])): ?>
        <p class="text-muted mt-2">ثبت شده در: <?php echo $report[$dateColumn]; ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php 
// نمایش فرم ثبت بازخورد فقط اگر کاربر دریافت کننده گزارش است و هنوز بازخوردی ثبت نکرده
if ($report['receiver_id'] == $_SESSION['user_id'] && (!$feedbackColumn || empty($report[$feedbackColumn]))): 
?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">ثبت بازخورد</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="feedback" class="form-label">بازخورد شما:</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="5" required></textarea>
            </div>
            <div class="text-center">
                <button type="submit" name="submit_feedback" class="btn btn-primary px-5">ثبت بازخورد</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>