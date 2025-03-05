<?php
// refresh_captcha.php - فایل بازسازی کپچا با AJAX

// شروع جلسه اگر شروع نشده باشد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ایجاد کد کپچای جدید
$captchaCode = rand(1000, 9999);
$_SESSION['captcha_code'] = $captchaCode;

// برگرداندن کد کپچای جدید به کلاینت
echo $captchaCode;
?>