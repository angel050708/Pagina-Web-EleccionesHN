<?php
session_start();

include_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/index.php?error=Acceso no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] !== 'votante')) {
    header('Location: ../login.php?error=Debes iniciar sesión como votante');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$perfil = obtenerResumenVotante($usuarioId);

if (!$perfil || (isset($perfil['tipo_votante']) && $perfil['tipo_votante'] !== 'internacional')) {
    header('Location: ../panel/votante/index.php?error=Esta opción solo está disponible para votantes en el exterior');
    exit;
}

$centroId = isset($_POST['centro_id']) ? (int) $_POST['centro_id'] : 0;

if ($centroId <= 0) {
    header('Location: ../panel/votante/index.php?error=Selecciona una sede válida');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $centro = dbQuery('SELECT id, pais, estado, ciudad, sector_electoral, nombre, direccion
                        FROM centros_votacion_exterior
                        WHERE id = :id
                        LIMIT 1', [
        ':id' => $centroId,
    ])->fetch();

    if (!$centro) {
        $pdo->rollBack();
        header('Location: ../panel/votante/index.php?error=La sede seleccionada no existe');
        exit;
    }

    dbQuery('UPDATE usuarios
             SET centro_votacion_exterior_id = :centro,
                 centro_votacion_id = NULL
             WHERE id = :id', [
        ':centro' => $centro['id'],
        ':id' => (int) $_SESSION['usuario_id'],
    ]);

    $pdo->commit();

    header('Location: ../panel/votante/index.php?message=Sede consular asignada correctamente');
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../panel/votante/index.php?error=No fue posible asignar la sede: ' . urlencode($e->getMessage()));
    exit;
}
