<?php
require_once 'database.php';
require_once 'functions.php';

try {
    // غیرفعال کردن بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // حذف جداول مرتبط با دسترسی‌ها
    $pdo->exec("DROP TABLE IF EXISTS role_permissions");
    $pdo->exec("DROP TABLE IF EXISTS permissions");
    
    // ایجاد مجدد جدول دسترسی‌ها
    $pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");
    
    // ایجاد مجدد جدول رابطه نقش‌ها و دسترسی‌ها
    $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci");
    
    // تعریف دسترسی‌های پیش‌فرض
    $defaultPermissions = [
        // داشبورد
        ['مشاهده داشبورد', 'view_dashboard', 'دسترسی به صفحه داشبورد'],
        
        // شرکت‌ها
        ['مشاهده شرکت‌ها', 'view_companies', 'مشاهده لیست شرکت‌ها'],
        ['افزودن شرکت', 'add_company', 'افزودن شرکت جدید'],
        ['ویرایش شرکت', 'edit_company', 'ویرایش اطلاعات شرکت'],
        ['حذف شرکت', 'delete_company', 'حذف شرکت'],
        ['تغییر وضعیت شرکت', 'toggle_company', 'فعال/غیرفعال کردن شرکت'],
        
        // پرسنل
        ['مشاهده پرسنل', 'view_personnel', 'مشاهده لیست پرسنل'],
        ['افزودن پرسنل', 'add_personnel', 'افزودن پرسنل جدید'],
        ['ویرایش پرسنل', 'edit_personnel', 'ویرایش اطلاعات پرسنل'],
        ['حذف پرسنل', 'delete_personnel', 'حذف پرسنل'],
        ['تغییر وضعیت پرسنل', 'toggle_personnel', 'فعال/غیرفعال کردن پرسنل'],
        ['بازنشانی رمز عبور', 'reset_password', 'بازنشانی رمز عبور پرسنل'],
        
        // نقش‌ها
        ['مشاهده نقش‌ها', 'view_roles', 'مشاهده لیست نقش‌ها'],
        ['افزودن نقش', 'add_role', 'افزودن نقش جدید'],
        ['ویرایش نقش', 'edit_role', 'ویرایش اطلاعات نقش'],
        ['حذف نقش', 'delete_role', 'حذف نقش'],
        ['مدیریت دسترسی‌ها', 'manage_permissions', 'تنظیم دسترسی‌های هر نقش'],
        
        // دسته‌بندی‌ها
        ['مشاهده دسته‌بندی‌ها', 'view_categories', 'مشاهده لیست دسته‌بندی‌ها'],
        ['افزودن دسته‌بندی', 'add_category', 'افزودن دسته‌بندی جدید'],
        ['ویرایش دسته‌بندی', 'edit_category', 'ویرایش اطلاعات دسته‌بندی'],
        ['حذف دسته‌بندی', 'delete_category', 'حذف دسته‌بندی'],
        
        // گزارش‌های روزانه
        ['مشاهده گزارش‌های روزانه', 'view_daily_reports', 'مشاهده لیست گزارش‌های روزانه'],
        ['افزودن گزارش روزانه', 'add_daily_report', 'ثبت گزارش روزانه جدید'],
        ['ویرایش گزارش روزانه', 'edit_daily_report', 'ویرایش گزارش روزانه'],
        ['حذف گزارش روزانه', 'delete_daily_report', 'حذف گزارش روزانه'],
        
        // گزارش‌های ماهانه
        ['مشاهده گزارش‌های ماهانه', 'view_monthly_reports', 'مشاهده لیست گزارش‌های ماهانه'],
        ['افزودن گزارش ماهانه', 'add_monthly_report', 'ثبت گزارش ماهانه جدید'],
        ['ویرایش گزارش ماهانه', 'edit_monthly_report', 'ویرایش گزارش ماهانه'],
        ['حذف گزارش ماهانه', 'delete_monthly_report', 'حذف گزارش ماهانه'],
        
        // گزارش‌های کوچ
        ['مشاهده گزارش‌های کوچ', 'view_coach_reports', 'مشاهده لیست گزارش‌های کوچ'],
        ['افزودن گزارش کوچ', 'add_coach_report', 'ثبت گزارش کوچ جدید'],
        ['ویرایش گزارش کوچ', 'edit_coach_report', 'ویرایش گزارش کوچ'],
        ['حذف گزارش کوچ', 'delete_coach_report', 'حذف گزارش کوچ'],
        
        // شبکه‌های اجتماعی
        ['مشاهده شبکه‌های اجتماعی', 'view_social_networks', 'مشاهده لیست شبکه‌های اجتماعی'],
        ['افزودن شبکه اجتماعی', 'add_social_network', 'افزودن شبکه اجتماعی جدید'],
        ['ویرایش شبکه اجتماعی', 'edit_social_network', 'ویرایش اطلاعات شبکه اجتماعی'],
        ['حذف شبکه اجتماعی', 'delete_social_network', 'حذف شبکه اجتماعی'],
        
        // صفحات اجتماعی
        ['مشاهده صفحات اجتماعی', 'view_social_pages', 'مشاهده لیست صفحات اجتماعی'],
        ['افزودن صفحه اجتماعی', 'add_social_page', 'افزودن صفحه اجتماعی جدید'],
        ['ویرایش صفحه اجتماعی', 'edit_social_page', 'ویرایش اطلاعات صفحه اجتماعی'],
        ['حذف صفحه اجتماعی', 'delete_social_page', 'حذف صفحه اجتماعی'],
        
        // مدیریت محتوا
        ['مشاهده محتواها', 'view_contents', 'مشاهده لیست محتواها'],
        ['افزودن محتوا', 'add_content', 'افزودن محتوای جدید'],
        ['ویرایش محتوا', 'edit_content', 'ویرایش محتوا'],
        ['حذف محتوا', 'delete_content', 'حذف محتوا'],
        ['مشاهده تقویم محتوا', 'view_content_calendar', 'مشاهده تقویم محتوایی'],
        ['مدیریت قالب‌های محتوا', 'manage_content_templates', 'مدیریت قالب‌های محتوایی'],
        
        // KPI و عملکرد
        ['مشاهده KPI', 'view_kpis', 'مشاهده شاخص‌های کلیدی عملکرد'],
        ['افزودن KPI', 'add_kpi', 'افزودن شاخص جدید'],
        ['ویرایش KPI', 'edit_kpi', 'ویرایش شاخص'],
        ['حذف KPI', 'delete_kpi', 'حذف شاخص'],
        ['مشاهده عملکرد', 'view_performance', 'مشاهده گزارش‌های عملکرد'],
        ['ثبت عملکرد', 'add_performance', 'ثبت عملکرد جدید'],
        ['ویرایش عملکرد', 'edit_performance', 'ویرایش عملکرد'],
        
        // تنظیمات
        ['مشاهده تنظیمات', 'view_settings', 'مشاهده تنظیمات سیستم'],
        ['ویرایش تنظیمات', 'edit_settings', 'ویرایش تنظیمات سیستم']
    ];
    
    // درج دسترسی‌ها
    $insertPermission = $pdo->prepare("INSERT INTO permissions (name, code, description) VALUES (?, ?, ?)");
    foreach ($defaultPermissions as $permission) {
        $insertPermission->execute($permission);
    }
    
    // دریافت شناسه نقش مدیر سیستم
    $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'مدیر سیستم' LIMIT 1");
    $adminRoleId = $stmt->fetchColumn();
    
    if ($adminRoleId) {
        // اختصاص تمام دسترسی‌ها به نقش مدیر سیستم
        $stmt = $pdo->query("SELECT id FROM permissions");
        $permissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $insertRolePermission = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissionIds as $permissionId) {
            $insertRolePermission->execute([$adminRoleId, $permissionId]);
        }
    }
    
    // فعال کردن مجدد بررسی کلیدهای خارجی
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "عملیات با موفقیت انجام شد.";
    
} catch (PDOException $e) {
    echo "خطا: " . $e->getMessage();
} 