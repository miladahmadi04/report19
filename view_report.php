<?php
// view_report.php - View a specific report with multiple items
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// Check if logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } elseif (isCEO()) {
        redirect('view_reports.php');
    } else {
        redirect('personnel_dashboard.php');
    }
}

$reportId = clean($_GET['id']);
$currentCompanyId = $_SESSION['company_id']; // دریافت شرکت فعال

// Get report details
$stmt = $pdo->prepare("SELECT r.*, p.username as personnel_name, p.id as personnel_id, 
                      c.name as company_name, c.id as company_id 
                      FROM reports r 
                      JOIN personnel p ON r.personnel_id = p.id 
                      JOIN companies c ON r.company_id = c.id 
                      WHERE r.id = ?");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } elseif (isCEO()) {
        redirect('view_reports.php');
    } else {
        redirect('personnel_dashboard.php');
    }
}

// Check if user has permission to view this report
if (!canAccessReport($report, $currentCompanyId)) {
    $_SESSION['error_message'] = 'شما دسترسی لازم برای مشاهده این گزارش را ندارید.';
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } elseif (isCEO()) {
        redirect('view_reports.php');
    } else {
        redirect('personnel_dashboard.php');
    }
}

// Get report items with their categories
$stmt = $pdo->prepare("SELECT ri.*, 
                      (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
                       FROM report_item_categories ric 
                       JOIN categories c ON ric.category_id = c.id 
                       WHERE ric.item_id = ri.id) as categories 
                      FROM report_items ri 
                      WHERE ri.report_id = ? 
                      ORDER BY ri.created_at");
$stmt->execute([$reportId]);
$reportItems = $stmt->fetchAll();

// تابع بررسی دسترسی به گزارش
function canAccessReport($report, $currentCompanyId) {
    // مدیر سیستم به همه گزارش‌ها دسترسی دارد
    if (isAdmin()) {
        return true;
    }
    
    // مدیر عامل فقط به گزارش‌های شرکت خود دسترسی دارد
    if (isCEO()) {
        return $report['company_id'] == $currentCompanyId;
    }
    
    // کاربر عادی فقط به گزارش‌های خود در شرکت فعلی دسترسی دارد
    return $report['personnel_id'] == $_SESSION['user_id'] && $report['company_id'] == $currentCompanyId;
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>مشاهده گزارش</h1>
    <div>
        <?php if (isAdmin()): ?>
            <a href="admin_dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
        <?php else: ?>
            <a href="view_reports.php" class="btn btn-secondary">بازگشت به لیست گزارش‌ها</a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between">
            <h5 class="mb-0">گزارش مورخ <?php echo $report['report_date']; ?></h5>
            <span>ثبت شده در: <?php echo $report['created_at']; ?></span>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <p><strong>نام پرسنل:</strong> <?php echo $report['personnel_name']; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>شرکت:</strong> <?php echo $report['company_name']; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>تاریخ گزارش:</strong> <?php echo $report['report_date']; ?></p>
            </div>
        </div>
        
        <hr>
        
        <h5 class="mb-3">آیتم‌های گزارش</h5>
        
        <?php if (count($reportItems) > 0): ?>
            <?php foreach ($reportItems as $index => $item): ?>
                <div class="card mb-3 border-secondary">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">آیتم <?php echo $index + 1; ?></h6>
                            <span>
                                <strong>دسته‌بندی‌ها:</strong> 
                                <?php echo $item['categories'] ? $item['categories'] : 'بدون دسته‌بندی'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                هیچ آیتمی برای این گزارش یافت نشد.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>