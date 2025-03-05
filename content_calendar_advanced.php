<?php
// content_calendar_advanced.php - تقویم محتوایی پیشرفته
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی کاربر
if (!hasPermission('view_content_calendar')) {
    redirect('index.php');
}

// دریافت اطلاعات پایه مورد نیاز
$company_id = $_SESSION['company_id'] ?? null;

// اگر شرکت انتخاب نشده و کاربر مدیر سیستم است
if (!$company_id && isAdmin()) {
    $stmt = $pdo->query("SELECT id FROM companies WHERE is_active = 1 LIMIT 1");
    $company = $stmt->fetch();
    $company_id = $company['id'] ?? null;
}

// تنظیم نمای پیش‌فرض
$currentView = isset($_GET['view']) ? clean($_GET['view']) : 'month';
$date = isset($_GET['date']) ? clean($_GET['date']) : date('Y-m-d');

// تنظیم تاریخ‌ها بر اساس نمای انتخاب شده
$year = date('Y', strtotime($date));
$month = date('m', strtotime($date));
$day = date('d', strtotime($date));
$monthName = date('F Y', strtotime($date));
$weekStart = date('Y-m-d', strtotime('monday this week', strtotime($date)));
$weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

// دریافت فیلترهای محتوا
$contentType = isset($_GET['content_type']) ? clean($_GET['content_type']) : '';
$platform = isset($_GET['platform']) ? clean($_GET['platform']) : '';
$status = isset($_GET['status']) ? clean($_GET['status']) : '';
$creator = isset($_GET['creator']) ? clean($_GET['creator']) : '';

// دریافت محتواهای برنامه‌ریزی شده
$params = [];
$whereClause = "WHERE c.company_id = ?";
$params[] = $company_id;

if ($currentView == 'month') {
    $startDate = date('Y-m-01', strtotime($date));
    $endDate = date('Y-m-t', strtotime($date));
    $whereClause .= " AND c.publish_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
} elseif ($currentView == 'week') {
    $whereClause .= " AND c.publish_date BETWEEN ? AND ?";
    $params[] = $weekStart;
    $params[] = $weekEnd;
} elseif ($currentView == 'day') {
    $whereClause .= " AND c.publish_date = ?";
    $params[] = $date;
}

// فیلترهای اضافی
if (!empty($contentType)) {
    $whereClause .= " AND ctr.type_id = ?";
    $params[] = $contentType;
}

if (!empty($platform)) {
    $whereClause .= " AND EXISTS (SELECT 1 FROM content_platform_relations cpr WHERE cpr.content_id = c.id AND cpr.platform_id = ?)";
    $params[] = $platform;
}

if (!empty($status)) {
    $whereClause .= " AND c.publish_status_id = ?";
    $params[] = $status;
}

if (!empty($creator)) {
    $whereClause .= " AND c.created_by = ?";
    $params[] = $creator;
}

// بازیابی داده‌ها - اصلاح شده: حذف ps.color و prs.color که وجود ندارند
$query = "
SELECT 
    c.id,
    c.title,
    c.publish_date,
    c.publish_time,
    c.scenario,
    c.description,
    ps.name as publish_status,
    prs.name as production_status,
    GROUP_CONCAT(DISTINCT ct.name SEPARATOR ', ') as content_types,
    GROUP_CONCAT(DISTINCT cp.name SEPARATOR ', ') as platforms,
    CONCAT(p.first_name, ' ', p.last_name) as creator_name
FROM
    contents c
LEFT JOIN
    content_publish_statuses ps ON c.publish_status_id = ps.id
LEFT JOIN
    content_production_statuses prs ON c.production_status_id = prs.id
LEFT JOIN
    content_type_relations ctr ON c.id = ctr.content_id
LEFT JOIN
    content_types ct ON ctr.type_id = ct.id
LEFT JOIN
    content_platform_relations cpr ON c.id = cpr.content_id
LEFT JOIN
    content_platforms cp ON cpr.platform_id = cp.id
LEFT JOIN
    personnel p ON c.created_by = p.id
{$whereClause}
GROUP BY
    c.id
ORDER BY
    c.publish_date ASC, c.publish_time ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$contentItems = $stmt->fetchAll();

// دریافت انواع محتوا برای فیلتر
$stmt = $pdo->prepare("SELECT id, name FROM content_types WHERE company_id = ? OR is_default = 1 ORDER BY name");
$stmt->execute([$company_id]);
$contentTypes = $stmt->fetchAll();

// دریافت پلتفرم‌ها برای فیلتر
$stmt = $pdo->prepare("SELECT id, name FROM content_platforms WHERE company_id = ? OR is_default = 1 ORDER BY name");
$stmt->execute([$company_id]);
$platforms = $stmt->fetchAll();

// دریافت وضعیت‌های انتشار برای فیلتر
$stmt = $pdo->prepare("SELECT id, name FROM content_publish_statuses WHERE company_id = ? ORDER BY name");
$stmt->execute([$company_id]);
$publishStatuses = $stmt->fetchAll();

// دریافت لیست تولیدکنندگان محتوا
$stmt = $pdo->prepare("SELECT p.id, CONCAT(p.first_name, ' ', p.last_name) as full_name 
                      FROM personnel p 
                      WHERE p.company_id = ? AND p.is_active = 1 
                      ORDER BY full_name");
$stmt->execute([$company_id]);
$contentCreators = $stmt->fetchAll();

// تابع تولید رنگ بر اساس نام وضعیت
function getStatusColor($statusName) {
    switch (strtolower(trim($statusName))) {
        case 'منتشر شده':
        case 'تکمیل شده':
            return '#28a745'; // سبز
        case 'در انتظار انتشار':
        case 'در حال پیشرفت':
            return '#17a2b8'; // آبی
        case 'پیش‌نویس':
        case 'در دست اقدام':
            return '#ffc107'; // زرد
        case 'لغو شده':
        case 'متوقف شده':
            return '#dc3545'; // قرمز
        default:
            return '#6c757d'; // خاکستری
    }
}

// ساختار داده محتوا برای استفاده در تقویم
$calendarEvents = [];
foreach ($contentItems as $item) {
    $backgroundColor = getStatusColor($item['publish_status']);
    $borderColor = getStatusColor($item['production_status']);
    
    $calendarEvents[] = [
        'id' => $item['id'],
        'title' => $item['title'],
        'start' => $item['publish_date'] . 'T' . $item['publish_time'],
        'backgroundColor' => $backgroundColor,
        'borderColor' => $borderColor,
        'textColor' => '#ffffff',
        'extendedProps' => [
            'description' => $item['description'],
            'contentTypes' => $item['content_types'],
            'platforms' => $item['platforms'],
            'status' => $item['publish_status'],
            'productionStatus' => $item['production_status'],
            'creator' => $item['creator_name']
        ]
    ];
}

// محاسبه تاریخ‌های قبلی و بعدی برای پیمایش
if ($currentView == 'month') {
    $prevDate = date('Y-m-d', strtotime('-1 month', strtotime($date)));
    $nextDate = date('Y-m-d', strtotime('+1 month', strtotime($date)));
} elseif ($currentView == 'week') {
    $prevDate = date('Y-m-d', strtotime('-1 week', strtotime($date)));
    $nextDate = date('Y-m-d', strtotime('+1 week', strtotime($date)));
} else {
    $prevDate = date('Y-m-d', strtotime('-1 day', strtotime($date)));
    $nextDate = date('Y-m-d', strtotime('+1 day', strtotime($date)));
}

include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<style>
.calendar-container {
    height: 800px;
}
.fc-event {
    cursor: pointer;
}
.fc .fc-daygrid-day.fc-day-today {
    background-color: rgba(52, 152, 219, 0.1);
}
.filter-section {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
.view-buttons .btn {
    min-width: 80px;
}
.event-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.legend-item {
    display: flex;
    align-items: center;
    margin-right: 10px;
}
.legend-color {
    width: 15px;
    height: 15px;
    border-radius: 3px;
    margin-left: 5px;
}
.content-detail-row {
    margin-bottom: 10px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
.content-detail-row:last-child {
    border-bottom: none;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>تقویم محتوایی</h1>
    <?php if (hasPermission('add_content')): ?>
        <div>
            <a href="content_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> افزودن محتوای جدید
            </a>
            <a href="content_list.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> لیست محتواها
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- فیلترها -->
<div class="filter-section">
    <form method="GET" action="">
        <input type="hidden" name="view" value="<?php echo $currentView; ?>">
        <input type="hidden" name="date" value="<?php echo $date; ?>">
        
        <div class="row align-items-end">
            <div class="col-md-2">
                <label for="content_type" class="form-label">نوع محتوا</label>
                <select class="form-select" id="content_type" name="content_type">
                    <option value="">همه</option>
                    <?php foreach ($contentTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo ($contentType == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo $type['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="platform" class="form-label">پلتفرم</label>
                <select class="form-select" id="platform" name="platform">
                    <option value="">همه</option>
                    <?php foreach ($platforms as $plat): ?>
                        <option value="<?php echo $plat['id']; ?>" <?php echo ($platform == $plat['id']) ? 'selected' : ''; ?>>
                            <?php echo $plat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">وضعیت انتشار</label>
                <select class="form-select" id="status" name="status">
                    <option value="">همه</option>
                    <?php foreach ($publishStatuses as $stat): ?>
                        <option value="<?php echo $stat['id']; ?>" <?php echo ($status == $stat['id']) ? 'selected' : ''; ?>>
                            <?php echo $stat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="creator" class="form-label">ایجاد کننده</label>
                <select class="form-select" id="creator" name="creator">
                    <option value="">همه</option>
                    <?php foreach ($contentCreators as $creator): ?>
                        <option value="<?php echo $creator['id']; ?>" <?php echo ($creator == $creator['id']) ? 'selected' : ''; ?>>
                            <?php echo $creator['full_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> اعمال فیلتر
                </button>
            </div>
            
            <div class="col-md-2">
                <a href="content_calendar_advanced.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync"></i> پاک کردن فیلترها
                </a>
            </div>
        </div>
    </form>
</div>

<!-- دکمه‌های تغییر نما -->
<div class="view-buttons text-center mb-3">
    <div class="btn-group" role="group">
        <a href="?view=month&date=<?php echo $date; ?>" class="btn <?php echo $currentView == 'month' ? 'btn-primary' : 'btn-outline-primary'; ?>">ماه</a>
        <a href="?view=week&date=<?php echo $date; ?>" class="btn <?php echo $currentView == 'week' ? 'btn-primary' : 'btn-outline-primary'; ?>">هفته</a>
        <a href="?view=day&date=<?php echo $date; ?>" class="btn <?php echo $currentView == 'day' ? 'btn-primary' : 'btn-outline-primary'; ?>">روز</a>
    </div>
</div>

<!-- دکمه‌های پیمایش -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="?view=<?php echo $currentView; ?>&date=<?php echo $prevDate; ?>" class="btn btn-outline-primary">
        <i class="fas fa-chevron-right"></i> قبلی
    </a>
    
    <h4 class="m-0">
        <?php
        if ($currentView == 'month') {
            echo date('F Y', strtotime($date));
        } elseif ($currentView == 'week') {
            echo date('d M', strtotime($weekStart)) . ' تا ' . date('d M Y', strtotime($weekEnd));
        } else {
            echo date('l, d F Y', strtotime($date));
        }
        ?>
    </h4>
    
    <a href="?view=<?php echo $currentView; ?>&date=<?php echo $nextDate; ?>" class="btn btn-outline-primary">
        بعدی <i class="fas fa-chevron-left"></i>
    </a>
</div>

<!-- نمایش تقویم -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div id="calendar" class="calendar-container"></div>
    </div>
</div>

<!-- راهنمای رنگ‌بندی -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">راهنمای رنگ‌بندی وضعیت‌ها</h6>
    </div>
    <div class="card-body">
        <div class="event-legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745;"></div>
                <span>منتشر شده / تکمیل شده</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #17a2b8;"></div>
                <span>در انتظار انتشار / در حال پیشرفت</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107;"></div>
                <span>پیش‌نویس / در دست اقدام</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>لغو شده / متوقف شده</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #6c757d;"></div>
                <span>سایر وضعیت‌ها</span>
            </div>
        </div>
    </div>
</div>

<!-- مدال جزئیات محتوا -->
<div class="modal fade" id="contentDetailModal" tabindex="-1" aria-labelledby="contentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentDetailModalLabel">جزئیات محتوا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">عنوان:</div>
                    <div class="col-md-9" id="content-title"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">تاریخ انتشار:</div>
                    <div class="col-md-9" id="content-date"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">نوع محتوا:</div>
                    <div class="col-md-9" id="content-types"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">پلتفرم‌ها:</div>
                    <div class="col-md-9" id="content-platforms"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">وضعیت انتشار:</div>
                    <div class="col-md-9" id="content-status"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">وضعیت تولید:</div>
                    <div class="col-md-9" id="content-production-status"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">ایجاد کننده:</div>
                    <div class="col-md-9" id="content-creator"></div>
                </div>
                <div class="row content-detail-row">
                    <div class="col-md-3 fw-bold">توضیحات:</div>
                    <div class="col-md-9" id="content-description"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <a href="#" class="btn btn-primary" id="edit-content-btn">ویرایش محتوا</a>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت‌های تقویم -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/fa.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fa',
        headerToolbar: false,
        initialView: '<?php echo $currentView == 'month' ? 'dayGridMonth' : ($currentView == 'week' ? 'timeGridWeek' : 'timeGridDay'); ?>',
        initialDate: '<?php echo $date; ?>',
        direction: 'rtl',
        height: 'auto',
        navLinks: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: <?php echo json_encode($calendarEvents); ?>,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        select: function(info) {
            if (<?php echo hasPermission('add_content') ? 'true' : 'false'; ?>) {
                window.location.href = 'content_add.php?publish_date=' + info.startStr.split('T')[0];
            }
        },
        eventClick: function(info) {
            // نمایش اطلاعات محتوا در مدال
            document.getElementById('content-title').textContent = info.event.title;
            document.getElementById('content-date').textContent = new Date(info.event.start).toLocaleDateString('fa-IR') + ' ' + 
                                                                info.event.start.toLocaleTimeString('fa-IR', {hour: '2-digit', minute:'2-digit'});
            document.getElementById('content-types').textContent = info.event.extendedProps.contentTypes || 'تعیین نشده';
            document.getElementById('content-platforms').textContent = info.event.extendedProps.platforms || 'تعیین نشده';
            document.getElementById('content-status').textContent = info.event.extendedProps.status || 'تعیین نشده';
            document.getElementById('content-production-status').textContent = info.event.extendedProps.productionStatus || 'تعیین نشده';
            document.getElementById('content-creator').textContent = info.event.extendedProps.creator || 'ناشناس';
            document.getElementById('content-description').textContent = info.event.extendedProps.description || 'بدون توضیحات';
            
            // تنظیم لینک دکمه ویرایش
            document.getElementById('edit-content-btn').href = 'content_edit.php?id=' + info.event.id;
            
            // نمایش مدال
            var myModal = new bootstrap.Modal(document.getElementById('contentDetailModal'));
            myModal.show();
        }
    });
    
    calendar.render();
});
</script>

<?php include 'footer.php'; ?>