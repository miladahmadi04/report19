<?php
// auth.php - Authentication and authorization functions
session_start();

// Check if the user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

// Check if user has admin privileges
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
}

// Check if user is a CEO
if (!function_exists('isCEO')) {
    function isCEO() {
        return isset($_SESSION['is_ceo']) && $_SESSION['is_ceo'] === true;
    }
}

// Check if user is regular personnel
if (!function_exists('isPersonnel')) {
    function isPersonnel() {
        return isLoggedIn() && !isAdmin() && !isCEO();
    }
}

// تابع بررسی دسترسی کوچ (میلاد احمدی)
if (!function_exists('isCoach')) {
    function isCoach() {
        return isAdmin() || (isset($_SESSION['username']) && $_SESSION['username'] == 'میلاد احمدی');
    }
}

// Require admin access or redirect
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isAdmin()) {
            redirect('index.php');
        }
    }
}

// Require CEO or admin access
if (!function_exists('requireCEOorAdmin')) {
    function requireCEOorAdmin() {
        if (!isAdmin() && !isCEO()) {
            redirect('index.php');
        }
    }
}

// Require logged in user (any type)
if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            redirect('login.php');
        }
    }
}

// Require personnel access only
if (!function_exists('requirePersonnel')) {
    function requirePersonnel() {
        if (!isLoggedIn() || isAdmin()) {
            redirect('index.php');
        }
    }
}

// Admin login function
if (!function_exists('adminLogin')) {
    function adminLogin($username, $password, $pdo) {
        // Clean inputs should be already done in login.php
        
        // Check admin credentials
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        // Verify password
        if ($admin && verifyPassword($password, $admin['password'])) {
            // Set session data
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['logged_in'] = true;
            
            return true;
        }
        
        return false;
    }
}

// Personnel login function
if (!function_exists('personnelLogin')) {
    function personnelLogin($username, $password, $pdo) {
        // Clean inputs should be already done in login.php
        
        // Get personnel info
        $stmt = $pdo->prepare("SELECT p.*, r.is_ceo 
                              FROM personnel p 
                              JOIN roles r ON p.role_id = r.id 
                              WHERE p.username = ? AND p.is_active = 1");
        $stmt->execute([$username]);
        $personnel = $stmt->fetch();
        
        // Verify password
        if ($personnel && verifyPassword($password, $personnel['password'])) {
            // دریافت لیست شرکت‌های مجاز کاربر
            $stmt = $pdo->prepare("SELECT pc.company_id, pc.is_primary, c.name as company_name, c.is_active
                                 FROM personnel_companies pc
                                 JOIN companies c ON pc.company_id = c.id
                                 WHERE pc.personnel_id = ? AND c.is_active = 1
                                 ORDER BY pc.is_primary DESC");
            $stmt->execute([$personnel['id']]);
            $companies = $stmt->fetchAll();
            
            // اگر کاربر به هیچ شرکت فعالی دسترسی ندارد، ورود ناموفق است
            if (empty($companies)) {
                // اگر جدول personnel_companies وجود ندارد یا استفاده نمی‌شود،
                // فقط شرکت اصلی تعیین شده در جدول personnel را استفاده می‌کنیم
                $stmt = $pdo->prepare("SELECT c.id as company_id, 1 as is_primary, c.name as company_name, c.is_active
                                     FROM companies c
                                     WHERE c.id = ? AND c.is_active = 1");
                $stmt->execute([$personnel['company_id']]);
                $companies = $stmt->fetchAll();
                
                if (empty($companies)) {
                    return false;
                }
            }
            
            // شرکت اصلی کاربر (اولین شرکت فعال یا شرکت پیش‌فرض)
            $primaryCompany = $companies[0];
            
            // ذخیره اطلاعات شرکت‌ها در session
            $_SESSION['companies'] = $companies;
            
            // ذخیره اطلاعات کاربر در session
            $_SESSION['user_id'] = $personnel['id'];
            $_SESSION['username'] = $personnel['username'];
            $_SESSION['user_type'] = 'user';
            $_SESSION['company_id'] = $primaryCompany['company_id']; // شرکت فعلی
            $_SESSION['company_name'] = $primaryCompany['company_name'];
            $_SESSION['role_id'] = $personnel['role_id'];
            $_SESSION['is_ceo'] = $personnel['is_ceo'] ? true : false;
            $_SESSION['logged_in'] = true;
            
            return true;
        }
        
        return false;
    }
}
if (!function_exists('switchCompany')) {
    function switchCompany($companyId) {
        // بررسی اینکه کاربر به این شرکت دسترسی دارد
        $hasAccess = false;
        
        if (isset($_SESSION['companies'])) {
            foreach ($_SESSION['companies'] as $company) {
                if ($company['company_id'] == $companyId && $company['is_active']) {
                    $hasAccess = true;
                    $_SESSION['company_id'] = $company['company_id'];
                    $_SESSION['company_name'] = $company['company_name'];
                    break;
                }
            }
        }
        
        return $hasAccess;
    }
}
// Check if user has specific permission
if (!function_exists('hasPermission')) {
    function hasPermission($permissionCode) {
        global $pdo;
        
        // کوچ (میلاد احمدی) دسترسی ویژه برای ثبت گزارش کوچ دارد
        if (isCoach() && $permissionCode == 'add_coach_report') {
            return true;
        }
        
        // Admin has all permissions
        if (isAdmin()) {
            return true;
        }
        
        // Get user's role_id
        if (!isset($_SESSION['role_id'])) {
            return false;
        }
        
        $roleId = $_SESSION['role_id'];
        
        // Check if the permission exists for the role
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM role_permissions rp
             JOIN permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = ? AND p.code = ?"
        );
        $stmt->execute([$roleId, $permissionCode]);
        $hasPermission = $stmt->fetchColumn() > 0;
        
        // Special case: CEOs always have access to view_coach_reports
        if ($permissionCode == 'view_coach_reports' && isCEO()) {
            return true;
        }
        
        return $hasPermission;
    }
}

// Generate password hash
if (!function_exists('generateHash')) {
    function generateHash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Verify password against hash
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

// Generate a random password
if (!function_exists('generateRandomPassword')) {
    function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
}

// Check if user can access reports
if (!function_exists('canAccessReports')) {
    function canAccessReports($personnelId, $pdo) {
        // Admin can access all reports
        if (isAdmin()) {
            return true;
        }
        
        // CEO can access reports from their company
        if (isCEO()) {
            // Get company ID of the personnel
            $stmt = $pdo->prepare("SELECT company_id FROM personnel WHERE id = ?");
            $stmt->execute([$personnelId]);
            $personnelCompanyId = $stmt->fetchColumn();
            
            // Check if CEO's company matches personnel's company
            return $personnelCompanyId == $_SESSION['company_id'];
        }
        
        // Regular personnel can only access their own reports
        return $personnelId == $_SESSION['user_id'];
    }
}

// Redirect helper function
if (!function_exists('redirect')) {
    function redirect($location) {
        header("Location: $location");
        exit;
    }
}