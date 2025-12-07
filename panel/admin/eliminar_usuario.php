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

if (!$usuarioId) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
    exit;
}

// No permitir eliminar el propio usuario
if ($usuarioId == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

try {
    $pdo = db();
    
    // Verificar si el usuario ha votado
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM votos WHERE usuario_id = :id');
    $stmt->execute([':id' => (int) $usuarioId]);
    $haVotado = $stmt->fetchColumn() > 0;
    
    if ($haVotado) {
        // En lugar de eliminar, suspender el usuario si ya ha votado
        $stmt = $pdo->prepare('UPDATE usuarios SET estado = :estado, actualizado_en = NOW() WHERE id = :id');
        $stmt->execute([
            ':estado' => 'suspendido',
            ':id' => (int) $usuarioId
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario suspendido (ha emitido votos, no se puede eliminar completamente)'
        ]);
    } else {
        // Eliminar usuario si no ha votado
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => (int) $usuarioId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error al eliminar usuario: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al eliminar el usuario']);
}
