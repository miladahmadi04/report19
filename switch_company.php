<?php
// switch_company.php - Change active company for personnel
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// Check if user is logged in and not admin
if (!isLoggedIn() || isAdmin()) {
    redirect('index.php');
}

// Get company_id from URL
if (isset($_GET['company_id']) && is_numeric($_GET['company_id'])) {
    $companyId = clean($_GET['company_id']);
    
    // Try to switch company
    if (switchCompany($companyId)) {
        // Redirect to dashboard
        redirect('personnel_dashboard.php');
    } else {
        // Redirect to dashboard with error
        $_SESSION['error_message'] = 'شما به این شرکت دسترسی ندارید.';
        redirect('personnel_dashboard.php');
    }
} else {
    // Redirect to dashboard
    redirect('personnel_dashboard.php');
}
?>