<?php
header('Content-Type: application/json');

include_once __DIR__ . '/../../includes/funciones.php';

$departamentoId = $_GET['departamento_id'] ?? 0;

if (!$departamentoId) {
    echo json_encode(['success' => false, 'error' => 'Departamento no especificado']);
    exit;
}

try {
    $query = "SELECT id, nombre FROM municipios WHERE departamento_id = ? ORDER BY nombre ASC";
    $municipios = dbQuery($query, [(int) $departamentoId])->fetchAll();
    
    echo json_encode([
        'success' => true,
        'municipios' => $municipios
    ]);
    
} catch (Exception $e) {
    error_log('Error al obtener municipios: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al obtener municipios']);
}
