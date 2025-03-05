<?php
// login.php - Login page
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } else {
        redirect('personnel_dashboard.php');
    }
}

// ایجاد کپچای ساده
function generateSimpleCaptcha() {
    // ایجاد یک عدد تصادفی بین 1000 تا 9999
    $captchaCode = rand(1000, 9999);
    $_SESSION['captcha_code'] = $captchaCode;
    
    return $captchaCode;
}

// تولید کپچای جدید اگر وجود نداشته باشد
if (!isset($_SESSION['captcha_code'])) {
    $captchaCode = generateSimpleCaptcha();
} else {
    $captchaCode = $_SESSION['captcha_code'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = clean($_POST['password']);
    $captchaInput = isset($_POST['captcha']) ? clean($_POST['captcha']) : '';
    
    // بررسی کپچا
    $captchaValid = isset($_SESSION['captcha_code']) && $captchaInput == $_SESSION['captcha_code'];
    
    if (empty($username) || empty($password)) {
        $error = 'لطفا نام کاربری و رمز عبور را وارد کنید.';
        $captchaCode = generateSimpleCaptcha(); // تولید کپچای جدید
    } elseif (empty($captchaInput)) {
        $error = 'لطفا کد امنیتی را وارد کنید.';
        $captchaCode = generateSimpleCaptcha(); // تولید کپچای جدید
    } elseif (!$captchaValid) {
        $error = 'کد امنیتی وارد شده صحیح نیست.';
        $captchaCode = generateSimpleCaptcha(); // تولید کپچای جدید
    } else {
        // Try admin login first
        if (adminLogin($username, $password, $pdo)) {
            // برای مقابله با حملات Timing Attack، بعد از ورود ناموفق موفقیت نیز تأخیر ایجاد می‌کنیم
            usleep(rand(100000, 300000)); // تأخیر 100-300 میلی‌ثانیه
            
            // ثبت لاگ ورود موفق
            error_log("Successful login for username: " . $username . " (admin) - IP: " . $_SERVER['REMOTE_ADDR']);
            
            // بررسی و تنظیم احتمالی تعداد تلاش‌های ناموفق
            if (isset($_SESSION['login_attempts'])) {
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt_time']);
            }
            
            // پاکسازی کد کپچا پس از ورود موفق
            unset($_SESSION['captcha_code']);
            
            redirect('admin_dashboard.php');
        } 
        // Then try personnel login
        else if (personnelLogin($username, $password, $pdo)) {
            // تأخیر مشابه برای مقابله با Timing Attack
            usleep(rand(100000, 300000));
            
            // ثبت لاگ ورود موفق
            error_log("Successful login for username: " . $username . " (personnel) - IP: " . $_SERVER['REMOTE_ADDR']);
            
            // بررسی و تنظیم احتمالی تعداد تلاش‌های ناموفق
            if (isset($_SESSION['login_attempts'])) {
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt_time']);
            }
            
            // پاکسازی کد کپچا پس از ورود موفق
            unset($_SESSION['captcha_code']);
            
            redirect('personnel_dashboard.php');
        } else {
            // تأخیر برای جلوگیری از حملات Brute Force و Timing Attack
            usleep(rand(100000, 300000)); // تأخیر 100-300 میلی‌ثانیه
            
            // ثبت لاگ ورود ناموفق
            error_log("Failed login attempt for username: " . $username . " - IP: " . $_SERVER['REMOTE_ADDR']);
            
            // ثبت تعداد تلاش‌های ناموفق
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 1;
                $_SESSION['last_attempt_time'] = time();
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
            }
            
            // اگر تعداد تلاش‌های ناموفق بیش از حد مجاز بود، محدودیت زمانی اعمال کنید
            if ($_SESSION['login_attempts'] >= 5) {
                $error = 'به دلیل تلاش‌های ناموفق متعدد، لطفاً 5 دقیقه بعد دوباره امتحان کنید.';
                
                // محدودیت زمانی 5 دقیقه
                if ($_SESSION['login_attempts'] >= 5 && time() - $_SESSION['last_attempt_time'] > 300) {
                    // اگر 5 دقیقه گذشته بود، تلاش‌ها را ریست کنید
                    $_SESSION['login_attempts'] = 0;
                    unset($_SESSION['last_attempt_time']);
                }
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است.';
            }
            
            // تولید کپچای جدید پس از تلاش ناموفق
            $captchaCode = generateSimpleCaptcha();
        }
    }
}

// Debug information
if (isset($_POST['username'])) {
    error_log("Login attempt for username: " . $_POST['username']);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Tahoma, Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-floating > label {
            right: 0;
            left: auto;
            padding-right: 15px;
        }
        .form-floating > .form-control {
            padding-right: 15px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
        .captcha-container {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .captcha-code {
            display: inline-block;
            padding: 10px 15px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            background-color: #f0f0f0;
            color: #333;
            border-radius: 4px;
            margin-left: 10px;
            text-decoration: line-through;
            font-family: 'Courier New', monospace;
            background-image: 
                linear-gradient(45deg, transparent 0%, transparent 20%, #ddd 20%, #ddd 40%, transparent 40%, transparent 60%, #ddd 60%, #ddd 80%, transparent 80%, transparent 100%),
                linear-gradient(-45deg, transparent 0%, transparent 20%, #ddd 20%, #ddd 40%, transparent 40%, transparent 60%, #ddd 60%, #ddd 80%, transparent 80%, transparent 100%);
        }
        .refresh-captcha {
            display: inline-block;
            padding: 8px 12px;
            font-size: 18px;
            color: #007bff;
            cursor: pointer;
        }
        .refresh-captcha:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-container">
                <!-- لوگوی خود را اینجا قرار دهید -->
                <h2 class="text-center mb-4">ورود به سیستم</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="نام کاربری" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <label for="username"><i class="fas fa-user me-2"></i> نام کاربری</label>
                    <div class="invalid-feedback">
                        لطفا نام کاربری را وارد کنید.
                    </div>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="رمز عبور" required>
                    <label for="password"><i class="fas fa-lock me-2"></i> رمز عبور</label>
                    <div class="invalid-feedback">
                        لطفا رمز عبور را وارد کنید.
                    </div>
                </div>
                
                <!-- کپچای ساده -->
                <div class="mb-3">
                    <label for="captcha" class="form-label">کد امنیتی</label>
                    <div class="captcha-container">
                        <div class="captcha-code"><?php echo $captchaCode; ?></div>
                        <a href="javascript:void(0)" class="refresh-captcha" title="تولید کد جدید" id="refresh-captcha-btn">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                    <input type="text" class="form-control" id="captcha" name="captcha" placeholder="کد امنیتی را وارد کنید" required>
                    <div class="invalid-feedback">
                        لطفا کد امنیتی را وارد کنید.
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i> ورود</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // اعتبارسنجی فرم
        (function() {
            'use strict';
            
            // فرم‌ها را پیدا کن
            var forms = document.querySelectorAll('.needs-validation');
            
            // برای هر فرم از submit جلوگیری کن اگر معتبر نبود
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
            
            // کد مربوط به تازه‌سازی کپچا با ایجکس
            document.getElementById('refresh-captcha-btn').addEventListener('click', function() {
                // ایجاد یک درخواست ایجکس
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'refresh_captcha.php', true);
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        // بروزرسانی کد کپچا در صفحه
                        document.querySelector('.captcha-code').textContent = xhr.responseText;
                    }
                };
                
                xhr.send();
            });
        })();
    </script>
</body>
</html>