<?php
session_start();

include_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/index.php?error=Acceso no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'votante') {
    header('Location: ../login.php?error=Debes iniciar sesión como votante');
    exit;
}

$centroId = isset($_POST['centro_id']) ? (int) $_POST['centro_id'] : 0;

if ($centroId <= 0) {
    header('Location: ../panel/votante/index.php?error=Selecciona un centro de votación válido');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $centro = dbQuery('SELECT c.id, c.nombre, c.codigo, c.direccion, c.capacidad, m.id AS municipio_id, d.id AS departamento_id
                        FROM centros_votacion c
                        INNER JOIN municipios m ON m.id = c.municipio_id
                        INNER JOIN departamentos d ON d.id = m.departamento_id
                        WHERE c.id = :id
                        LIMIT 1', [
        ':id' => $centroId,
    ])->fetch();

    if (!$centro) {
        $pdo->rollBack();
        header('Location: ../panel/votante/index.php?error=El centro seleccionado no existe');
        exit;
    }

    dbQuery('UPDATE usuarios SET centro_votacion_id = :centro, departamento_id = :departamento, municipio_id = :municipio
             WHERE id = :id', [
        ':centro' => $centro['id'],
        ':departamento' => $centro['departamento_id'],
        ':municipio' => $centro['municipio_id'],
        ':id' => (int) $_SESSION['usuario_id'],
    ]);

    $pdo->commit();

    header('Location: ../panel/votante/index.php?message=Centro de votación asignado correctamente');
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../panel/votante/index.php?error=No fue posible asignar el centro: ' . urlencode($e->getMessage()));
    exit;
}
