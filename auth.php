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