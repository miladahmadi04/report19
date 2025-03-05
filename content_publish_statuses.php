<?php
// content_publish_statuses.php - مدیریت وضعیت‌های انتشار محتوا
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

if (!$companyId) {
    redirect('content_management.php');
}

// پردازش فرم افزودن/ویرایش وضعیت
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' && isset($_POST['name'])) {
            // افزودن وضعیت جدید
            $name = clean($_POST['name']);
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            try {
                // اگر این وضعیت به عنوان پیش‌فرض انتخاب شده، وضعیت پیش‌فرض قبلی را برداریم
                if ($isDefault) {
                    $stmt = $pdo->prepare("UPDATE content_publish_statuses SET is_default = 0 WHERE company_id = ?");
                    $stmt->execute([$companyId]);
                }
                
                $stmt = $pdo->prepare("INSERT INTO content_publish_statuses (company_id, name, is_default) VALUES (?, ?, ?)");
                $stmt->execute([$companyId, $name, $isDefault]);
                
                setSuccess('وضعیت انتشار جدید با موفقیت اضافه شد.');
                redirect("content_publish_statuses.php?company=$companyId");
            } catch (PDOException $e) {
                setError('خطا در افزودن وضعیت انتشار.');
            }
        } elseif ($_POST['action'] == 'edit' && isset($_POST['id']) && isset($_POST['name'])) {
            // ویرایش وضعیت
            $id = clean($_POST['id']);
            $name = clean($_POST['name']);
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            try {
                // بررسی اینکه آیا این وضعیت سیستمی است
                $stmt = $pdo->prepare("SELECT is_system FROM content_publish_statuses WHERE id = ? AND company_id = ?");
                $stmt->execute([$id, $companyId]);
                $status = $stmt->fetch();
                
                if ($status && $status['is_system']) {
                    setError('وضعیت‌های سیستمی قابل ویرایش نیستند.');
                    redirect("content_publish_statuses.php?company=$companyId");
                }
                
                // اگر این وضعیت به عنوان پیش‌فرض انتخاب شده، وضعیت پیش‌فرض قبلی را برداریم
                if ($isDefault) {
                    $stmt = $pdo->prepare("UPDATE content_publish_statuses SET is_default = 0 WHERE company_id = ?");
                    $stmt->execute([$companyId]);
                }
                
                $stmt = $pdo->prepare("UPDATE content_publish_statuses SET name = ?, is_default = ? WHERE id = ? AND company_id = ?");
                $stmt->execute([$name, $isDefault, $id, $companyId]);
                
                setSuccess('وضعیت انتشار با موفقیت ویرایش شد.');
                redirect("content_publish_statuses.php?company=$companyId");
            } catch (PDOException $e) {
                setError('خطا در ویرایش وضعیت انتشار.');
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            // حذف وضعیت
            $id = clean($_POST['id']);
            
            try {
                // بررسی اینکه آیا این وضعیت سیستمی است
                $stmt = $pdo->prepare("SELECT is_system FROM content_publish_statuses WHERE id = ? AND company_id = ?");
                $stmt->execute([$id, $companyId]);
                $status = $stmt->fetch();
                
                if ($status && $status['is_system']) {
                    setError('وضعیت‌های سیستمی قابل حذف نیستند.');
                    redirect("content_publish_statuses.php?company=$companyId");
                }
                
                // بررسی استفاده از این وضعیت در محتواها
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM contents WHERE publish_status_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    setError('این وضعیت در محتواها استفاده شده است و قابل حذف نیست.');
                    redirect("content_publish_statuses.php?company=$companyId");
                }
                
                $stmt = $pdo->prepare("DELETE FROM content_publish_statuses WHERE id = ? AND company_id = ? AND is_system = 0");
                $stmt->execute([$id, $companyId]);
                
                setSuccess('وضعیت انتشار با موفقیت حذف شد.');
                redirect("content_publish_statuses.php?company=$companyId");
            } catch (PDOException $e) {
                setError('خطا در حذف وضعیت انتشار.');
            }
        }
    }
}

// دریافت لیست وضعیت‌ها
$stmt = $pdo->prepare("SELECT * FROM content_publish_statuses WHERE company_id = ? ORDER BY is_system DESC, name");
$stmt->execute([$companyId]);
$statuses = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>مدیریت وضعیت‌های انتشار محتوا</h1>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatusModal">
            <i class="fas fa-plus"></i> وضعیت جدید
        </button>
        <a href="content_management.php?company=<?php echo $companyId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

<?php include 'alerts.php'; ?>

<div class="card">
    <div class="card-body">
        <?php if (count($statuses) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>نام وضعیت</th>
                            <th>پیش‌فرض</th>
                            <th>نوع</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statuses as $status): ?>
                            <tr>
                                <td><?php echo $status['name']; ?></td>
                                <td>
                                    <?php if ($status['is_default']): ?>
                                        <span class="badge bg-success">بله</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">خیر</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status['is_system']): ?>
                                        <span class="badge bg-info">سیستمی</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">سفارشی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$status['is_system']): ?>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editStatusModal" 
                                                data-id="<?php echo $status['id']; ?>"
                                                data-name="<?php echo $status['name']; ?>"
                                                data-default="<?php echo $status['is_default']; ?>">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?php echo $status['id']; ?>)">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                هیچ وضعیت انتشاری یافت نشد.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal افزودن وضعیت -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن وضعیت انتشار جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">نام وضعیت</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">وضعیت پیش‌فرض</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ویرایش وضعیت -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش وضعیت انتشار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">نام وضعیت</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_default" name="is_default">
                            <label class="form-check-label" for="edit_is_default">وضعیت پیش‌فرض</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تایید حذف -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header">
                    <h5 class="modal-title">تایید حذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>آیا از حذف این وضعیت اطمینان دارید؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// تنظیم مقادیر مودال ویرایش
document.getElementById('editStatusModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var name = button.getAttribute('data-name');
    var isDefault = button.getAttribute('data-default') == '1';
    
    this.querySelector('#edit_id').value = id;
    this.querySelector('#edit_name').value = name;
    this.querySelector('#edit_is_default').checked = isDefault;
});

// نمایش مودال حذف
function confirmDelete(id) {
    document.getElementById('delete_id').value = id;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include 'footer.php'; ?>