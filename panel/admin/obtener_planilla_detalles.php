<?php
session_start();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

include_once __DIR__ . '/../../includes/funciones.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

$planillaId = (int)$_GET['id'];

try {
    $planilla = dbQuery(
        "SELECT p.*, d.nombre as departamento_nombre, m.nombre as municipio_nombre,
                COUNT(c.id) as total_candidatos
         FROM planillas p
         LEFT JOIN departamentos d ON p.departamento_id = d.id
         LEFT JOIN municipios m ON p.municipio_id = m.id
         LEFT JOIN candidatos c ON p.id = c.planilla_id
         WHERE p.id = ?
         GROUP BY p.id",
        [$planillaId]
    )->fetch();
    
    if ($planilla) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'planilla' => $planilla]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Planilla no encontrada']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error interno']);
}