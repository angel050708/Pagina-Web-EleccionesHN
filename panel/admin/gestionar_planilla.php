<?php
session_start();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

include_once __DIR__ . '/../../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';
$planillaId = (int)($_POST['id'] ?? 0);

if ($accion === 'eliminar' && $planillaId > 0) {
    try {
        db()->beginTransaction();
        
        dbQuery("DELETE FROM candidatos WHERE planilla_id = ?", [$planillaId]);
        dbQuery("DELETE FROM planillas WHERE id = ?", [$planillaId]);
        
        db()->commit();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Planilla eliminada correctamente']);
    } catch (Exception $e) {
        db()->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la planilla']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
}