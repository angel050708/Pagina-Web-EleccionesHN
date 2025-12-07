<?php
session_start();

include_once '../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/seleccionar_tipo_votacion.php?error=Acceso no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'votante') {
    header('Location: ../login.php?error=Debes iniciar sesión como votante');
    exit;
}

$tipoVotante = isset($_POST['tipo_votante']) ? trim($_POST['tipo_votante']) : '';

if (!in_array($tipoVotante, ['nacional', 'internacional'])) {
    header('Location: ../panel/votante/seleccionar_tipo_votacion.php?error=Selecciona un tipo de votación válido');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $usuarioId = (int) $_SESSION['usuario_id'];
    
    // Verificar que el usuario no tenga ya un tipo asignado
    $existe = dbQuery('SELECT tipo_votante FROM usuarios WHERE id = :id LIMIT 1', [
        ':id' => $usuarioId
    ])->fetchColumn();

    if (!empty($existe)) {
        $pdo->rollBack();
        header('Location: ../panel/votante/index.php?info=Ya tienes un tipo de votación asignado');
        exit;
    }

    // Actualizar el tipo de votante
    dbQuery('UPDATE usuarios SET tipo_votante = :tipo WHERE id = :id', [
        ':tipo' => $tipoVotante,
        ':id' => $usuarioId
    ]);

    // Si es internacional, asignar un centro exterior por defecto (Estados Unidos)
    if ($tipoVotante === 'internacional') {
        $centroExterior = dbQuery('SELECT id FROM centros_votacion_exterior 
                                   WHERE pais = "Estados Unidos de América" 
                                   ORDER BY id ASC LIMIT 1')->fetchColumn();
        
        if ($centroExterior) {
            dbQuery('UPDATE usuarios SET centro_votacion_exterior_id = :centro WHERE id = :id', [
                ':centro' => $centroExterior,
                ':id' => $usuarioId
            ]);
        }
    }

    $pdo->commit();

    // Redirigir al panel principal
    $mensaje = $tipoVotante === 'nacional' 
        ? 'Tipo de votación configurado: Nacional. Ahora puedes seleccionar tu centro de votación.'
        : 'Tipo de votación configurado: Internacional. Se te ha asignado un centro en el extranjero.';

    header("Location: ../panel/votante/index.php?success=" . urlencode($mensaje));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error en procesar_tipo_votacion.php: " . $e->getMessage());
    header('Location: ../panel/votante/seleccionar_tipo_votacion.php?error=Error al procesar la selección. Inténtalo de nuevo.');
    exit;
}
?>