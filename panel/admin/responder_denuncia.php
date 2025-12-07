<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

header('Content-Type: application/json');

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$denunciaId = (int)($_POST['denuncia_id'] ?? 0);
$nuevoEstado = trim($_POST['nuevo_estado'] ?? '');

if ($denunciaId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de denuncia no válido']);
    exit;
}

$estadosValidos = ['en_revision', 'resuelta', 'rechazada'];
if (!in_array($nuevoEstado, $estadosValidos)) {
    echo json_encode(['success' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    db()->beginTransaction();
    
    // Actualizar estado de la denuncia
    $stmt = db()->prepare("UPDATE denuncias_actos_irregulares SET estado = :estado, actualizada_en = NOW() WHERE id = :id");
    $stmt->execute([
        ':estado' => $nuevoEstado,
        ':id' => $denunciaId
    ]);
    
    // Aquí podrías agregar una tabla para guardar las respuestas/mensajes
    // Por ahora solo actualizamos el estado
    
    db()->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Estado de denuncia actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    error_log('Error al actualizar denuncia: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al procesar la actualización']);
}
