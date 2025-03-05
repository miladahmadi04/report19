<?php
// header.php - Header template for all pages
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم مدیریت شرکت</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="fontstyle.css" rel="stylesheet">
<style>
    body {
        font-family: Tahoma, Arial, sans-serif;
        background-color: #f8f9fa;
    }
    
    /* Navbar styles */
    .navbar-brand {
        font-weight: bold;
    }
    
    /* Content styles */
    .content {
        padding: 20px;
    }
    
    /* Sidebar styles */
    .sidebar {
        min-height: calc(100vh - 56px);
        background-color: #343a40;
        color: #fff;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    .sidebar a {
        color: #e9ecef;
        text-decoration: none;
        padding: 12px 15px;
        display: block;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }
    
    .sidebar a:hover {
        background-color: #495057;
        border-left: 3px solid #6c757d;
    }
    
    .sidebar .active {
        background-color: #495057;
        border-left: 3px solid #007bff;
    }
    
    /* Menu section header */
    .menu-section {
        border-top: 1px solid #4a545e;
        margin-top: 5px;
        padding-top: 5px;
        position: relative;
    }
    
    .menu-header {
        color: #adb5bd;
        padding: 8px 15px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: bold;
    }
    
    /* Submenu Styles */
    .has-submenu {
        position: relative;
    }
    
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s;
        background-color: #2c3136;
    }
    
    /* Show submenu when parent is open or hovered */
    .has-submenu.open .submenu,
    .has-submenu:hover .submenu {
        max-height: 500px;
    }
    
    .submenu-item {
        padding-left: 25px !important;
        font-size: 0.9rem;
    }
    
    /* Badge styles */
    .badge {
        font-size: 80%;
        font-weight: normal;
    }
    
    /* Card styles */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    /* Button styles */
    .btn {
        border-radius: 0.25rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Custom responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            min-height: auto;
        }
        
        .content {
            padding: 15px;
        }
    }
    
    /* Icon spacing */
    .fas, .fab {
        margin-right: 5px;
    }
    
    /* Form control styles */
    .form-control {
        border-radius: 0.25rem;
    }
    
    /* Alert styles */
    .alert {
        border-radius: 0.25rem;
    }
    
    /* Table responsive styles */
    .table-responsive {
        margin-bottom: 1rem;
    }
    
    /* Pagination styles */
    .pagination {
        justify-content: center;
    }
    
    /* Performance level indicator styles */
    .performance-indicator {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .performance-excellent {
        background-color: #28a745;
    }
    
    .performance-good {
        background-color: #007bff;
    }
    
    .performance-average {
        background-color: #ffc107;
    }
    
    .performance-poor {
        background-color: #dc3545;
    }
</style>

</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">سیستم مدیریت شرکت</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <?php if (!isAdmin() && isset($_SESSION['companies']) && count($_SESSION['companies']) > 1): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="companyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-building"></i> <?php echo $_SESSION['company_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="companyDropdown">
                        <?php foreach ($_SESSION['companies'] as $company): ?>
                            <li>
                                <a class="dropdown-item <?php echo ($company['company_id'] == $_SESSION['company_id']) ? 'active' : ''; ?>" 
                                   href="switch_company.php?company_id=<?php echo $company['company_id']; ?>">
                                    <?php echo $company['company_name']; ?>
                                    <?php if (isset($company['is_primary']) && $company['is_primary']): ?>
                                        <span class="badge bg-secondary">اصلی</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            <?php if (isAdmin()): ?>
                                <span class="badge bg-danger">مدیر سیستم</span>
                            <?php elseif (isCEO()): ?>
                                <span class="badge bg-warning text-dark">مدیر عامل</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> خروج
                        </a>
                    </li>
                    <?php if (hasPermission('view_reports')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-file-alt"></i> گزارش‌ها
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['can_view_coach_reports']) && $_SESSION['can_view_coach_reports']): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="coach_report_list.php">
                                <i class="fas fa-chart-line"></i> گزارش کوچ
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar p-0">
                <div class="py-4">
                    <!-- بخش مربوط به منوی مدیریتی در header.php -->
                    <!-- برای مدیر سیستم -->
                    <?php if (isAdmin()): ?>
                        <!-- Admin Menu -->
                        <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i> داشبورد
                        </a>
                        <a href="companies.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'active' : ''; ?>">
                            <i class="fas fa-building me-2"></i> مدیریت شرکت‌ها
                        </a>
                        <a href="personnel.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'personnel.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users me-2"></i> مدیریت پرسنل
                        </a>
                        <a href="roles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'roles.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-tag me-2"></i> نقش‌های کاربری
                        </a>
                        <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tags me-2"></i> دسته‌بندی‌ها
                        </a>
                        
                        <!-- منوی مدیریت محتوا -->
                        <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['content_management.php', 'content_add.php', 'content_edit.php', 'content_list.php', 'content_view.php', 'content_calendar.php', 'content_templates.php', 'content_template_add.php', 'content_template_edit.php', 'content_topics.php', 'content_audiences.php', 'content_platforms.php', 'content_types.php', 'content_tasks.php', 'content_production_statuses.php', 'content_publish_statuses.php', 'content_formats.php']) ? 'active-menu' : ''; ?>">
                            <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['content_management.php', 'content_add.php', 'content_edit.php', 'content_list.php', 'content_view.php', 'content_calendar.php', 'content_templates.php', 'content_template_add.php', 'content_template_edit.php', 'content_topics.php', 'content_audiences.php', 'content_platforms.php', 'content_types.php', 'content_tasks.php', 'content_production_statuses.php', 'content_publish_statuses.php', 'content_formats.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-file-alt me-2"></i> مدیریت محتوا <i class="fas fa-chevron-down float-end mt-1"></i>
                            </a>
                            <div class="submenu">
                                <a href="content_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_management.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-home me-2"></i> صفحه اصلی
                                </a>
                                <a href="content_add.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_add.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-plus me-2"></i> ثبت محتوای جدید
                                </a>
                                <a href="content_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_list.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-list me-2"></i> لیست محتواها
                                </a>
                                <a href="content_calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_calendar.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-calendar-alt me-2"></i> تقویم محتوایی
                                </a>
                                <a href="content_templates.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_templates.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-file-code me-2"></i> قالب‌های محتوایی
                                </a>
                                <div class="menu-section">
                                    <div class="menu-header">تنظیمات</div>
                                    <a href="content_topics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_topics.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-tag me-2"></i> موضوعات کلی
                                    </a>
                                    <a href="content_audiences.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_audiences.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-users me-2"></i> مخاطبین هدف
                                    </a>
                                    <a href="content_platforms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_platforms.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-share-alt me-2"></i> پلتفرم‌های انتشار
                                    </a>
                                    <a href="content_types.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_types.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-file me-2"></i> انواع محتوا
                                    </a>
                                    <a href="content_tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_tasks.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-tasks me-2"></i> وظایف
                                    </a>
                                    <a href="content_production_statuses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_production_statuses.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-industry me-2"></i> وضعیت‌های تولید
                                    </a>
                                    <a href="content_publish_statuses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_publish_statuses.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-check-circle me-2"></i> وضعیت‌های انتشار
                                    </a>
                                    <a href="content_formats.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content_formats.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-file-alt me-2"></i> فرمت‌های محتوایی
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <a href="admin_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog me-2"></i> پروفایل مدیر
                        </a>
                        <!-- منوی مدیریت گزارشات -->
                        <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_reports.php', 'view_report.php', 'report_management.php', 'report_categories.php']) ? 'active-menu' : ''; ?>">
                            <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_reports.php', 'view_report.php', 'report_management.php', 'report_categories.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-clipboard-list me-2"></i> مدیریت گزارشات <i class="fas fa-chevron-down float-end mt-1"></i>
                            </a>
                            <div class="submenu">
                                <a href="view_reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_reports.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-list me-2"></i> لیست گزارشات
                                </a>
                                <a href="report_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'report_management.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-chart-pie me-2"></i> آمار و تحلیل گزارشات
                                </a>
                            </div>
                        </div>
                        
                        <a href="admin_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog me-2"></i> پروفایل مدیر
                        </a>
                        
                    
                        <!-- منوی hover شبکه‌های اجتماعی -->
                        <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_networks.php', 'social_network_fields.php', 'social_pages.php', 'kpi_models.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active-menu' : ''; ?>">
                            <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_networks.php', 'social_network_fields.php', 'social_pages.php', 'kpi_models.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-share-alt me-2"></i> شبکه‌های اجتماعی <i class="fas fa-chevron-down float-end mt-1"></i>
                            </a>
                            <div class="submenu">
                                <a href="social_networks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'social_networks.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-hashtag me-2"></i> مدیریت شبکه‌ها
                                </a>
                                <a href="social_network_fields.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'social_network_fields.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-list-ul me-2"></i> فیلدهای شبکه‌ها
                                </a>
                                <a href="social_pages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'social_pages.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-file-alt me-2"></i> صفحات اجتماعی
                                </a>
                                <a href="kpi_models.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kpi_models.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-chart-line me-2"></i> مدل‌های KPI
                                </a>
                            </div>
                        </div>
                        
                        <?php if (hasPermission('view_coach_reports')): ?>
                            <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['coach_report.php', 'coach_report_list.php', 'coach_report_view.php']) ? 'active-menu' : ''; ?>">
                                <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['coach_report.php', 'coach_report_list.php', 'coach_report_view.php']) ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-line me-2"></i> گزارش کوچ <i class="fas fa-chevron-down float-end mt-1"></i>
                                </a>
                                <div class="submenu">
                                    <a href="coach_report_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'coach_report_list.php' ? 'active' : ''; ?> submenu-item">
                                        <i class="fas fa-list me-2"></i> لیست گزارش‌ها
                                    </a>
                                    <?php if (hasPermission('add_coach_report')): ?>
                                        <a href="coach_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'coach_report.php' ? 'active' : ''; ?> submenu-item">
                                            <i class="fas fa-plus me-2"></i> گزارش جدید
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php elseif (isCEO()): ?>
                        <!-- CEO Menu -->
                        <a href="personnel_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'personnel_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i> داشبورد
                        </a>
                        <a href="view_reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_reports.php' || basename($_SERVER['PHP_SELF']) == 'view_report.php' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard me-2"></i> گزارش‌های روزانه
                        </a>
                        
                        <!-- Social media section for CEO -->
                        <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_pages.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active-menu' : ''; ?>">
                            <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_pages.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-share-alt me-2"></i> شبکه‌های اجتماعی <i class="fas fa-chevron-down float-end mt-1"></i>
                            </a>
                            <div class="submenu">
                                <a href="social_pages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'social_pages.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-file-alt me-2"></i> صفحات اجتماعی
                                </a>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Personnel Menu -->
                        <a href="personnel_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'personnel_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i> داشبورد
                        </a>
                        <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard me-2"></i> ثبت گزارش روزانه
                        </a>
                        <a href="view_reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_reports.php' || basename($_SERVER['PHP_SELF']) == 'view_report.php' ? 'active' : ''; ?>">
                            <i class="fas fa-list me-2"></i> مشاهده گزارش‌ها
                        </a>
                        
                        <!-- Social media reporting for regular personnel -->
                        <div class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_pages.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active-menu' : ''; ?>">
                            <a href="#" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['social_pages.php', 'page_kpi.php', 'social_report.php', 'view_social_report.php', 'expected_performance.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-share-alt me-2"></i> شبکه‌های اجتماعی <i class="fas fa-chevron-down float-end mt-1"></i>
                            </a>
                            <div class="submenu">
                                <a href="social_pages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'social_pages.php' ? 'active' : ''; ?> submenu-item">
                                    <i class="fas fa-file-alt me-2"></i> صفحات اجتماعی
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- این کد باید در header.php در بخش منوی سایدبار اضافه شود -->

<!-- بخش منوی پرسنل -->
<?php if (!isAdmin() && !isCEO()): ?>

    
    <!-- اضافه کردن دسترسی به گزارشات کوچ برای دریافت کنندگان گزارش -->
    <?php
    // بررسی آیا کاربر فعلی دریافت کننده گزارش کوچ است
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_reports WHERE receiver_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isCoachReportReceiver = $stmt->fetchColumn() > 0;
    
    if ($isCoachReportReceiver):
    ?>
    <a href="coach_report_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'coach_report_list.php' || basename($_SERVER['PHP_SELF']) == 'coach_report_view.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line me-2"></i> گزارشات کوچ
    </a>
    <?php endif; ?>
    
    <!-- سایر منوهای پرسنل -->
<?php endif; ?>
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-md-10 content">