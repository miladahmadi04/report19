<?php
// content_calendar_settings.php - تنظیمات تقویم محتوایی
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی ادمین یا مدیر محتوا
if (!isAdmin() && !hasPermission('manage_content')) {
    redirect('index.php');
}

$message = '';
$companyId = isAdmin() ? (isset($_GET['company']) ? clean($_GET['company']) : '') : $_SESSION['company_id'];

// اگر شرکت انتخاب نشده است، به صفحه مدیریت محتوا بازگردیم
if (empty($companyId)) {
    redirect('content_management.php');
}

// ذخیره تنظیمات تقویم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        // شروع تراکنش
        $pdo->beginTransaction();
        
        // دریافت فیلدهای تنظیم شده
        $fieldSettings = isset($_POST['fields']) ? $_POST['fields'] : [];
        
        // پاک کردن تنظیمات قبلی
        $stmt = $pdo->prepare("DELETE FROM content_calendar_settings WHERE company_id = ?");
        $stmt->execute([$companyId]);
        
        // درج تنظیمات جدید
        if (!empty($fieldSettings)) {
            $stmt = $pdo->prepare("INSERT INTO content_calendar_settings (company_id, field_name, is_visible, display_order) VALUES (?, ?, ?, ?)");
            
            $order = 1;
            foreach ($fieldSettings as $fieldName => $visibility) {
                $stmt->execute([$companyId, $fieldName, 1, $order]);
                $order++;
            }
        }
        
        $pdo->commit();
        $message = showSuccess('تنظیمات تقویم محتوایی با موفقیت ذخیره شد.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = showError('خطا در ذخیره تنظیمات: ' . $e->getMessage());
    }
}

// دریافت اطلاعات شرکت
$stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$companyName = $stmt->fetchColumn();

// دریافت تنظیمات فعلی
$stmt = $pdo->prepare("SELECT * FROM content_calendar_settings WHERE company_id = ? ORDER BY display_order");
$stmt->execute([$companyId]);
$currentSettings = $stmt->fetchAll();

// تبدیل تنظیمات فعلی به آرایه‌ی ساده‌تر
$visibleFields = [];
foreach ($currentSettings as $setting) {
    $visibleFields[$setting['field_name']] = $setting['is_visible'];
}

// تعریف فیلدهای ممکن برای نمایش در تقویم
$availableFields = [
    'title' => 'عنوان',
    'publish_date' => 'تاریخ انتشار',
    'publish_time' => 'ساعت انتشار',
    'production_status' => 'وضعیت تولید',
    'publish_status' => 'وضعیت انتشار',
    'topics' => 'موضوعات کلی',
    'audiences' => 'مخاطبین هدف',
    'types' => 'نوع محتوا',
    'platforms' => 'پلتفرم‌های انتشار',
    'responsible' => 'مسئول اصلی',
    'scenario' => 'سناریو',
    'description' => 'توضیحات'
];

// بررسی اینکه آیا تنظیمات پیش‌فرض برای این شرکت وجود دارد
if (empty($currentSettings)) {
    // ایجاد تنظیمات پیش‌فرض
    try {
        $stmt = $pdo->prepare("INSERT INTO content_calendar_settings (company_id, field_name, is_visible, display_order) VALUES (?, ?, ?, ?)");
        
        $defaultFields = ['title', 'publish_date', 'publish_time', 'production_status', 'publish_status'];
        
        foreach ($defaultFields as $index => $fieldName) {
            $stmt->execute([$companyId, $fieldName, 1, $index + 1]);
            $visibleFields[$fieldName] = 1;
        }
    } catch (PDOException $e) {
        // در صورت خطا، ادامه می‌دهیم
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>تنظیمات تقویم محتوایی</h1>
    <div>
        <a href="content_management.php<?php echo isAdmin() ? '?company=' . $companyId : ''; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به مدیریت محتوا
        </a>
    </div>
</div>

<?php echo $message; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    تنظیمات تقویم محتوایی برای شرکت <strong><?php echo $companyName; ?></strong>
    <p class="mb-0 mt-2">در این صفحه می‌توانید تعیین کنید که کدام فیلدها در نمای خلاصه تقویم محتوایی نمایش داده شوند.</p>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">فیلدهای قابل نمایش در تقویم</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <?php foreach ($availableFields as $fieldName => $fieldLabel): ?>
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="fields[<?php echo $fieldName; ?>]" 
                                   id="field_<?php echo $fieldName; ?>" 
                                   value="1"
                                   <?php echo isset($visibleFields[$fieldName]) && $visibleFields[$fieldName] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="field_<?php echo $fieldName; ?>">
                                <?php echo $fieldLabel; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i> 
                توجه: فیلدهای انتخاب شده، در نمای خلاصه تقویم محتوایی نمایش داده می‌شوند. 
                برای مشاهده جزئیات کامل، کاربر می‌تواند روی یک محتوا کلیک کند.
            </div>
            
            <div class="d-grid gap-2 col-md-4 mx-auto mt-4">
                <button type="submit" name="save_settings" class="btn btn-success">
                    <i class="fas fa-save"></i> ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">پیش‌نمایش تقویم محتوایی</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            پیش‌نمایش نمونه‌ای از نحوه نمایش یک محتوا در تقویم محتوایی:
        </div>
        
        <div class="calendar-item-preview p-3 border rounded">
            <?php
            $previewContent = '';
            foreach ($availableFields as $fieldName => $fieldLabel) {
                if (isset($visibleFields[$fieldName]) && $visibleFields[$fieldName]) {
                    $sampleValue = '';
                    
                    switch ($fieldName) {
                        case 'title':
                            $sampleValue = 'نمونه عنوان محتوا';
                            break;
                        case 'publish_date':
                            $sampleValue = date('Y-m-d');
                            break;
                        case 'publish_time':
                            $sampleValue = '14:30';
                            break;
                        case 'production_status':
                            $sampleValue = 'محتوا تولید شده';
                            break;
                        case 'publish_status':
                            $sampleValue = 'منتشر نشده';
                            break;
                        case 'topics':
                            $sampleValue = 'محصولات، بازاریابی';
                            break;
                        case 'audiences':
                            $sampleValue = 'مشتریان جدید';
                            break;
                        case 'types':
                            $sampleValue = 'مقاله، پست';
                            break;
                        case 'platforms':
                            $sampleValue = 'اینستاگرام، وبسایت';
                            break;
                        case 'responsible':
                            $sampleValue = 'علی احمدی';
                            break;
                        default:
                            $sampleValue = 'نمونه متن برای ' . $fieldLabel;
                    }
                    
                    $previewContent .= '<div class="mb-1"><strong>' . $fieldLabel . ':</strong> ' . $sampleValue . '</div>';
                }
            }
            
            if (empty($previewContent)) {
                echo '<div class="alert alert-warning">هیچ فیلدی برای نمایش انتخاب نشده است!</div>';
            } else {
                echo $previewContent;
            }
            ?>
        </div>
    </div>
</div>

<style>
    .calendar-item-preview {
        background-color: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<?php include 'footer.php'; ?>