<?php
// report_management.php - Report statistics and analysis for admin
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// Check admin access
requireAdmin();

// Date range filter parameters
$startDate = isset($_GET['start_date']) ? clean($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? clean($_GET['end_date']) : date('Y-m-d');
$companyFilter = isset($_GET['company']) && is_numeric($_GET['company']) ? clean($_GET['company']) : '';

// Get all companies for filter
$stmt = $pdo->query("SELECT * FROM companies WHERE is_active = 1 ORDER BY name");
$companies = $stmt->fetchAll();

// Get report counts by company
$companyParams = [];
$companyQuery = "SELECT c.id, c.name, COUNT(r.id) as report_count, 
                COUNT(DISTINCT r.personnel_id) as personnel_count,
                (SELECT COUNT(ri.id) FROM report_items ri JOIN reports rep ON ri.report_id = rep.id 
                 JOIN personnel p2 ON rep.personnel_id = p2.id 
                 WHERE p2.company_id = c.id";

if (!empty($startDate)) {
    $companyQuery .= " AND rep.report_date >= ?";
    $companyParams[] = $startDate;
}

if (!empty($endDate)) {
    $companyQuery .= " AND rep.report_date <= ?";
    $companyParams[] = $endDate;
}

$companyQuery .= ") as item_count
                FROM companies c
                LEFT JOIN personnel p ON c.id = p.company_id
                LEFT JOIN reports r ON p.id = r.personnel_id";

if (!empty($startDate)) {
    $companyQuery .= " AND r.report_date >= ?";
    $companyParams[] = $startDate;
}

if (!empty($endDate)) {
    $companyQuery .= " AND r.report_date <= ?";
    $companyParams[] = $endDate;
}

if (!empty($companyFilter)) {
    $companyQuery .= " AND c.id = ?";
    $companyParams[] = $companyFilter;
}

$companyQuery .= " GROUP BY c.id ORDER BY report_count DESC";

$stmt = $pdo->prepare($companyQuery);
$stmt->execute($companyParams);
$companyStats = $stmt->fetchAll();

// Get reports by date
$dateParams = [];
$dateQuery = "SELECT r.report_date, COUNT(r.id) as report_count, 
             (SELECT COUNT(ri.id) FROM report_items ri WHERE ri.report_id IN 
              (SELECT id FROM reports WHERE report_date = r.report_date";

if (!empty($companyFilter)) {
    $dateQuery .= " AND personnel_id IN (SELECT id FROM personnel WHERE company_id = ?)";
    $dateParams[] = $companyFilter;
}

$dateQuery .= ")) as item_count
             FROM reports r";

$whereAdded = false;

if (!empty($startDate)) {
    $dateQuery .= " WHERE r.report_date >= ?";
    $dateParams[] = $startDate;
    $whereAdded = true;
}

if (!empty($endDate)) {
    $dateQuery .= $whereAdded ? " AND" : " WHERE";
    $dateQuery .= " r.report_date <= ?";
    $dateParams[] = $endDate;
    $whereAdded = true;
}

if (!empty($companyFilter)) {
    $dateQuery .= $whereAdded ? " AND" : " WHERE";
    $dateQuery .= " r.personnel_id IN (SELECT id FROM personnel WHERE company_id = ?)";
    $dateParams[] = $companyFilter;
}

$dateQuery .= " GROUP BY r.report_date ORDER BY r.report_date DESC LIMIT 30";

$stmt = $pdo->prepare($dateQuery);
$stmt->execute($dateParams);
$dateStats = $stmt->fetchAll();

// Get category statistics
$categoryParams = [];
$categoryQuery = "SELECT c.id, c.name, COUNT(ric.item_id) as usage_count
                 FROM categories c
                 LEFT JOIN report_item_categories ric ON c.id = ric.category_id
                 LEFT JOIN report_items ri ON ric.item_id = ri.id
                 LEFT JOIN reports r ON ri.report_id = r.id";

$whereAdded = false;

if (!empty($startDate)) {
    $categoryQuery .= " WHERE r.report_date >= ?";
    $categoryParams[] = $startDate;
    $whereAdded = true;
}

if (!empty($endDate)) {
    $categoryQuery .= $whereAdded ? " AND" : " WHERE";
    $categoryQuery .= " r.report_date <= ?";
    $categoryParams[] = $endDate;
    $whereAdded = true;
}

if (!empty($companyFilter)) {
    $categoryQuery .= $whereAdded ? " AND" : " WHERE";
    $categoryQuery .= " r.personnel_id IN (SELECT id FROM personnel WHERE company_id = ?)";
    $categoryParams[] = $companyFilter;
}

$categoryQuery .= " GROUP BY c.id ORDER BY usage_count DESC";

$stmt = $pdo->prepare($categoryQuery);
$stmt->execute($categoryParams);
$categoryStats = $stmt->fetchAll();

// Get top 5 active personnel
$personnelParams = [];
$personnelQuery = "SELECT p.id, p.username, COUNT(r.id) as report_count, 
                  (SELECT COUNT(ri.id) FROM report_items ri WHERE ri.report_id IN 
                   (SELECT id FROM reports WHERE personnel_id = p.id";

if (!empty($startDate)) {
    $personnelQuery .= " AND report_date >= ?";
    $personnelParams[] = $startDate;
}

if (!empty($endDate)) {
    $personnelQuery .= " AND report_date <= ?";
    $personnelParams[] = $endDate;
}

$personnelQuery .= ")) as item_count,
                  c.name as company_name
                  FROM personnel p
                  JOIN companies c ON p.company_id = c.id
                  LEFT JOIN reports r ON p.id = r.personnel_id";

$whereAdded = false;

if (!empty($startDate)) {
    $personnelQuery .= " WHERE r.report_date >= ?";
    $personnelParams[] = $startDate;
    $whereAdded = true;
}

if (!empty($endDate)) {
    $personnelQuery .= $whereAdded ? " AND" : " WHERE";
    $personnelQuery .= " r.report_date <= ?";
    $personnelParams[] = $endDate;
    $whereAdded = true;
}

if (!empty($companyFilter)) {
    $personnelQuery .= $whereAdded ? " AND" : " WHERE";
    $personnelQuery .= " p.company_id = ?";
    $personnelParams[] = $companyFilter;
}

$personnelQuery .= " GROUP BY p.id ORDER BY report_count DESC LIMIT 5";

$stmt = $pdo->prepare($personnelQuery);
$stmt->execute($personnelParams);
$topPersonnel = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>آمار و تحلیل گزارشات</h1>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">فیلترها</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="company" class="form-label">شرکت</label>
                    <select class="form-select" id="company" name="company">
                        <option value="">همه شرکت‌ها</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>" <?php echo $companyFilter == $company['id'] ? 'selected' : ''; ?>>
                                <?php echo $company['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">از تاریخ</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">تا تاریخ</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">اعمال فیلتر</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">تعداد کل گزارشات</h5>
                <p class="card-text display-4">
                    <?php 
                    $totalReports = 0;
                    foreach ($companyStats as $company) {
                        $totalReports += $company['report_count'];
                    }
                    echo $totalReports; 
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">تعداد کل آیتم‌ها</h5>
                <p class="card-text display-4">
                    <?php 
                    $totalItems = 0;
                    foreach ($companyStats as $company) {
                        $totalItems += $company['item_count'];
                    }
                    echo $totalItems; 
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">شرکت‌های فعال</h5>
                <p class="card-text display-4">
                    <?php 
                    $activeCompanies = 0;
                    foreach ($companyStats as $company) {
                        if ($company['report_count'] > 0) {
                            $activeCompanies++;
                        }
                    }
                    echo $activeCompanies; 
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h5 class="card-title">پرسنل فعال</h5>
                <p class="card-text display-4">
                    <?php 
                    $activePersonnel = 0;
                    foreach ($companyStats as $company) {
                        $activePersonnel += $company['personnel_count'];
                    }
                    echo $activePersonnel; 
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Company Statistics -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">آمار شرکت‌ها</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام شرکت</th>
                        <th>تعداد گزارشات</th>
                        <th>تعداد آیتم‌ها</th>
                        <th>تعداد پرسنل فعال</th>
                        <th>میانگین آیتم در هر گزارش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($companyStats) > 0): ?>
                        <?php foreach ($companyStats as $index => $company): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $company['name']; ?></td>
                                <td><?php echo $company['report_count']; ?></td>
                                <td><?php echo $company['item_count']; ?></td>
                                <td><?php echo $company['personnel_count']; ?></td>
                                <td>
                                    <?php 
                                    if ($company['report_count'] > 0) {
                                        echo number_format($company['item_count'] / $company['report_count'], 2);
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">هیچ آماری یافت نشد.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Personnel -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">پرسنل برتر (بر اساس تعداد گزارش)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>رتبه</th>
                        <th>نام کاربری</th>
                        <th>شرکت</th>
                        <th>تعداد گزارشات</th>
                        <th>تعداد آیتم‌ها</th>
                        <th>میانگین آیتم در هر گزارش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topPersonnel) > 0): ?>
                        <?php foreach ($topPersonnel as $index => $person): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $person['username']; ?></td>
                                <td><?php echo $person['company_name']; ?></td>
                                <td><?php echo $person['report_count']; ?></td>
                                <td><?php echo $person['item_count']; ?></td>
                                <td>
                                    <?php 
                                    if ($person['report_count'] > 0) {
                                        echo number_format($person['item_count'] / $person['report_count'], 2);
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">هیچ پرسنلی یافت نشد.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Category Statistics -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">آمار دسته‌بندی‌ها</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام دسته‌بندی</th>
                        <th>تعداد استفاده</th>
                        <th>درصد استفاده</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categoryStats) > 0): ?>
                        <?php 
                        $totalUsage = 0;
                        foreach ($categoryStats as $category) {
                            $totalUsage += $category['usage_count'];
                        }
                        ?>
                        <?php foreach ($categoryStats as $index => $category): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $category['name']; ?></td>
                                <td><?php echo $category['usage_count']; ?></td>
                                <td>
                                    <?php 
                                    if ($totalUsage > 0) {
                                        echo number_format(($category['usage_count'] / $totalUsage) * 100, 2) . '%';
                                    } else {
                                        echo '0%';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">هیچ دسته‌بندی یافت نشد.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reports by Date -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">گزارشات بر اساس تاریخ (30 روز اخیر)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>تعداد گزارشات</th>
                        <th>تعداد آیتم‌ها</th>
                        <th>میانگین آیتم در هر گزارش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($dateStats) > 0): ?>
                        <?php foreach ($dateStats as $date): ?>
                            <tr>
                                <td><?php echo $date['report_date']; ?></td>
                                <td><?php echo $date['report_count']; ?></td>
                                <td><?php echo $date['item_count']; ?></td>
                                <td>
                                    <?php 
                                    if ($date['report_count'] > 0) {
                                        echo number_format($date['item_count'] / $date['report_count'], 2);
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">هیچ گزارشی یافت نشد.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>