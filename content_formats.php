<?php
// content_formats.php - مدیریت فرمت‌های محتوایی
require_once 'config/db_connect.php';
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

if (empty($companyId)) {
    $_SESSION['error'] = "شناسه شرکت نامعتبر است.";
    redirect('content_management.php');
}

// پردازش فرم افزودن/ویرایش
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add']) || isset($_POST['edit'])) {
        $name = clean($_POST['name']);
        $formatId = isset($_POST['format_id']) ? clean($_POST['format_id']) : null;

        if (empty($name)) {
            $_SESSION['error'] = "نام فرمت الزامی است.";
        } else {
            try {
                if (isset($_POST['add'])) {
                    // افزودن فرمت جدید
                    $stmt = $pdo->prepare("INSERT INTO content_formats (company_id, name, is_system) VALUES (?, ?, 0)");
                    $stmt->execute([$companyId, $name]);
                    $_SESSION['success'] = "فرمت محتوایی با موفقیت اضافه شد.";
                } else {
                    // ویرایش فرمت
                    $stmt = $pdo->prepare("UPDATE content_formats SET name = ? WHERE id = ? AND company_id = ? AND is_system = 0");
                    $stmt->execute([$name, $formatId, $companyId]);
                    $_SESSION['success'] = "فرمت محتوایی با موفقیت ویرایش شد.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "خطا در انجام عملیات: " . $e->getMessage();
            }
        }
    }
    // پردازش حذف فرمت
    elseif (isset($_POST['delete'])) {
        $formatId = clean($_POST['format_id']);
        try {
            // بررسی استفاده از فرمت در محتواها
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM contents WHERE content_format_id = ?");
            $stmt->execute([$formatId]);
            $usageCount = $stmt->fetchColumn();

            if ($usageCount > 0) {
                $_SESSION['error'] = "این فرمت در محتواها استفاده شده و قابل حذف نیست.";
            } else {
                // حذف فرمت غیر سیستمی
                $stmt = $pdo->prepare("DELETE FROM content_formats WHERE id = ? AND company_id = ? AND is_system = 0");
                $stmt->execute([$formatId, $companyId]);
                if ($stmt->rowCount() > 0) {
                    $_SESSION['success'] = "فرمت محتوایی با موفقیت حذف شد.";
                } else {
                    $_SESSION['error'] = "این فرمت قابل حذف نیست.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "خطا در حذف فرمت: " . $e->getMessage();
        }
    }
    redirect('content_formats.php' . (isAdmin() ? '?company=' . $companyId : ''));
}

// بررسی و ایجاد فرمت‌های پیش‌فرض
try {
    // بررسی وجود فرمت‌های پیش‌فرض
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM content_formats WHERE company_id = ? AND is_system = 1");
    $stmt->execute([$companyId]);
    $defaultFormatsExist = $stmt->fetchColumn() > 0;

    if (!$defaultFormatsExist) {
        // ایجاد فرمت‌های پیش‌فرض
        $defaultFormats = [
            'خلاصه',
            'کامل',
            'تیزر'
        ];

        $stmt = $pdo->prepare("INSERT INTO content_formats (company_id, name, is_system) VALUES (?, ?, 1)");
        foreach ($defaultFormats as $format) {
            $stmt->execute([$companyId, $format]);
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "خطا در بررسی/ایجاد فرمت‌های پیش‌فرض: " . $e->getMessage();
}

// دریافت لیست فرمت‌ها
try {
    $stmt = $pdo->prepare("SELECT * FROM content_formats WHERE company_id = ? ORDER BY is_system DESC, name ASC");
    $stmt->execute([$companyId]);
    $formats = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "خطا در دریافت لیست فرمت‌ها: " . $e->getMessage();
    $formats = [];
}

include 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2 class="mb-4">مدیریت فرمت‌های محتوایی</h2>
            
            <?php include 'alerts.php'; ?>

            <!-- دکمه افزودن فرمت جدید -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFormatModal">
                <i class="fas fa-plus"></i> افزودن فرمت جدید
            </button>

            <!-- جدول فرمت‌ها -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>نام فرمت</th>
                            <th>نوع</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formats as $format): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($format['name']); ?></td>
                                <td>
                                    <?php if ($format['is_system']): ?>
                                        <span class="badge bg-info">پیش‌فرض سیستم</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">سفارشی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$format['is_system']): ?>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editFormatModal<?php echo $format['id']; ?>">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteFormatModal<?php echo $format['id']; ?>">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- مودال ویرایش -->
                            <div class="modal fade" id="editFormatModal<?php echo $format['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post">
                                            <div class="modal-header">
                                                <h5 class="modal-title">ویرایش فرمت محتوایی</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="format_id" value="<?php echo $format['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="editName<?php echo $format['id']; ?>" class="form-label">نام فرمت</label>
                                                    <input type="text" class="form-control" id="editName<?php echo $format['id']; ?>" name="name" value="<?php echo htmlspecialchars($format['name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                <button type="submit" name="edit" class="btn btn-primary">ذخیره تغییرات</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- مودال حذف -->
                            <div class="modal fade" id="deleteFormatModal<?php echo $format['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأیید حذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="format_id" value="<?php echo $format['id']; ?>">
                                                <p>آیا از حذف فرمت "<?php echo htmlspecialchars($format['name']); ?>" اطمینان دارید؟</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                <button type="submit" name="delete" class="btn btn-danger">حذف</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن فرمت جدید -->
<div class="modal fade" id="addFormatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن فرمت محتوایی جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">نام فرمت</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>