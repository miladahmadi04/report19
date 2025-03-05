<?php
// personnel_dashboard.php - Personnel dashboard
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

requirePersonnel();

$personnelId = $_SESSION['user_id'];

// نمایش پیام خطا در صورت وجود
$message = '';
if (isset($_SESSION['error_message'])) {
    $message = showError($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}

// بررسی آیا کاربر فعلی دریافت کننده گزارش کوچ است
function isCoachReportRecipient() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    // بررسی ساختار جدول coach_reports
    $tableInfoQuery = "DESCRIBE coach_reports";
    $tableInfoStmt = $pdo->query($tableInfoQuery);
    $tableColumns = $tableInfoStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // اگر ستون recipients وجود دارد
    if (in_array('recipients', $tableColumns)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_reports WHERE FIND_IN_SET(?, recipients) > 0");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() > 0;
    }
    // اگر ستون ceo_id وجود دارد
    elseif (in_array('ceo_id', $tableColumns)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_reports WHERE ceo_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() > 0;
    }
    // اگر ستون personnel_id وجود دارد
    elseif (in_array('personnel_id', $tableColumns)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_reports WHERE personnel_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() > 0;
    }
    
    return false;
}

// تنظیم متغیر دسترسی به گزارش کوچ برای استفاده در header.php
$_SESSION['can_view_coach_reports'] = hasPermission('view_coach_reports') || isCoachReportRecipient();

// Get personnel information with error handling
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as company_name, r.name as role_name 
                        FROM personnel p 
                        LEFT JOIN companies c ON p.company_id = c.id 
                        LEFT JOIN roles r ON p.role_id = r.id 
                        WHERE p.id = ?");
    $stmt->execute([$personnelId]);
    $personnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // اگر اطلاعات پرسنل یافت نشد، مقادیر پیش‌فرض تنظیم شود
    if (!$personnel) {
        // دریافت اطلاعات از جدول users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        $personnel = [
            'id' => $_SESSION['user_id'],
            'first_name' => isset($_SESSION['username']) ? $_SESSION['username'] : 'کاربر',
            'last_name' => '',
            'company_name' => isset($_SESSION['company_name']) ? $_SESSION['company_name'] : '',
            'role_name' => 'کاربر سیستم',
            'email' => $user ? $user['email'] : '',
            'mobile' => '',
        ];
    }
} catch (Exception $e) {
    // در صورت هر گونه خطا، مقادیر پیش‌فرض تنظیم شود
    $personnel = [
        'id' => $_SESSION['user_id'],
        'first_name' => isset($_SESSION['username']) ? $_SESSION['username'] : 'کاربر',
        'last_name' => '',
        'company_name' => isset($_SESSION['company_name']) ? $_SESSION['company_name'] : '',
        'role_name' => 'کاربر سیستم',
        'email' => '',
        'mobile' => '',
    ];
}

// مطمئن شویم که تمام فیلدهای مورد نیاز وجود دارند
$personnel['first_name'] = isset($personnel['first_name']) ? $personnel['first_name'] : 'کاربر';
$personnel['last_name'] = isset($personnel['last_name']) ? $personnel['last_name'] : '';
$personnel['role_name'] = isset($personnel['role_name']) ? $personnel['role_name'] : 'کاربر';
$personnel['email'] = isset($personnel['email']) ? $personnel['email'] : '';
$personnel['mobile'] = isset($personnel['mobile']) ? $personnel['mobile'] : '';

// مطمئن شویم که اطلاعات شرکت فعلی به‌روز است
if (isset($_SESSION['company_id']) && isset($_SESSION['companies'])) {
    foreach ($_SESSION['companies'] as $company) {
        if ($company['company_id'] == $_SESSION['company_id']) {
            $personnel['company_name'] = $company['company_name'];
            break;
        }
    }
}

// If this is a CEO, show different dashboard
if (isCEO()) {
    // Count personnel in company
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM personnel WHERE company_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['company_id']]);
    $totalPersonnel = $stmt->fetch()['count'];
    
    // Count total reports in company
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reports r 
                          JOIN personnel p ON r.personnel_id = p.id 
                          WHERE p.company_id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $totalReports = $stmt->fetch()['count'];
    
    // Get report count by date for company
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(r.report_date, '%Y-%m') as month, COUNT(*) as count 
                          FROM reports r
                          JOIN personnel p ON r.personnel_id = p.id
                          WHERE p.company_id = ?
                          GROUP BY month 
                          ORDER BY month DESC 
                          LIMIT 6");
    $stmt->execute([$_SESSION['company_id']]);
    $reportsByMonth = $stmt->fetchAll();
    
    // Get recent reports from company
    $stmt = $pdo->prepare("SELECT r.id, r.report_date, CONCAT(p.first_name, ' ', p.last_name) AS full_name,
                          (SELECT COUNT(*) FROM report_items WHERE report_id = r.id) as item_count
                          FROM reports r 
                          JOIN personnel p ON r.personnel_id = p.id 
                          WHERE p.company_id = ? 
                          ORDER BY r.report_date DESC, r.created_at DESC
                          LIMIT 10");
    $stmt->execute([$_SESSION['company_id']]);
    $recentReports = $stmt->fetchAll();
    
    // Get unread coach reports (if user is a recipient)
    if ($_SESSION['can_view_coach_reports']) {
        $tableInfoQuery = "DESCRIBE coach_reports";
        $tableInfoStmt = $pdo->query($tableInfoQuery);
        $tableColumns = $tableInfoStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('recipients', $tableColumns)) {
            $stmt = $pdo->prepare("SELECT cr.id, cr.report_date, cr.session_title,
                                  CONCAT(p.first_name, ' ', p.last_name) as coach_name
                                  FROM coach_reports cr
                                  JOIN personnel p ON cr.coach_id = p.id
                                  WHERE FIND_IN_SET(?, cr.recipients) > 0
                                  ORDER BY cr.report_date DESC
                                  LIMIT 5");
            $stmt->execute([$_SESSION['user_id']]);
            $coachReports = $stmt->fetchAll();
        } else {
            $coachReports = [];
        }
    }
    
} else {
    // Regular personnel dashboard
    
    // Count total reports by personnel
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reports WHERE personnel_id = ?");
    $stmt->execute([$personnelId]);
    $totalReports = $stmt->fetch()['count'];
    
    // Get report count by date
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(report_date, '%Y-%m') as month, COUNT(*) as count 
                          FROM reports 
                          WHERE personnel_id = ? 
                          GROUP BY month 
                          ORDER BY month DESC 
                          LIMIT 6");
    $stmt->execute([$personnelId]);
    $reportsByMonth = $stmt->fetchAll();
    
    // Get report count by category
    $stmt = $pdo->prepare("SELECT c.name, COUNT(DISTINCT ri.report_id) as count 
                          FROM report_item_categories ric 
                          JOIN categories c ON ric.category_id = c.id 
                          JOIN report_items ri ON ric.item_id = ri.id
                          JOIN reports r ON ri.report_id = r.id
                          WHERE r.personnel_id = ? 
                          GROUP BY c.id 
                          ORDER BY count DESC 
                          LIMIT 5");
    $stmt->execute([$personnelId]);
    $reportsByCategory = $stmt->fetchAll();
    
    // Get recent reports
    $stmt = $pdo->prepare("SELECT r.id, r.report_date, 
                          (SELECT COUNT(*) FROM report_items WHERE report_id = r.id) as item_count
                          FROM reports r 
                          WHERE r.personnel_id = ? 
                          ORDER BY r.report_date DESC, r.created_at DESC
                          LIMIT 5");
    $stmt->execute([$personnelId]);
    $recentReports = $stmt->fetchAll();
    
    // Get coach reports (if user is a recipient)
    if ($_SESSION['can_view_coach_reports']) {
        $tableInfoQuery = "DESCRIBE coach_reports";
        $tableInfoStmt = $pdo->query($tableInfoQuery);
        $tableColumns = $tableInfoStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('recipients', $tableColumns)) {
            $stmt = $pdo->prepare("SELECT cr.id, cr.report_date, cr.session_title,
                                  CONCAT(p.first_name, ' ', p.last_name) as coach_name
                                  FROM coach_reports cr
                                  JOIN personnel p ON cr.coach_id = p.id
                                  WHERE FIND_IN_SET(?, cr.recipients) > 0
                                  ORDER BY cr.report_date DESC
                                  LIMIT 5");
            $stmt->execute([$_SESSION['user_id']]);
            $coachReports = $stmt->fetchAll();
        } else {
            $coachReports = [];
        }
    }
}

include 'header.php';
?>

<h1 class="mb-4">داشبورد <?php echo isCEO() ? 'مدیر عامل' : 'پرسنل'; ?></h1>

<?php echo $message; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">اطلاعات <?php echo isCEO() ? 'مدیر عامل' : 'پرسنلی'; ?></h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>نام و نام خانوادگی:</strong> 
                        <?php echo $personnel['first_name'] . ' ' . $personnel['last_name']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>شرکت فعال:</strong> 
                        <?php echo $personnel['company_name']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>نقش:</strong> 
                        <?php echo $personnel['role_name']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>ایمیل:</strong> 
                        <?php echo $personnel['email']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>موبایل:</strong> 
                        <?php echo $personnel['mobile']; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">آمار گزارش‌ها</h5>
                <div class="row">
                    <?php if (isCEO()): ?>
                    <div class="col-md-6">
                        <div class="card bg-primary text-white mb-3">
                            <div class="card-body text-center">
                                <h3 class="card-title">تعداد پرسنل</h3>
                                <p class="display-4"><?php echo $totalPersonnel; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="card-title">کل گزارش‌ها</h3>
                                <p class="display-4"><?php echo $totalReports; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <div class="card bg-primary text-white mb-3">
                            <div class="card-body text-center">
                                <h3 class="card-title">کل گزارش‌ها</h3>
                                <p class="display-4"><?php echo $totalReports; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="card-title">ماه جاری</h3>
                                <?php 
                                    $currentMonth = date('Y-m');
                                    $currentMonthCount = 0;
                                    
                                    foreach ($reportsByMonth as $month) {
                                        if ($month['month'] == $currentMonth) {
                                            $currentMonthCount = $month['count'];
                                            break;
                                        }
                                    }
                                ?>
                                <p class="display-4"><?php echo $currentMonthCount; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['companies'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">انتخاب شرکت</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($_SESSION['companies'] as $company): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card <?php echo ($_SESSION['company_id'] == $company['company_id']) ? 'bg-primary text-white' : ''; ?>">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo $company['company_name']; ?></h5>
                                    <?php if (isset($company['is_primary']) && $company['is_primary']): ?>
                                        <span class="badge bg-warning text-dark">شرکت اصلی</span>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['company_id'] != $company['company_id']): ?>
                                        <div class="mt-2">
                                            <a href="switch_company.php?company_id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-light">انتخاب</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-<?php echo isset($coachReports) ? '6' : '12'; ?>">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">گزارش‌های روزانه اخیر</h5>
            </div>
            <div class="card-body">
                <?php if (isset($recentReports) && count($recentReports) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <?php if (isCEO()): ?>
                                    <th>نام پرسنل</th>
                                    <?php endif; ?>
                                    <th>تعداد آیتم</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReports as $report): ?>
                                    <tr>
                                        <td><?php echo $report['report_date']; ?></td>
                                        <?php if (isCEO()): ?>
                                        <td><?php echo $report['full_name']; ?></td>
                                        <?php endif; ?>
                                        <td><?php echo $report['item_count']; ?></td>
                                        <td>
                                            <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-info">مشاهده</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="view_reports.php" class="btn btn-primary">مشاهده همه گزارش‌ها</a>
                    </div>
                <?php else: ?>
                    <p class="text-center">هیچ گزارشی یافت نشد.</p>
                    <?php if (!isCEO()): ?>
                    <div class="text-center">
                        <a href="reports.php" class="btn btn-primary">ثبت گزارش جدید</a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($coachReports) && !empty($coachReports)): ?>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">گزارش‌های کوچ اخیر</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>کوچ</th>
                                <?php if (isset($coachReports[0]['session_title'])): ?>
                                <th>عنوان</th>
                                <?php endif; ?>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coachReports as $report): ?>
                                <tr>
                                    <td><?php echo $report['report_date']; ?></td>
                                    <td><?php echo $report['coach_name']; ?></td>
                                    <?php if (isset($report['session_title'])): ?>
                                    <td><?php echo $report['session_title']; ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="coach_report_view.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-info">مشاهده</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-center">
                    <a href="coach_report_list.php" class="btn btn-primary">مشاهده همه گزارش‌ها</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>