<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../../includes/funciones.php';

// Verificar autenticación
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$usuarioId = $input['id'] ?? 0;
$estado = $input['estado'] ?? '';

if (!$usuarioId || !in_array($estado, ['activo', 'suspendido'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

// No permitir cambiar el estado del propio usuario
if ($usuarioId == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'error' => 'No puedes cambiar tu propio estado']);
    exit;
}

try {
    $stmt = db()->prepare('UPDATE usuarios SET estado = :estado, actualizado_en = NOW() WHERE id = :id');
    $stmt->execute([
        ':estado' => $estado,
        ':id' => (int) $usuarioId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error al cambiar estado del usuario: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al actualizar el estado']);
}
