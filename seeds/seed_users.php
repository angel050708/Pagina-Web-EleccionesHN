<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/funciones.php';

$pdo = db();

$usuarios = [
    [
        'dni' => '0801-1990-00001',
        'nombre' => 'Administrador General',
        'email' => 'admin@elecciones.hn',
        'telefono' => '+504 2200-0001',
        'rol' => 'administrador',
        'password' => 'Admin!2025',
        'departamento' => 'Francisco Morazán',
        'municipio_codigo' => '0801',
        'centro_codigo' => 'CM-0801',
        'tipo_votacion' => 'nacional',
    ],
    [
        'dni' => '0801-2001-12345',
        'nombre' => 'Juana Votante Ciudadana',
        'email' => 'juana.votante@example.com',
        'telefono' => '+504 9933-2211',
        'rol' => 'votante',
        'password' => 'Juana!2025',
        'departamento' => 'Francisco Morazán',
        'municipio_codigo' => '0801',
        'centro_codigo' => 'CM-0801',
        'tipo_votacion' => 'nacional',
        'fecha_nacimiento' => '1998-07-15',
        'genero' => 'F',
        'direccion' => 'Col. Kennedy, Tegucigalpa',
    ],
];

$pdo->beginTransaction();

try {
    $stmtDepartamento = $pdo->prepare('SELECT id FROM departamentos WHERE nombre = :nombre LIMIT 1');
    $stmtMunicipio = $pdo->prepare('SELECT id FROM municipios WHERE codigo = :codigo LIMIT 1');
    $stmtCentro = $pdo->prepare('SELECT id FROM centros_votacion WHERE codigo = :codigo LIMIT 1');
    $stmtTipo = $pdo->prepare('SELECT id FROM tipos_votacion WHERE codigo = :codigo LIMIT 1');

    $insertUsuario = $pdo->prepare('INSERT INTO usuarios (dni, nombre, email, telefono, rol, password_hash, departamento_id, municipio_id, centro_votacion_id, tipo_votacion_id)
        VALUES (:dni, :nombre, :email, :telefono, :rol, :password_hash, :departamento_id, :municipio_id, :centro_id, :tipo_votacion_id)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), email = VALUES(email), telefono = VALUES(telefono),
            rol = VALUES(rol), password_hash = VALUES(password_hash), departamento_id = VALUES(departamento_id),
            municipio_id = VALUES(municipio_id), centro_votacion_id = VALUES(centro_votacion_id), tipo_votacion_id = VALUES(tipo_votacion_id)');

    $insertVotante = $pdo->prepare('INSERT INTO votantes (usuario_id, fecha_nacimiento, genero, direccion)
        VALUES (:usuario_id, :fecha_nacimiento, :genero, :direccion)
        ON DUPLICATE KEY UPDATE fecha_nacimiento = VALUES(fecha_nacimiento), genero = VALUES(genero), direccion = VALUES(direccion)');

    foreach ($usuarios as $usuario) {
        $stmtDepartamento->execute([':nombre' => $usuario['departamento']]);
        $departamentoId = (int) $stmtDepartamento->fetchColumn();
        if ($departamentoId === 0) {
            throw new RuntimeException('El departamento ' . $usuario['departamento'] . ' no existe. Ejecuta primero scripts/seed_locations.php');
        }

        $stmtMunicipio->execute([':codigo' => $usuario['municipio_codigo']]);
        $municipioId = (int) $stmtMunicipio->fetchColumn();
        if ($municipioId === 0) {
            throw new RuntimeException('El municipio con código ' . $usuario['municipio_codigo'] . ' no está registrado.');
        }

        $stmtCentro->execute([':codigo' => $usuario['centro_codigo']]);
        $centroId = (int) $stmtCentro->fetchColumn();
        if ($centroId === 0) {
            throw new RuntimeException('El centro de votación ' . $usuario['centro_codigo'] . ' no existe.');
        }

        $stmtTipo->execute([':codigo' => $usuario['tipo_votacion']]);
        $tipoVotacionId = (int) $stmtTipo->fetchColumn();
        if ($tipoVotacionId === 0) {
            throw new RuntimeException('El tipo de votación ' . $usuario['tipo_votacion'] . ' no existe.');
        }

        $insertUsuario->execute([
            ':dni' => $usuario['dni'],
            ':nombre' => $usuario['nombre'],
            ':email' => $usuario['email'],
            ':telefono' => $usuario['telefono'],
            ':rol' => $usuario['rol'],
            ':password_hash' => password_hash($usuario['password'], PASSWORD_DEFAULT),
            ':departamento_id' => $departamentoId,
            ':municipio_id' => $municipioId,
            ':centro_id' => $centroId,
            ':tipo_votacion_id' => $tipoVotacionId,
        ]);

        $usuarioId = (int) dbQuery('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1', [':dni' => $usuario['dni']])->fetchColumn();

        if ($usuario['rol'] === 'votante') {
            $insertVotante->execute([
                ':usuario_id' => $usuarioId,
                ':fecha_nacimiento' => $usuario['fecha_nacimiento'] ?? null,
                ':genero' => $usuario['genero'] ?? null,
                ':direccion' => $usuario['direccion'] ?? null,
            ]);
        }

        echo sprintf("Usuario %s (%s) registrado o actualizado.%s", $usuario['nombre'], $usuario['rol'], PHP_EOL);
    }

    $pdo->commit();
    echo PHP_EOL . "Usuarios de ejemplo listos." . PHP_EOL;
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Error al registrar usuarios: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
