<?php
// switch_company.php - تغییر شرکت جاری کاربر
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// بررسی ورود کاربر
requireLogin();

// بررسی مدیر نبودن کاربر (مدیران به شرکت خاصی متصل نیستند)
if (isAdmin()) {
    redirect('admin_dashboard.php');
}

// دریافت شناسه شرکت از پارامتر URL
$companyId = isset($_GET['company_id']) ? clean($_GET['company_id']) : null;

if (!$companyId) {
    redirect('index.php');
}

// بررسی دسترسی کاربر به شرکت
$hasAccess = false;
$newCompanyName = '';

if (isset($_SESSION['companies'])) {
    foreach ($_SESSION['companies'] as $company) {
        if ($company['company_id'] == $companyId && $company['is_active']) {
            $hasAccess = true;
            $newCompanyName = $company['company_name'];
            break;
        }
    }
}

if (!$hasAccess) {
    // نمایش پیام خطا
    $_SESSION['message'] = showError('شما به این شرکت دسترسی ندارید.');
    redirect('index.php');
}

// تغییر شرکت جاری کاربر
$_SESSION['company_id'] = $companyId;
$_SESSION['company_name'] = $newCompanyName;

// نمایش پیام موفقیت
$_SESSION['message'] = showSuccess("شرکت فعال به «{$newCompanyName}» تغییر یافت.");

// بازگشت به صفحه قبلی یا صفحه اصلی
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
redirect($referer);