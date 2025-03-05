<?php
require_once 'database.php';
require_once 'functions.php';

try {
    // بررسی تعداد دسترسی‌ها
    $stmt = $pdo->query("SELECT COUNT(*) FROM permissions");
    echo "تعداد دسترسی‌ها: " . $stmt->fetchColumn() . "\n";
    
    // نمایش لیست دسترسی‌ها
    $stmt = $pdo->query("SELECT * FROM permissions ORDER BY code");
    $permissions = $stmt->fetchAll();
    
    echo "\nلیست دسترسی‌ها:\n";
    foreach ($permissions as $permission) {
        echo sprintf("%d. %s (%s)\n", $permission['id'], $permission['name'], $permission['code']);
    }
    
} catch (PDOException $e) {
    echo "خطا: " . $e->getMessage();
} 