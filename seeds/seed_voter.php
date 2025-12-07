<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/funciones.php';

$dni = '0801-2001-12345';
$nombre = 'Juana Votante Ciudadana';
$password = 'Juana2025#';
$email = 'juana.votante@example.com';
$telefono = '+504 9933-2211';

try {
    $pdo = db();
    $pdo->beginTransaction();

    $usuarioId = dbQuery('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1', [':dni' => $dni])->fetchColumn();

    if ($usuarioId) {
        dbQuery('UPDATE usuarios SET nombre = :nombre, email = :email, telefono = :telefono, rol = :rol, password_hash = :password
                 WHERE id = :id', [
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':rol' => 'votante',
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $usuarioId,
        ]);

        dbQuery('INSERT INTO votantes (usuario_id, habilitado, fecha_verificacion)
                 VALUES (:usuario_id, 1, NOW())
                 ON DUPLICATE KEY UPDATE habilitado = VALUES(habilitado), fecha_verificacion = VALUES(fecha_verificacion)', [
            ':usuario_id' => $usuarioId,
        ]);

        $pdo->commit();
        echo 'Credenciales del votante actualizadas.' . PHP_EOL;
        exit(0);
    }

    dbQuery('INSERT INTO usuarios (dni, nombre, email, telefono, rol, password_hash)
             VALUES (:dni, :nombre, :email, :telefono, :rol, :password)', [
        ':dni' => $dni,
        ':nombre' => $nombre,
        ':email' => $email,
        ':telefono' => $telefono,
        ':rol' => 'votante',
        ':password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    $nuevoId = (int) dbQuery('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1', [':dni' => $dni])->fetchColumn();

    dbQuery('INSERT INTO votantes (usuario_id, habilitado, fecha_verificacion)
             VALUES (:usuario_id, 1, NOW())', [
        ':usuario_id' => $nuevoId,
    ]);

    $pdo->commit();
    echo 'Votante creado con DNI ' . $dni . ' y contraseña ' . $password . '.' . PHP_EOL;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'No se pudo preparar el votante: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
