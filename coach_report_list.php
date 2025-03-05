<?php
// coach_report_list.php - لیست گزارشات کوچ
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی به گزارشات کوچ
$canView = false;

if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')) {
    // مدیر سیستم (میلاد احمدی) می‌تواند همه گزارش‌ها را ببیند
    $canView = true;
    $isReceiver = false;
} else {
    // بررسی آیا کاربر دریافت کننده گزارش است
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_reports WHERE receiver_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isReceiver = $stmt->fetchColumn() > 0;
    
    $canView = $isReceiver;
}

if (!$canView) {
    redirect('index.php');
}

// پارامترهای فیلتر
$startDate = isset($_GET['start_date']) ? clean($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? clean($_GET['end_date']) : '';

// ساخت کوئری بر اساس نوع کاربر
if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')) {
    // کوچ/مدیر سیستم می‌تواند همه گزارش‌ها را ببیند
    $query = "SELECT cr.id, cr.report_date, 
              CONCAT(p.first_name, ' ', p.last_name) as receiver_name,
              c.name as company_name
              FROM coach_reports cr
              JOIN personnel p ON cr.receiver_id = p.id
              JOIN companies c ON p.company_id = c.id
              WHERE 1=1";
    $params = [];
} else {
    // دریافت کننده فقط گزارش‌های خودش را می‌بیند
    $query = "SELECT cr.id, cr.report_date, 
              CONCAT(coach.first_name, ' ', coach.last_name) as coach_name,
              c.name as company_name
              FROM coach_reports cr
              JOIN personnel coach ON cr.coach_id = coach.id
              JOIN companies c ON cr.company_id = c.id
              WHERE cr.receiver_id = ?";
    $params = [$_SESSION['user_id']];
}

// اعمال فیلترها
if (!empty($startDate)) {
    $query .= " AND cr.report_date >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $query .= " AND cr.report_date <= ?";
    $params[] = $endDate;
}

$query .= " ORDER BY cr.report_date DESC";

// اجرای کوئری
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>گزارشات کوچ</h1>
    <?php if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')): ?>
    <a href="coach_report.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> ثبت گزارش جدید
    </a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">فیلترها</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" id="filterForm">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label for="start_date" class="form-label">از تاریخ</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-5 mb-3">
                    <label for="end_date" class="form-label">تا تاریخ</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">اعمال فیلتر</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($reports) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاریخ گزارش</th>
                            <?php if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')): ?>
                            <th>دریافت کننده</th>
                            <?php else: ?>
                            <th>کوچ</th>
                            <?php endif; ?>
                            <th>شرکت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $report['report_date']; ?></td>
                                <?php if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')): ?>
                                <td><?php echo $report['receiver_name']; ?></td>
                                <?php else: ?>
                                <td><?php echo $report['coach_name']; ?></td>
                                <?php endif; ?>
                                <td><?php echo $report['company_name']; ?></td>
                                <td>
                                    <a href="coach_report_view.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-info">
                                        مشاهده
                                    </a>
                                    <?php if (isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی')): ?>
                                    <a href="coach_report.php?edit=<?php echo $report['id']; ?>" class="btn btn-sm btn-warning">
                                        ویرایش
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center">هیچ گزارشی یافت نشد.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>