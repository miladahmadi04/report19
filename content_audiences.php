<?php
// content_audiences.php - مدیریت مخاطبین هدف
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی دسترسی ادمین یا مدیر محتوا
if (!isAdmin() && !hasPermission('manage_content')) {
    redirect('index.php');
}

$message = '';
$companyId = isAdmin() ? (isset($_GET['company']) ? clean($_GET['company']) : '') : $_SESSION['company_id'];

// افزودن مخاطب جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_audience'])) {
    $name = clean($_POST['name']);
    $selectedCompany = clean($_POST['company_id']);
    
    if (empty($name)) {
        $message = showError('لطفا نام مخاطب را وارد کنید.');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO content_audiences (company_id, name) VALUES (?, ?)");
            $stmt->execute([$selectedCompany, $name]);
            $message = showSuccess('مخاطب هدف با موفقیت اضافه شد.');
        } catch (PDOException $e) {
            $message = showError('خطا در ثبت مخاطب: ' . $e->getMessage());
        }
    }
}

// ویرایش مخاطب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_audience'])) {
    $audienceId = clean($_POST['audience_id']);
    $name = clean($_POST['name']);
    
    if (empty($name)) {
        $message = showError('لطفا نام مخاطب را وارد کنید.');
    } else {
        try {
            // بررسی اینکه آیا این مخاطب متعلق به شرکت کاربر است
            $canEdit = false;
            if (isAdmin()) {
                $canEdit = true;
            } else {
                $stmt = $pdo->prepare("SELECT company_id FROM content_audiences WHERE id = ?");
                $stmt->execute([$audienceId]);
                $audience = $stmt->fetch();
                if ($audience && $audience['company_id'] == $_SESSION['company_id']) {
                    $canEdit = true;
                }
            }
            
            if ($canEdit) {
                $stmt = $pdo->prepare("UPDATE content_audiences SET name = ? WHERE id = ?");
                $stmt->execute([$name, $audienceId]);
                $message = showSuccess('مخاطب هدف با موفقیت ویرایش شد.');
            } else {
                $message = showError('شما اجازه ویرایش این مخاطب را ندارید.');
            }
        } catch (PDOException $e) {
            $message = showError('خطا در ویرایش مخاطب: ' . $e->getMessage());
        }
    }
}

// حذف مخاطب
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $audienceId = $_GET['delete'];
    
    try {
        // بررسی اینکه آیا این مخاطب متعلق به شرکت کاربر است
        $canDelete = false;
        if (isAdmin()) {
            $canDelete = true;
        } else {
            $stmt = $pdo->prepare("SELECT company_id FROM content_audiences WHERE id = ?");
            $stmt->execute([$audienceId]);
            $audience = $stmt->fetch();
            if ($audience && $audience['company_id'] == $_SESSION['company_id']) {
                $canDelete = true;
            }
        }
        
        if ($canDelete) {
            // بررسی اینکه آیا این مخاطب در استفاده است
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM content_audience_relations WHERE audience_id = ?");
            $stmt->execute([$audienceId]);
            $usageCount = $stmt->fetch()['count'];
            
            if ($usageCount > 0) {
                $message = showError('این مخاطب قابل حذف نیست زیرا در ' . $usageCount . ' محتوا استفاده شده است.');
            } else {
                $stmt = $pdo->prepare("DELETE FROM content_audiences WHERE id = ?");
                $stmt->execute([$audienceId]);
                $message = showSuccess('مخاطب هدف با موفقیت حذف شد.');
            }
        } else {
            $message = showError('شما اجازه حذف این مخاطب را ندارید.');
        }
    } catch (PDOException $e) {
        $message = showError('خطا در حذف مخاطب: ' . $e->getMessage());
    }
}

// دریافت لیست شرکت‌ها برای ادمین
if (isAdmin()) {
    $stmt = $pdo->query("SELECT * FROM companies WHERE is_active = 1 ORDER BY name");
    $companies = $stmt->fetchAll();
}

// دریافت لیست مخاطبین
$query = "SELECT a.*, c.name as company_name, 
         (SELECT COUNT(*) FROM content_audience_relations WHERE audience_id = a.id) as usage_count 
         FROM content_audiences a 
         JOIN companies c ON a.company_id = c.id ";

if (!isAdmin()) {
    $query .= " WHERE a.company_id = ? ";
    $params = [$_SESSION['company_id']];
} else if (!empty($companyId)) {
    $query .= " WHERE a.company_id = ? ";
    $params = [$companyId];
} else {
    $params = [];
}

$query .= " ORDER BY a.company_id, a.name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$audiences = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>مدیریت مخاطبین هدف</h1>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAudienceModal">
            <i class="fas fa-plus"></i> افزودن مخاطب جدید
        </button>
        <a href="content_management.php" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به مدیریت محتوا
        </a>
    </div>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <?php if (count($audiences) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام مخاطب</th>
                            <?php if (isAdmin()): ?>
                                <th>شرکت</th>
                            <?php endif; ?>
                            <th>تعداد استفاده</th>
                            <th>تاریخ ایجاد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audiences as $index => $audience): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $audience['name']; ?></td>
                                <?php if (isAdmin()): ?>
                                    <td><?php echo $audience['company_name']; ?></td>
                                <?php endif; ?>
                                <td><?php echo $audience['usage_count']; ?></td>
                                <td><?php echo $audience['created_at']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-audience" 
                                            data-bs-toggle="modal" data-bs-target="#editAudienceModal"
                                            data-id="<?php echo $audience['id']; ?>"
                                            data-name="<?php echo $audience['name']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($audience['usage_count'] == 0): ?>
                                        <a href="?delete=<?php echo $audience['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('آیا از حذف این مخاطب اطمینان دارید؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">هیچ مخاطبی یافت نشد.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal افزودن مخاطب -->
<div class="modal fade" id="addAudienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن مخاطب جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <?php if (isAdmin()): ?>
                        <div class="mb-3">
                            <label for="company_id" class="form-label">شرکت</label>
                            <select class="form-select" id="company_id" name="company_id" required>
                                <option value="">انتخاب شرکت...</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo $company['id']; ?>" <?php echo $companyId == $company['id'] ? 'selected' : ''; ?>>
                                        <?php echo $company['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="company_id" value="<?php echo $_SESSION['company_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">نام مخاطب</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_audience" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ویرایش مخاطب -->
<div class="modal fade" id="editAudienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش مخاطب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="audience_id" id="edit_audience_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">نام مخاطب</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="edit_audience" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // تنظیم مقادیر مودال ویرایش
    $('.edit-audience').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#edit_audience_id').val(id);
        $('#edit_name').val(name);
    });
});
</script>

<?php include 'footer.php'; ?>