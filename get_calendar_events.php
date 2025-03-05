<?php
// get_calendar_events.php - دریافت رویدادهای تقویم
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// تنظیم هدر پاسخ به JSON
header('Content-Type: application/json; charset=utf-8');

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    echo json_encode(['error' => 'دسترسی غیرمجاز']);
    exit;
}

// دریافت شناسه شرکت
$companyId = isAdmin() ? 
    (isset($_GET['company']) && is_numeric($_GET['company']) ? clean($_GET['company']) : null) : 
    $_SESSION['company_id'];

if (!$companyId) {
    echo json_encode(['error' => 'شرکت مشخص نشده']);
    exit;
}

// دریافت تاریخ‌های شروع و پایان
$start = isset($_GET['start']) ? clean($_GET['start']) : date('Y-m-d');
$end = isset($_GET['end']) ? clean($_GET['end']) : date('Y-m-d', strtotime('+30 days'));

try {
    $events = [];
    
    // دریافت محتواها
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.title,
            c.publish_date,
            c.publish_time,
            c.scenario,
            c.description,
            ps.name as publish_status,
            ps.color as publish_status_color,
            prs.name as production_status,
            prs.color as production_status_color,
            GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as topics,
            GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as platforms
        FROM 
            contents c
        LEFT JOIN 
            content_publish_statuses ps ON c.publish_status_id = ps.id
        LEFT JOIN 
            content_production_statuses prs ON c.production_status_id = prs.id
        LEFT JOIN 
            content_topic_relations ctr ON c.id = ctr.content_id
        LEFT JOIN 
            content_topics t ON ctr.topic_id = t.id
        LEFT JOIN 
            content_platform_relations cpr ON c.id = cpr.content_id
        LEFT JOIN 
            content_platforms p ON cpr.platform_id = p.id
        WHERE 
            c.company_id = ? AND
            c.publish_date BETWEEN ? AND ?
        GROUP BY 
            c.id
        ORDER BY 
            c.publish_date, c.publish_time
    ");
    
    $stmt->execute([$companyId, $start, $end]);
    $contents = $stmt->fetchAll();
    
    foreach ($contents as $content) {
        $events[] = [
            'id' => 'content_' . $content['id'],
            'title' => $content['title'],
            'start' => $content['publish_date'] . 'T' . ($content['publish_time'] ?: '10:00:00'),
            'extendedProps' => [
                'type' => 'content',
                'contentId' => $content['id'],
                'topics' => $content['topics'],
                'platforms' => $content['platforms'],
                'description' => $content['description'],
                'productionStatus' => $content['production_status'],
                'productionStatusColor' => $content['production_status_color'],
                'publishStatus' => $content['publish_status'],
                'publishStatusColor' => $content['publish_status_color'],
                'scenario' => $content['scenario']
            ]
        ];
    }
    
    // دریافت فرآیندهای پس از انتشار
    $stmt = $pdo->prepare("
        SELECT 
            cpp.id,
            c.id as content_id,
            c.title as content_title,
            f.name as format_name,
            DATE_ADD(c.publish_date, INTERVAL cpp.days_after DAY) as process_date,
            cpp.publish_time as process_time,
            ps.name as publish_status,
            ps.color as publish_status_color,
            GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as platforms
        FROM 
            content_post_publish_processes cpp
        JOIN 
            contents c ON cpp.content_id = c.id
        JOIN 
            content_formats f ON cpp.format_id = f.id
        LEFT JOIN 
            content_publish_statuses ps ON c.publish_status_id = ps.id
        LEFT JOIN 
            post_publish_platform_relations pppr ON cpp.id = pppr.process_id
        LEFT JOIN 
            content_platforms p ON pppr.platform_id = p.id
        WHERE 
            c.company_id = ? AND
            DATE_ADD(c.publish_date, INTERVAL cpp.days_after DAY) BETWEEN ? AND ?
        GROUP BY 
            cpp.id
        ORDER BY 
            process_date, process_time
    ");
    
    $stmt->execute([$companyId, $start, $end]);
    $processes = $stmt->fetchAll();
    
    foreach ($processes as $process) {
        $processTitle = $process['format_name'] . ' - ' . $process['content_title'];
        
        $events[] = [
            'id' => 'process_' . $process['id'],
            'title' => $processTitle,
            'start' => $process['process_date'] . 'T' . ($process['process_time'] ?: '10:00:00'),
            'extendedProps' => [
                'type' => 'process',
                'contentId' => $process['content_id'],
                'processId' => $process['id'],
                'platforms' => $process['platforms'],
                'description' => 'فرآیند پس از انتشار: ' . $process['format_name'] . ' برای محتوای "' . $process['content_title'] . '"',
                'processType' => $process['format_name'],
                'processStatus' => $process['publish_status'],
                'processStatusColor' => $process['publish_status_color'],
                'relatedContent' => $process['content_title']
            ]
        ];
    }
    
    echo json_encode($events);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    error_log('Calendar Events Error: ' . $e->getMessage());
}