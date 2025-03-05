<?php
// content_calendar.php - تقویم محتوایی با FullCalendar
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
    redirect('content_management.php');
}

// دریافت اطلاعات شرکت
$companyStmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
$companyStmt->execute([$companyId]);
$company = $companyStmt->fetch();
$companyName = $company ? $company['name'] : 'شرکت نامشخص';

// دریافت نوع نمایش
$view = isset($_GET['view']) ? clean($_GET['view']) : 'month';

// دریافت تنظیمات نمایش
$stmt = $pdo->prepare("SELECT field_name, is_visible FROM content_calendar_settings WHERE company_id = ?");
$stmt->execute([$companyId]);
$dbSettings = $stmt->fetchAll();

// تنظیمات پیش‌فرض
$settings = [
    'title' => 1,
    'publish_date' => 1,
    'publish_time' => 1,
    'production_status' => 1,
    'publish_status' => 1,
    'topics' => 1,
    'audiences' => 1,
    'types' => 1,
    'platforms' => 1,
    'responsible' => 1,
    'scenario' => 0,
    'description' => 0
];

// ادغام تنظیمات پایگاه داده با تنظیمات پیش‌فرض
if ($dbSettings) {
    foreach ($dbSettings as $setting) {
        $settings[$setting['field_name']] = $setting['is_visible'];
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>تقویم محتوایی - <?php echo htmlspecialchars($companyName); ?></h1>
    <div>
        <button type="button" onclick="openAddContentPopup()" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> افزودن محتوا
        </button>
        <?php if (isAdmin() || isMainTaskResponsible($companyId)): ?>
            <a href="content_calendar_settings.php?company=<?php echo $companyId; ?>" class="btn btn-info me-2">
                <i class="fas fa-cog"></i> تنظیمات نمایش
            </a>
        <?php endif; ?>
        <a href="content_management.php?company=<?php echo $companyId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تقویم محتوایی</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="show-processes" checked>
                        <label class="form-check-label text-white" for="show-processes">نمایش فرآیندهای پس از انتشار</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal نمایش جزئیات محتوا -->
<div class="modal fade" id="viewContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جزئیات محتوا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contentDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">در حال بارگذاری...</span>
                        </div>
                        <p class="mt-2">در حال بارگذاری اطلاعات...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-warning" id="editContentBtn">ویرایش محتوا</button>
                <button type="button" class="btn btn-danger" id="deleteContentBtn">حذف محتوا</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal حذف محتوا -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">تأیید حذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                آیا از حذف این محتوا اطمینان دارید؟ این عمل غیرقابل بازگشت است.
                <p class="font-weight-bold mt-2" id="deleteContentTitle"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">بله، حذف شود</button>
            </div>
        </div>
    </div>
</div>

<!-- افزودن کتابخانه‌های FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/locales-all.min.js"></script>

<!-- افزودن کتابخانه tippy.js برای نمایش tooltip بهتر -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />

<style>
/* تنظیمات برای تقویم */
#calendar {
    direction: rtl;
    text-align: right;
    height: auto;
    min-height: 800px;
}

.fc-header-toolbar {
    margin-bottom: 0.5em !important;
    padding: 0.5em;
}

.fc-event {
    cursor: pointer;
    border: none;
    margin: 2px 0;
    padding: 3px;
    white-space: normal !important;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fc-event-main {
    padding: 4px 6px;
    white-space: normal !important;
}

.fc-daygrid-day {
    cursor: pointer;
}

/* مرز بین ماه‌ها در نمای چند ماهه */
.fc .fc-multimonth-month {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 5px;
    background-color: #fff;
}

/* عنوان ماه در نمای چند ماهه */
.fc .fc-multimonth-month-name {
    font-weight: bold;
    font-size: 1.2em;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 5px;
}

/* رنگ‌بندی برای روزهای غیر ماه جاری */
.fc-day-other {
    background-color: #f8f9fa;
    opacity: 0.7;
}

/* بهبود نمایش روز امروز */
.fc-day-today {
    background-color: rgba(255, 220, 40, 0.15) !important;
    font-weight: bold;
}

/* رنگ‌بندی رویدادها */
.content-event {
    background-color: #4285f4;
    border-left: 4px solid #0d47a1;
    color: white;
}

.process-event {
    background-color: #34a853;
    border-left: 4px solid #1b5e20;
    color: white;
}

/* استایل برای تگ‌ها */
.content-tag {
    display: inline-block;
    font-size: 0.75rem;
    padding: 1px 5px;
    margin: 1px;
    border-radius: 3px;
    background-color: rgba(255, 255, 255, 0.25);
    color: white;
}

/* استایل برای popover بیشتر رویدادها */
.fc-more-popover {
    max-width: 300px;
}

.fc-popover-body {
    max-height: 300px;
    overflow-y: auto;
}

/* ناوبری زمانی */
.time-navigation {
    padding: 5px 0;
    background-color: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 10px;
}

.time-navigation .btn-group {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.time-navigation .btn {
    min-width: 40px;
}

/* استایل‌های بیشتر */
.fc-col-header-cell {
    background-color: #f8f9fa;
    font-weight: bold;
}

.fc-daygrid-day-number {
    font-weight: bold;
    font-size: 1.1em;
}

.fc-daygrid-day-top {
    justify-content: center;
}

/* کمرنگ کردن روزهای گذشته */
.fc .fc-day-past {
    opacity: 0.8;
}

/* هایلایت دوره فعلی */
.fc .fc-highlight {
    background-color: rgba(66, 133, 244, 0.1);
}

/* گذار حالت‌ها */
.fc-event {
    transition: all 0.2s ease;
}
</style>

<script>
let calendar; // برای دسترسی سراسری به تقویم
let contentData = []; // برای ذخیره اطلاعات محتواها
let showProcesses = true;
let activeContentId = null;
let popupWindow = null; // متغیر برای نگهداری مرجع پنجره پاپ آپ

document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');
    
    // تنظیمات نمایش فیلدها
    const displaySettings = <?php echo json_encode($settings); ?>;
    
    // ایجاد نوار ناوبری زمانی
    const timeNavigation = document.createElement('div');
    timeNavigation.className = 'time-navigation mt-3 mb-2 text-center';
    
    // افزودن دکمه‌های جهش برای نمای فعلی
    const currentViewButtons = document.createElement('div');
    currentViewButtons.className = 'btn-group';
    currentViewButtons.innerHTML = `
        <button class="btn btn-outline-primary jump-prev-3" title="۳ دوره قبل">
            <i class="fas fa-angle-double-left"></i>
        </button>
        <button class="btn btn-outline-primary jump-prev" title="دوره قبل">
            <i class="fas fa-angle-left"></i>
        </button>
        <button class="btn btn-outline-primary jump-today" title="امروز">
            امروز
        </button>
        <button class="btn btn-outline-primary jump-next" title="دوره بعد">
            <i class="fas fa-angle-right"></i>
        </button>
        <button class="btn btn-outline-primary jump-next-3" title="۳ دوره بعد">
            <i class="fas fa-angle-double-right"></i>
        </button>
    `;
    
    timeNavigation.appendChild(currentViewButtons);
    
    // افزودن به صفحه
    calendarEl.parentNode.insertBefore(timeNavigation, calendarEl);
    
    // مقداردهی تقویم
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fa',
        timeZone: 'local',
        initialView: '<?php echo $view === 'week' ? 'timeGridWeek' : 'dayGridMonth'; ?>',
        headerToolbar: {
            right: 'prev,next today',
            center: 'title',
            left: 'dayGridMonth,multiMonthYear,timeGridWeek,timeGridDay'
        },
        // اضافه کردن نوار ابزار پایین
        footerToolbar: {
            center: 'prevYear,prev,next,nextYear'
        },
        // تعریف دکمه‌های سفارشی
        customButtons: {
            prevYear: {
                text: 'سال قبل',
                click: function() {
                    calendar.prevYear();
                }
            },
            nextYear: {
                text: 'سال بعد',
                click: function() {
                    calendar.nextYear();
                }
            }
        },
        buttonText: {
            today: 'امروز',
            month: 'ماه',
            multiMonthYear: '۳ ماهه',
            week: 'هفته',
            day: 'روز'
        },
        // تنظیم نماهای مختلف
        views: {
            dayGridMonth: {
                // امکان نمایش ماه‌های مختلف
                titleFormat: { year: 'numeric', month: 'long' },
                buttonText: 'ماه'
            },
            timeGridWeek: {
                // امکان نمایش هفته‌های مختلف
                titleFormat: { year: 'numeric', month: 'short', day: '2-digit' },
                buttonText: 'هفته',
                dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric', omitCommas: true }
            },
            timeGridDay: {
                // امکان نمایش روزهای مختلف
                titleFormat: { year: 'numeric', month: 'long', day: '2-digit' },
                buttonText: 'روز',
                dayHeaderFormat: { weekday: 'long', month: 'long', day: 'numeric', omitCommas: true }
            },
            multiMonthYear: {
                type: 'multiMonth',
                duration: { months: 3 },
                titleFormat: { year: 'numeric' },
                buttonText: '۳ ماهه'
            }
        },
        
        // تنظیمات ظاهری و کارکردی
        firstDay: 6, // شنبه به عنوان اولین روز هفته
        height: 'auto',
        direction: 'rtl',
        navLinks: true, // امکان کلیک روی نام روزها برای رفتن به آن روز
        dayMaxEvents: true, // نمایش دکمه "بیشتر" برای رویدادهای زیاد
        fixedWeekCount: false, // نمایش تعداد متغیر هفته در هر ماه
        showNonCurrentDates: false, // پنهان کردن روزهای ماه‌های دیگر
        
        // تنظیمات ساعت و زمان
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },
        
        // قابلیت‌های تعاملی
        selectable: true,
        editable: false,
        
        events: function(info, successCallback, failureCallback) {
            // دریافت رویدادها از سرور با مدیریت خطا
            fetch('get_calendar_events.php?company=<?php echo $companyId; ?>&start=' + info.startStr + '&end=' + info.endStr)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    contentData = data;
                    
                    // فیلتر کردن فرآیندهای پس از انتشار اگر لازم باشد
                    const filteredEvents = showProcesses ? data : data.filter(event => event.extendedProps.type === 'content');
                    
                    successCallback(filteredEvents);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    // نمایش پیام خطا به کاربر
                    showAlert('danger', 'خطا در دریافت اطلاعات تقویم: ' + error.message);
                    failureCallback(error);
                });
        },
        
        eventDidMount: function(info) {
            // نمایش اطلاعات اضافی روی رویدادها
            const event = info.event;
            const eventEl = info.el;
            
            // اعمال رنگ‌بندی بر اساس نوع
            if (event.extendedProps.type === 'content') {
                eventEl.classList.add('content-event');
            } else {
                eventEl.classList.add('process-event');
            }
            
            // نمایش اطلاعات بیشتر بر اساس تنظیمات
            let additionalInfo = '';
            
            if (displaySettings.platforms && event.extendedProps.platforms) {
                additionalInfo += `<span class="content-tag">${event.extendedProps.platforms}</span>`;
            }
            
            if (displaySettings.topics && event.extendedProps.topics) {
                additionalInfo += `<span class="content-tag">${event.extendedProps.topics}</span>`;
            }
            
            if (additionalInfo) {
                const titleEl = eventEl.querySelector('.fc-event-title');
                if (titleEl) {
                    titleEl.innerHTML += '<div class="mt-1">' + additionalInfo + '</div>';
                }
            }
            
            // افزودن tooltip برای نمایش جزئیات بیشتر
            tippy(eventEl, {
                content: event.extendedProps.description || event.title,
                placement: 'top',
                arrow: true,
                theme: 'light-border'
            });
        },
        
        // تغییر نما
        viewDidMount: function(viewInfo) {
            // ذخیره نمای فعلی در پارامتر URL برای بازیابی آسان
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('view', viewInfo.view.type);
            
            if (viewInfo.view.type === 'timeGridWeek' || viewInfo.view.type === 'timeGridDay') {
                // در نمای هفتگی و روزانه، تاریخ شروع را هم ذخیره کنیم
                urlParams.set('date', viewInfo.view.currentStart.toISOString().substr(0, 10));
            }
            
            // به‌روزرسانی URL بدون بارگذاری مجدد صفحه
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            history.replaceState({}, '', newUrl);
        },
        
        eventClick: function(info) {
            // نمایش جزئیات محتوا در مودال
            const eventData = info.event.extendedProps;
            activeContentId = eventData.contentId;
            
            // باز کردن مودال مناسب
            openContentModal(activeContentId);
        },
        
        dateClick: function(info) {
            // باز کردن پنجره پاپ آپ برای ایجاد محتوا با تاریخ انتخاب شده
            openAddContentPopup(info.dateStr);
        }
    });
    
    // مقداردهی اولیه تقویم
    calendar.render();
    
    // بررسی پارامترهای URL برای بازیابی نما و تاریخ
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('date')) {
        // اگر تاریخ در URL وجود دارد، به آن تاریخ برویم
        calendar.gotoDate(urlParams.get('date'));
    }
    
    // کنترل نمایش فرآیندهای پس از انتشار
    document.getElementById('show-processes').addEventListener('change', function() {
        showProcesses = this.checked;
        calendar.refetchEvents();
    });
    
    // تعریف رفتار دکمه‌های ناوبری
    document.querySelector('.jump-prev-3').addEventListener('click', function() {
        const view = calendar.view.type;
        if (view === 'dayGridMonth') {
            calendar.prev(); calendar.prev(); calendar.prev();
        } else if (view === 'timeGridWeek') {
            calendar.prev(); calendar.prev(); calendar.prev();
        } else if (view === 'timeGridDay') {
            calendar.prev(); calendar.prev(); calendar.prev();
        } else if (view === 'multiMonthYear') {
            calendar.prev(); calendar.prev(); calendar.prev();
        }
    });
    
    document.querySelector('.jump-prev').addEventListener('click', function() {
        calendar.prev();
    });
    
    document.querySelector('.jump-today').addEventListener('click', function() {
        calendar.today();
    });
    
    document.querySelector('.jump-next').addEventListener('click', function() {
        calendar.next();
    });
    
    document.querySelector('.jump-next-3').addEventListener('click', function() {
        const view = calendar.view.type;
        if (view === 'dayGridMonth') {
            calendar.next(); calendar.next(); calendar.next();
        } else if (view === 'timeGridWeek') {
            calendar.next(); calendar.next(); calendar.next();
        } else if (view === 'timeGridDay') {
            calendar.next(); calendar.next(); calendar.next();
        } else if (view === 'multiMonthYear') {
            calendar.next(); calendar.next(); calendar.next();
        }
    });
});

// باز کردن پنجره پاپ آپ برای ایجاد محتوا
function openAddContentPopup(date = '') {
    // اگر پنجره قبلی هنوز باز است، آن را ببندیم
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
    }
    
    let url = 'content_add.php?company=<?php echo $companyId; ?>';
    if (date) {
        url += '&date=' + date;
    }
    
    // تنظیم اندازه و موقعیت پنجره پاپ آپ
    const width = Math.min(1200, window.innerWidth - 100);
    const height = Math.min(800, window.innerHeight - 100);
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;
    
    popupWindow = window.open(url, 'AddContentPopup', `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`);
    
    // اضافه کردن یک تابع برای بررسی وضعیت پنجره پاپ آپ
    const checkPopupClosed = setInterval(function() {
        if (popupWindow.closed) {
            clearInterval(checkPopupClosed);
            // به‌روزرسانی تقویم بعد از بسته شدن پنجره
            refreshCalendar();
        }
    }, 500);
    
    // برای اطمینان از دریافت فوکوس
    popupWindow.focus();
}

// باز کردن پنجره پاپ آپ برای ویرایش محتوا
function openEditContentPopup(contentId) {
    // اگر پنجره قبلی هنوز باز است، آن را ببندیم
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
    }
    
    const url = 'content_edit.php?id=' + contentId + '&company=<?php echo $companyId; ?>';
    
    // تنظیم اندازه و موقعیت پنجره پاپ آپ
    const width = Math.min(1200, window.innerWidth - 100);
    const height = Math.min(800, window.innerHeight - 100);
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;
    
    popupWindow = window.open(url, 'EditContentPopup', `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`);
    
    // اضافه کردن یک تابع برای بررسی وضعیت پنجره پاپ آپ
    const checkPopupClosed = setInterval(function() {
        if (popupWindow.closed) {
            clearInterval(checkPopupClosed);
            // به‌روزرسانی تقویم بعد از بسته شدن پنجره
            refreshCalendar();
        }
    }, 500);
    
    // برای اطمینان از دریافت فوکوس
    popupWindow.focus();
}

// به‌روزرسانی تقویم بدون بارگذاری مجدد صفحه
function refreshCalendar() {
    if (calendar) {
        calendar.refetchEvents();
        showAlert('success', 'تقویم به‌روزرسانی شد');
    }
}

// باز کردن مودال نمایش محتوا
function openContentModal(contentId) {
    const viewModal = new bootstrap.Modal(document.getElementById('viewContentModal'));
    document.getElementById('contentDetails').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">در حال بارگذاری اطلاعات...</p></div>';
    
    // تنظیم دکمه‌های عملیات
    const editBtn = document.getElementById('editContentBtn');
    const deleteBtn = document.getElementById('deleteContentBtn');
    
    // دریافت اطلاعات محتوا
    fetch('get_content_details.php?id=' + contentId)
        .then(response => {
            if (!response.ok) {
                throw new Error("خطا در دریافت اطلاعات محتوا");
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('contentDetails').innerHTML = html;
            viewModal.show();
            
            // اتصال به رویدادهای دکمه‌ها
            editBtn.onclick = function() {
                viewModal.hide();
                openEditContentPopup(contentId);
            };
            
            deleteBtn.onclick = function() {
                viewModal.hide();
                openDeleteModal(contentId);
            };
        })
        .catch(error => {
            document.getElementById('contentDetails').innerHTML = '<div class="alert alert-danger">خطا در بارگذاری اطلاعات: ' + error.message + '</div>';
        });
}

// باز کردن مودال حذف محتوا
function openDeleteModal(contentId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // یافتن عنوان محتوا
    const contentEvent = contentData.find(event => event.extendedProps.contentId === contentId);
    if (contentEvent) {
        document.getElementById('deleteContentTitle').textContent = contentEvent.title;
    }
    
    // تنظیم عملیات دکمه حذف
    document.getElementById('confirmDeleteBtn').onclick = function() {
        fetch('ajax_delete_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + contentId
        })
        .then(response => response.json())
        .then(data => {
            deleteModal.hide();
            if (data.success) {
                // به‌روزرسانی تقویم بدون بارگذاری مجدد صفحه
                refreshCalendar();
                showAlert('success', 'محتوا با موفقیت حذف شد');
            } else {
                showAlert('danger', 'خطا در حذف محتوا: ' + data.message);
            }
        })
        .catch(error => {
            deleteModal.hide();
            showAlert('danger', 'خطا در ارتباط با سرور: ' + error.message);
        });
    };
    
    deleteModal.show();
}

// نمایش پیام هشدار
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = 9999;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // حذف خودکار پس از 5 ثانیه
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php include 'footer.php'; ?>