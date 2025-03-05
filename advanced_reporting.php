<?php
// advanced_reporting.php - سیستم گزارش‌گیری پیشرفته
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!isAdmin() && !isCEO()) {
    redirect('index.php');
}

// تاریخ‌های پیش‌فرض
$currentDate = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

// دریافت پارامترهای گزارش
$reportType = isset($_POST['report_type']) ? clean($_POST['report_type']) : '';
$company_id = isset($_POST['company_id']) ? clean($_POST['company_id']) : $_SESSION['company_id'] ?? null;
$dateFrom = isset($_POST['date_from']) ? clean($_POST['date_from']) : $firstDayOfMonth;
$dateTo = isset($_POST['date_to']) ? clean($_POST['date_to']) : $lastDayOfMonth;
$groupBy = isset($_POST['group_by']) ? clean($_POST['group_by']) : '';
$exportFormat = isset($_POST['export_format']) ? clean($_POST['export_format']) : '';
$personnel_id = isset($_POST['personnel_id']) ? clean($_POST['personnel_id']) : '';
$social_network_id = isset($_POST['social_network_id']) ? clean($_POST['social_network_id']) : '';
$content_type_id = isset($_POST['content_type_id']) ? clean($_POST['content_type_id']) : '';

// لیست شرکت‌ها
$companies = [];
if (isAdmin()) {
    $stmt = $pdo->query("SELECT id, name FROM companies WHERE is_active = 1 ORDER BY name");
    $companies = $stmt->fetchAll();
    
    // انتخاب اولین شرکت به صورت پیش‌فرض اگر انتخاب نشده باشد
    if (empty($company_id) && !empty($companies)) {
        $company_id = $companies[0]['id'];
    }
} else {
    // برای مدیرعامل، گرفتن شرکت مربوطه
    $stmt = $pdo->prepare("SELECT company_id FROM personnel WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $company_id = $user ? $user['company_id'] : null;
    
    // دریافت اطلاعات شرکت
    if ($company_id) {
        $stmt = $pdo->prepare("SELECT id, name FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $companies = $stmt->fetchAll();
    }
}

// لیست پرسنل شرکت
$personnel = [];
if ($company_id) {
    $stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as full_name 
                         FROM personnel 
                         WHERE company_id = ? AND is_active = 1 
                         ORDER BY first_name, last_name");
    $stmt->execute([$company_id]);
    $personnel = $stmt->fetchAll();
}

// لیست شبکه‌های اجتماعی
$socialNetworks = [];
$stmt = $pdo->query("SELECT id, name FROM social_networks ORDER BY name");
$socialNetworks = $stmt->fetchAll();

// لیست انواع محتوا
$contentTypes = [];
if ($company_id) {
    $stmt = $pdo->prepare("SELECT id, name FROM content_types WHERE company_id = ? OR is_default = 1 ORDER BY name");
    $stmt->execute([$company_id]);
    $contentTypes = $stmt->fetchAll();
}

// نتایج گزارش
$reportData = [];
$reportTitle = '';
$reportColumns = [];
$chartData = [];

// تولید گزارش بر اساس نوع درخواستی
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($reportType)) {
    // متغیرهای مشترک
    $params = [];
    $selectColumns = [];
    $fromTables = "";
    $whereClause = "";
    $groupByClause = "";
    $orderByClause = "";
    
    switch ($reportType) {
        case 'daily_reports':
            $reportTitle = 'گزارش فعالیت‌های روزانه';
            
            $selectColumns = [
                "DATE(r.report_date) as report_date",
                "COUNT(r.id) as report_count",
                "COUNT(DISTINCT r.personnel_id) as personnel_count"
            ];
            
            if ($groupBy == 'personnel') {
                $selectColumns[] = "CONCAT(p.first_name, ' ', p.last_name) as personnel_name";
            } elseif ($groupBy == 'category') {
                $selectColumns[] = "c.name as category_name";
                $fromTables = "
                    FROM reports r
                    JOIN personnel p ON r.personnel_id = p.id
                    JOIN report_items ri ON r.id = ri.report_id
                    JOIN report_item_categories ric ON ri.id = ric.item_id
                    JOIN categories c ON ric.category_id = c.id
                ";
            } else {
                $fromTables = "
                    FROM reports r
                    JOIN personnel p ON r.personnel_id = p.id
                ";
            }
            
            $whereClause = "WHERE p.company_id = ? AND DATE(r.report_date) BETWEEN ? AND ?";
            $params = [$company_id, $dateFrom, $dateTo];
            
            if (!empty($personnel_id)) {
                $whereClause .= " AND r.personnel_id = ?";
                $params[] = $personnel_id;
            }
            
            if ($groupBy == 'date') {
                $groupByClause = "GROUP BY DATE(r.report_date)";
                $orderByClause = "ORDER BY r.report_date ASC";
                $chartData = [
                    'type' => 'line',
                    'labels' => 'report_date',
                    'datasets' => [
                        ['label' => 'تعداد گزارش', 'data' => 'report_count', 'borderColor' => '#4e73df', 'backgroundColor' => 'rgba(78, 115, 223, 0.1)'],
                        ['label' => 'تعداد پرسنل', 'data' => 'personnel_count', 'borderColor' => '#1cc88a', 'backgroundColor' => 'rgba(28, 200, 138, 0.1)']
                    ]
                ];
            } elseif ($groupBy == 'personnel') {
                $groupByClause = "GROUP BY r.personnel_id";
                $orderByClause = "ORDER BY report_count DESC";
                $chartData = [
                    'type' => 'bar',
                    'labels' => 'personnel_name',
                    'datasets' => [
                        ['label' => 'تعداد گزارش', 'data' => 'report_count', 'backgroundColor' => 'rgba(78, 115, 223, 0.8)']
                    ]
                ];
            } elseif ($groupBy == 'category') {
                $groupByClause = "GROUP BY c.id";
                $orderByClause = "ORDER BY report_count DESC";
                $chartData = [
                    'type' => 'doughnut',
                    'labels' => 'category_name',
                    'datasets' => [
                        ['label' => 'تعداد گزارش', 'data' => 'report_count', 'backgroundColor' => [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)',
                            'rgba(133, 135, 150, 0.8)'
                        ]]
                    ]
                ];
            }
            break;
            
        case 'coach_reports':
            $reportTitle = 'گزارش عملکرد کوچینگ';
            
            $selectColumns = [
                "DATE(cr.report_date) as report_date",
                "COUNT(cr.id) as report_count",
                "AVG(crp.coach_score) as avg_score"
            ];
            
            if ($groupBy == 'personnel') {
                $selectColumns[] = "CONCAT(p.first_name, ' ', p.last_name) as personnel_name";
                $fromTables = "
                    FROM coach_reports cr
                    JOIN coach_report_personnel crp ON cr.id = crp.coach_report_id
                    JOIN personnel p ON crp.personnel_id = p.id
                ";
                $whereClause = "WHERE cr.company_id = ? AND DATE(cr.report_date) BETWEEN ? AND ?";
                $groupByClause = "GROUP BY crp.personnel_id";
                $orderByClause = "ORDER BY avg_score DESC";
                $chartData = [
                    'type' => 'bar',
                    'labels' => 'personnel_name',
                    'datasets' => [
                        ['label' => 'امتیاز متوسط', 'data' => 'avg_score', 'backgroundColor' => 'rgba(28, 200, 138, 0.8)']
                    ]
                ];
            } else {
                $fromTables = "
                    FROM coach_reports cr
                    JOIN coach_report_personnel crp ON cr.id = crp.coach_report_id
                ";
                $whereClause = "WHERE cr.company_id = ? AND DATE(cr.report_date) BETWEEN ? AND ?";
                $groupByClause = "GROUP BY DATE(cr.report_date)";
                $orderByClause = "ORDER BY cr.report_date ASC";
                $chartData = [
                    'type' => 'line',
                    'labels' => 'report_date',
                    'datasets' => [
                        ['label' => 'امتیاز متوسط', 'data' => 'avg_score', 'borderColor' => '#1cc88a', 'backgroundColor' => 'rgba(28, 200, 138, 0.1)']
                    ]
                ];
            }
            
            $params = [$company_id, $dateFrom, $dateTo];
            
            if (!empty($personnel_id)) {
                $whereClause .= " AND crp.personnel_id = ?";
                $params[] = $personnel_id;
            }
            break;
            
        case 'social_performance':
            $reportTitle = 'گزارش عملکرد شبکه‌های اجتماعی';
            
            $selectColumns = [
                "DATE(mr.report_date) as report_date",
                "sp.page_name",
                "sn.name as network_name",
                "AVG(rs.score) as avg_score"
            ];
            
            $fromTables = "
                FROM monthly_reports mr
                JOIN social_pages sp ON mr.page_id = sp.id
                JOIN social_networks sn ON sp.social_network_id = sn.id
                LEFT JOIN report_scores rs ON mr.id = rs.report_id
            ";
            
            $whereClause = "WHERE sp.company_id = ? AND DATE(mr.report_date) BETWEEN ? AND ?";
            $params = [$company_id, $dateFrom, $dateTo];
            
            if (!empty($social_network_id)) {
                $whereClause .= " AND sp.social_network_id = ?";
                $params[] = $social_network_id;
            }
            
            if ($groupBy == 'page') {
                $groupByClause = "GROUP BY sp.id";
                $orderByClause = "ORDER BY avg_score DESC";
                $chartData = [
                    'type' => 'bar',
                    'labels' => 'page_name',
                    'datasets' => [
                        ['label' => 'امتیاز متوسط', 'data' => 'avg_score', 'backgroundColor' => 'rgba(54, 185, 204, 0.8)']
                    ]
                ];
            } elseif ($groupBy == 'network') {
                $groupByClause = "GROUP BY sn.id";
                $orderByClause = "ORDER BY avg_score DESC";
                $chartData = [
                    'type' => 'pie',
                    'labels' => 'network_name',
                    'datasets' => [
                        ['label' => 'امتیاز متوسط', 'data' => 'avg_score', 'backgroundColor' => [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ]]
                    ]
                ];
            } else {
                $groupByClause = "GROUP BY DATE(mr.report_date)";
                $orderByClause = "ORDER BY mr.report_date ASC";
                $chartData = [
                    'type' => 'line',
                    'labels' => 'report_date',
                    'datasets' => [
                        ['label' => 'امتیاز متوسط', 'data' => 'avg_score', 'borderColor' => '#36b9cc', 'backgroundColor' => 'rgba(54, 185, 204, 0.1)']
                    ]
                ];
            }
            break;
            
        case 'content_analytics':
            $reportTitle = 'تحلیل محتوای تولید شده';
            
            $selectColumns = [
                "DATE(c.publish_date) as publish_date",
                "COUNT(c.id) as content_count"
            ];
            
            if ($groupBy == 'type') {
                $selectColumns[] = "ct.name as content_type";
                $fromTables = "
                    FROM contents c
                    JOIN content_type_relations ctr ON c.id = ctr.content_id
                    JOIN content_types ct ON ctr.type_id = ct.id
                ";
                $groupByClause = "GROUP BY ct.id";
                $orderByClause = "ORDER BY content_count DESC";
                $chartData = [
                    'type' => 'doughnut',
                    'labels' => 'content_type',
                    'datasets' => [
                        ['label' => 'تعداد محتوا', 'data' => 'content_count', 'backgroundColor' => [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)',
                            'rgba(133, 135, 150, 0.8)'
                        ]]
                    ]
                ];
            } elseif ($groupBy == 'status') {
                $selectColumns[] = "ps.name as status_name";
                $fromTables = "
                    FROM contents c
                    JOIN content_publish_statuses ps ON c.publish_status_id = ps.id
                ";
                $groupByClause = "GROUP BY ps.id";
                $orderByClause = "ORDER BY content_count DESC";
                $chartData = [
                    'type' => 'pie',
                    'labels' => 'status_name',
                    'datasets' => [
                        ['label' => 'تعداد محتوا', 'data' => 'content_count', 'backgroundColor' => [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ]]
                    ]
                ];
            } else {
                $fromTables = "FROM contents c";
                $groupByClause = "GROUP BY DATE(c.publish_date)";
                $orderByClause = "ORDER BY c.publish_date ASC";
                $chartData = [
                    'type' => 'line',
                    'labels' => 'publish_date',
                    'datasets' => [
                        ['label' => 'تعداد محتوا', 'data' => 'content_count', 'borderColor' => '#f6c23e', 'backgroundColor' => 'rgba(246, 194, 62, 0.1)']
                    ]
                ];
            }
            
            $whereClause = "WHERE c.company_id = ? AND DATE(c.publish_date) BETWEEN ? AND ?";
            $params = [$company_id, $dateFrom, $dateTo];
            
            if (!empty($content_type_id)) {
                $whereClause .= " AND EXISTS (SELECT 1 FROM content_type_relations ctr WHERE ctr.content_id = c.id AND ctr.type_id = ?)";
                $params[] = $content_type_id;
            }
            break;
    }
    
    // اجرای کوئری
    if (!empty($selectColumns) && !empty($fromTables)) {
        $query = "SELECT " . implode(", ", $selectColumns) . " " . $fromTables . " " . $whereClause;
        
        if (!empty($groupByClause)) {
            $query .= " " . $groupByClause;
        }
        
        if (!empty($orderByClause)) {
            $query .= " " . $orderByClause;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $reportData = $stmt->fetchAll();
        
        // تنظیم ستون‌های گزارش بر اساس داده‌های بازگشتی
        if (!empty($reportData)) {
            $reportColumns = array_keys($reportData[0]);
        }
        
        // خروجی اکسل
        if ($exportFormat == 'excel' && !empty($reportData)) {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report_' . $reportType . '_' . date('Ymd') . '.csv"');
            $output = fopen('php://output', 'w');
            
            // هدر CSV با نام‌های فارسی ستون‌ها
            $headerRow = [];
            foreach ($reportColumns as $column) {
                switch ($column) {
                    case 'report_date':
                    case 'publish_date':
                        $headerRow[] = 'تاریخ';
                        break;
                    case 'report_count':
                        $headerRow[] = 'تعداد گزارش';
                        break;
                    case 'content_count':
                        $headerRow[] = 'تعداد محتوا';
                        break;
                    case 'personnel_count':
                        $headerRow[] = 'تعداد پرسنل';
                        break;
                    case 'avg_score':
                        $headerRow[] = 'امتیاز متوسط';
                        break;
                    case 'personnel_name':
                        $headerRow[] = 'نام پرسنل';
                        break;
                    case 'category_name':
                        $headerRow[] = 'دسته‌بندی';
                        break;
                    case 'network_name':
                        $headerRow[] = 'شبکه اجتماعی';
                        break;
                    case 'page_name':
                        $headerRow[] = 'نام صفحه';
                        break;
                    case 'content_type':
                        $headerRow[] = 'نوع محتوا';
                        break;
                    case 'status_name':
                        $headerRow[] = 'وضعیت';
                        break;
                    default:
                        $headerRow[] = $column;
                }
            }
            fputcsv($output, $headerRow);
            
            // داده‌های گزارش
            foreach ($reportData as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit;
        }
    }
}

include 'header.php';
?>

<style>
.filter-section {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
.chart-container {
    position: relative;
    height: 400px;
    margin-bottom: 30px;
}
.report-table th {
    background-color: #f8f9fa;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>سیستم گزارش‌گیری پیشرفته</h1>
    <div>
        <?php if (!empty($reportData)): ?>
            <form method="POST" action="" class="d-inline">
                <input type="hidden" name="report_type" value="<?php echo $reportType; ?>">
                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                <input type="hidden" name="group_by" value="<?php echo $groupBy; ?>">
                <input type="hidden" name="personnel_id" value="<?php echo $personnel_id; ?>">
                <input type="hidden" name="social_network_id" value="<?php echo $social_network_id; ?>">
                <input type="hidden" name="content_type_id" value="<?php echo $content_type_id; ?>">
                <input type="hidden" name="export_format" value="excel">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> خروجی اکسل
                </button>
            </form>
        <?php endif; ?>
        
        <a href="executive_dashboard.php" class="btn btn-primary">
            <i class="fas fa-tachometer-alt"></i> داشبورد مدیریتی
        </a>
    </div>
</div>

<!-- فیلترهای گزارش -->
<div class="filter-section">
    <form method="POST" action="">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="report_type" class="form-label">نوع گزارش</label>
                    <select class="form-select" id="report_type" name="report_type" required onchange="updateFormFields()">
                        <option value="">انتخاب کنید...</option>
                        <option value="daily_reports" <?php echo ($reportType == 'daily_reports') ? 'selected' : ''; ?>>گزارش فعالیت‌های روزانه</option>
                        <option value="coach_reports" <?php echo ($reportType == 'coach_reports') ? 'selected' : ''; ?>>گزارش عملکرد کوچینگ</option>
                        <option value="social_performance" <?php echo ($reportType == 'social_performance') ? 'selected' : ''; ?>>گزارش عملکرد شبکه‌های اجتماعی</option>
                        <option value="content_analytics" <?php echo ($reportType == 'content_analytics') ? 'selected' : ''; ?>>تحلیل محتوای تولید شده</option>
                    </select>
                </div>
            </div>
            
            <?php if (isAdmin() && !empty($companies)): ?>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="company_id" class="form-label">شرکت</label>
                    <select class="form-select" id="company_id" name="company_id">
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>" <?php echo ($company_id == $company['id']) ? 'selected' : ''; ?>>
                                <?php echo $company['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="group_by" class="form-label">گروه‌بندی بر اساس</label>
                    <select class="form-select" id="group_by" name="group_by">
                        <option value="">انتخاب کنید...</option>
                        <option value="date" <?php echo ($groupBy == 'date') ? 'selected' : ''; ?> class="group-option for-daily for-coach for-social for-content">تاریخ</option>
                        <option value="personnel" <?php echo ($groupBy == 'personnel') ? 'selected' : ''; ?> class="group-option for-daily for-coach">پرسنل</option>
                        <option value="category" <?php echo ($groupBy == 'category') ? 'selected' : ''; ?> class="group-option for-daily">دسته‌بندی</option>
                        <option value="page" <?php echo ($groupBy == 'page') ? 'selected' : ''; ?> class="group-option for-social">صفحه</option>
                        <option value="network" <?php echo ($groupBy == 'network') ? 'selected' : ''; ?> class="group-option for-social">شبکه اجتماعی</option>
                        <option value="type" <?php echo ($groupBy == 'type') ? 'selected' : ''; ?> class="group-option for-content">نوع محتوا</option>
                        <option value="status" <?php echo ($groupBy == 'status') ? 'selected' : ''; ?> class="group-option for-content">وضعیت</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="date_from" class="form-label">از تاریخ</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="date_to" class="form-label">تا تاریخ</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="personnel_id" class="form-label filter-option for-daily for-coach">پرسنل</label>
                    <label for="social_network_id" class="form-label filter-option for-social">شبکه اجتماعی</label>
                    <label for="content_type_id" class="form-label filter-option for-content">نوع محتوا</label>
                    
                    <select class="form-select filter-option for-daily for-coach" id="personnel_id" name="personnel_id">
                        <option value="">همه پرسنل</option>
                        <?php foreach ($personnel as $person): ?>
                            <option value="<?php echo $person['id']; ?>" <?php echo ($personnel_id == $person['id']) ? 'selected' : ''; ?>>
                                <?php echo $person['full_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select class="form-select filter-option for-social" id="social_network_id" name="social_network_id">
                        <option value="">همه شبکه‌ها</option>
                        <?php foreach ($socialNetworks as $network): ?>
                            <option value="<?php echo $network['id']; ?>" <?php echo ($social_network_id == $network['id']) ? 'selected' : ''; ?>>
                                <?php echo $network['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select class="form-select filter-option for-content" id="content_type_id" name="content_type_id">
                        <option value="">همه انواع محتوا</option>
                        <?php foreach ($contentTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo ($content_type_id == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo $type['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <button type="submit" class="btn btn-primary px-5">
                <i class="fas fa-chart-bar"></i> ایجاد گزارش
            </button>
        </div>
    </form>
</div>

<?php if (!empty($reportData)): ?>
<!-- نمایش نتایج گزارش -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $reportTitle; ?></h6>
    </div>
    <div class="card-body">
        <!-- نمودار -->
        <?php if (!empty($chartData)): ?>
            <div class="chart-container">
                <canvas id="reportChart"></canvas>
            </div>
        <?php endif; ?>
        
        <!-- جدول داده‌ها -->
        <div class="table-responsive">
            <table class="table table-striped report-table">
                <thead>
                    <tr>
                        <?php foreach ($reportColumns as $column): ?>
                            <th>
                                <?php
                                switch ($column) {
                                    case 'report_date':
                                    case 'publish_date':
                                        echo 'تاریخ';
                                        break;
                                    case 'report_count':
                                        echo 'تعداد گزارش';
                                        break;
                                    case 'content_count':
                                        echo 'تعداد محتوا';
                                        break;
                                    case 'personnel_count':
                                        echo 'تعداد پرسنل';
                                        break;
                                    case 'avg_score':
                                        echo 'امتیاز متوسط';
                                        break;
                                    case 'personnel_name':
                                        echo 'نام پرسنل';
                                        break;
                                    case 'category_name':
                                        echo 'دسته‌بندی';
                                        break;
                                    case 'network_name':
                                        echo 'شبکه اجتماعی';
                                        break;
                                    case 'page_name':
                                        echo 'نام صفحه';
                                        break;
                                    case 'content_type':
                                        echo 'نوع محتوا';
                                        break;
                                    case 'status_name':
                                        echo 'وضعیت';
                                        break;
                                    default:
                                        echo $column;
                                }
                                ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <?php foreach ($reportColumns as $column): ?>
                                <td>
                                    <?php
                                    if (($column == 'report_date' || $column == 'publish_date') && $row[$column]) {
                                        echo formatDate($row[$column]);
                                    } elseif ($column == 'avg_score' && $row[$column] !== null) {
                                        echo number_format($row[$column], 2);
                                    } else {
                                        echo $row[$column];
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- کد جاوااسکریپت برای نمودار -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($chartData)): ?>
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    const chartLabels = <?php 
        $labels = array_map(function($item) use ($chartData) {
            $labelColumn = $chartData['labels'];
            if ($labelColumn == 'report_date' || $labelColumn == 'publish_date') {
                // تبدیل فرمت تاریخ برای نمایش بهتر
                return formatDateJs($item[$labelColumn]);
            } else {
                return $item[$labelColumn];
            }
        }, $reportData);
        echo json_encode($labels); 
    ?>;
    
    const chartDatasets = [];
    <?php foreach ($chartData['datasets'] as $index => $dataset): ?>
    chartDatasets.push({
        label: '<?php echo $dataset['label']; ?>',
        data: <?php 
            $data = array_map(function($item) use ($dataset) {
                return $item[$dataset['data']];
            }, $reportData);
            echo json_encode($data); 
        ?>,
        <?php if (isset($dataset['backgroundColor'])): ?>
        backgroundColor: <?php 
            if (is_array($dataset['backgroundColor'])) {
                echo json_encode($dataset['backgroundColor']);
            } else {
                echo "'" . $dataset['backgroundColor'] . "'";
            }
        ?>,
        <?php endif; ?>
        <?php if (isset($dataset['borderColor'])): ?>
        borderColor: '<?php echo $dataset['borderColor']; ?>',
        <?php endif; ?>
        borderWidth: 1
    });
    <?php endforeach; ?>
    
    const chart = new Chart(ctx, {
        type: '<?php echo $chartData['type']; ?>',
        data: {
            labels: chartLabels,
            datasets: chartDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: '<?php echo $reportTitle; ?>'
                }
            },
            <?php if ($chartData['type'] == 'line' || $chartData['type'] == 'bar'): ?>
            scales: {
                y: {
                    beginAtZero: true
                }
            }
            <?php endif; ?>
        }
    });
    <?php endif; ?>
});

// تابع فرمت کردن تاریخ برای جاوااسکریپت
function formatDateJs(dateStr) {
    const parts = dateStr.split('-');
    // تبدیل ساده به فرمت سال/ماه/روز
    return parts[0] + '/' + parts[1] + '/' + parts[2];
}
</script>
<?php endif; ?>

<script>
// نمایش/مخفی کردن فیلدهای مرتبط با نوع گزارش
function updateFormFields() {
    const reportType = document.getElementById('report_type').value;
    
    // مخفی کردن همه فیلترها و گزینه‌های گروه‌بندی
    document.querySelectorAll('.filter-option').forEach(el => {
        el.style.display = 'none';
    });
    
    document.querySelectorAll('.group-option').forEach(el => {
        el.style.display = 'none';
    });
    
    // نمایش فیلترها و گزینه‌های مرتبط
    if (reportType === 'daily_reports') {
        document.querySelectorAll('.for-daily').forEach(el => {
            el.style.display = '';
        });
    } else if (reportType === 'coach_reports') {
        document.querySelectorAll('.for-coach').forEach(el => {
            el.style.display = '';
        });
    } else if (reportType === 'social_performance') {
        document.querySelectorAll('.for-social').forEach(el => {
            el.style.display = '';
        });
    } else if (reportType === 'content_analytics') {
        document.querySelectorAll('.for-content').forEach(el => {
            el.style.display = '';
        });
    }
    
    // به‌روزرسانی گزینه‌های گروه‌بندی
    const groupSelect = document.getElementById('group_by');
    for (let i = 0; i < groupSelect.options.length; i++) {
        const option = groupSelect.options[i];
        if (option.value === '' || option.classList.contains('for-' + reportType.split('_')[0])) {
            option.hidden = false;
        } else {
            option.hidden = true;
        }
    }
    
    // تنظیم مقدار پیش‌فرض اگر گزینه انتخاب شده مخفی باشد
    if (groupSelect.selectedOptions[0].hidden) {
        groupSelect.value = '';
    }
}

// اجرای تابع در زمان بارگذاری صفحه
document.addEventListener('DOMContentLoaded', function() {
    updateFormFields();
});
</script>

<?php
// تابع ساده فرمت کردن تاریخ برای جایگزین jdate()
function formatDate($dateStr) {
    if (empty($dateStr)) return '';
    
    $timestamp = strtotime($dateStr);
    if ($timestamp === false) return $dateStr;
    
    // ساختار ساده تاریخ برای نمایش (Y/m/d)
    return date('Y/m/d', $timestamp);
}
?>

<?php include 'footer.php'; ?>