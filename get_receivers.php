<?php
// get_receivers.php - Get personnel list for receiver selection
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// Verificar y limpiar el ID de la empresa
$companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;

if (!$companyId) {
    echo json_encode(['error' => 'ID de empresa no válido']);
    exit;
}

try {
    // Obtener la lista de personal de la empresa seleccionada
    $stmt = $pdo->prepare("SELECT 
                            p.id, 
                            CONCAT(p.first_name, ' ', p.last_name) as full_name,
                            (CASE WHEN r.is_ceo = 1 THEN 1 ELSE 0 END) as is_ceo
                          FROM personnel p
                          LEFT JOIN roles r ON p.role_id = r.id
                          WHERE p.company_id = ? AND p.is_active = 1
                          ORDER BY r.is_ceo DESC, p.first_name, p.last_name");
    $stmt->execute([$companyId]);
    $receivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver como JSON
    header('Content-Type: application/json');
    echo json_encode($receivers);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}