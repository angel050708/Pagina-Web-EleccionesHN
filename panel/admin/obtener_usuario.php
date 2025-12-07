<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../../includes/funciones.php';

// Verificar autenticación
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuarioId = $_GET['id'] ?? 0;

if (!$usuarioId) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
    exit;
}

try {
    $query = "
        SELECT u.*,
               d.nombre as departamento_nombre,
               m.nombre as municipio_nombre,
               cv.nombre as centro_votacion_nombre,
               EXISTS(SELECT 1 FROM votos WHERE usuario_id = u.id LIMIT 1) as ha_votado
        FROM usuarios u
        LEFT JOIN departamentos d ON u.departamento_id = d.id
        LEFT JOIN municipios m ON u.municipio_id = m.id
        LEFT JOIN centros_votacion cv ON u.centro_votacion_id = cv.id
        WHERE u.id = :id
        LIMIT 1
    ";
    
    $usuario = dbQuery($query, [':id' => (int) $usuarioId])->fetch();
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }
    
    // No enviar el hash de contraseña
    unset($usuario['password_hash']);
    
    echo json_encode([
        'success' => true,
        'usuario' => $usuario
    ]);
    
} catch (Exception $e) {
    error_log('Error al obtener usuario: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al obtener el usuario']);
}
