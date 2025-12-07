<?php
session_start();

include_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirConMensaje('../registro.php', ['error' => 'Método no permitido.']);
}

$dni = normalizarDni(limpiarTexto(isset($_POST['dni']) ? $_POST['dni'] : ''));
$nombre = limpiarTexto(isset($_POST['nombre']) ? $_POST['nombre'] : '');
$email = strtolower(limpiarTexto(isset($_POST['email']) ? $_POST['email'] : ''));
$telefono = limpiarTexto(isset($_POST['telefono']) ? $_POST['telefono'] : '');
$tipoVotante = isset($_POST['tipo_votante']) ? $_POST['tipo_votante'] : 'nacional';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$passwordConfirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

if (!dniEsValido($dni)) {
    redirigirConMensaje('../registro.php', ['error' => 'El DNI no cumple el formato 0000-0000-00000.']);
}

if ($nombre === '' || strlen($nombre) < 6) {
    redirigirConMensaje('../registro.php', ['error' => 'Ingresa tu nombre completo.']);
}

if (!in_array($tipoVotante, ['nacional', 'internacional'], true)) {
    redirigirConMensaje('../registro.php', ['error' => 'Selecciona un tipo de votante válido.']);
}

if ($email === '' || !emailEsValido($email)) {
    redirigirConMensaje('../registro.php', ['error' => 'Debes proporcionar un correo electrónico válido.']);
}

if ($telefono === '' || !telefonoEsValido($telefono)) {
    redirigirConMensaje('../registro.php', ['error' => 'Debes proporcionar un teléfono de contacto válido.']);
}

$telefono = normalizarTelefono($telefono);

if (strlen($password) < 8) {
    redirigirConMensaje('../registro.php', ['error' => 'La contraseña debe tener al menos 8 caracteres.']);
}

if ($password !== $passwordConfirm) {
    redirigirConMensaje('../registro.php', ['error' => 'Las contraseñas no coinciden.']);
}

$correoEnviado = false;

try {
    $pdo = db();
    $pdo->beginTransaction();

    $existe = dbQuery('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1', [':dni' => $dni])->fetchColumn();
    if ($existe) {
        $pdo->rollBack();
        redirigirConMensaje('../registro.php', ['error' => 'Ya existe una cuenta con ese DNI.']);
    }

    $tipoVotacionCodigo = $tipoVotante === 'internacional' ? 'exterior' : 'nacional';
    $tipoVotacionId = dbQuery('SELECT id FROM tipos_votacion WHERE codigo = :codigo LIMIT 1', [
        ':codigo' => $tipoVotacionCodigo,
    ])->fetchColumn();

    // Get location from DNI for national voters
    $ubicacion = ['departamento_id' => null, 'municipio_id' => null];
    if ($tipoVotante === 'nacional') {
        $ubicacion = obtenerUbicacionPorDni($dni);
    }

    dbQuery('INSERT INTO usuarios (dni, nombre, email, telefono, rol, tipo_votante, tipo_votacion_id, password_hash, departamento_id, municipio_id)
             VALUES (:dni, :nombre, :email, :telefono, :rol, :tipo_votante, :tipo_votacion_id, :password_hash, :departamento_id, :municipio_id)', [
        ':dni' => $dni,
        ':nombre' => $nombre,
        ':email' => $email,
        ':telefono' => $telefono,
        ':rol' => 'votante',
        ':tipo_votante' => $tipoVotante,
        ':tipo_votacion_id' => $tipoVotacionId ? (int) $tipoVotacionId : null,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':departamento_id' => $ubicacion['departamento_id'],
        ':municipio_id' => $ubicacion['municipio_id'],
    ]);

    $usuarioId = (int) dbQuery('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1', [':dni' => $dni])->fetchColumn();

    dbQuery('INSERT INTO votantes (usuario_id, habilitado, fecha_verificacion)
             VALUES (:usuario_id, 1, NOW())', [
        ':usuario_id' => $usuarioId,
    ]);

    $pdo->commit();

    $correoEnviado = enviarCorreoConfirmacionRegistro($email, $nombre, $dni);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('[registro] ' . $e->getMessage());
    redirigirConMensaje('../registro.php', ['error' => 'Error de registro: ' . $e->getMessage() . ' - Línea: ' . $e->getLine()]);
}

$mensajeFinal = $correoEnviado
    ? 'Tu cuenta fue creada. Revisa tu correo para confirmar tu identidad.'
    : 'Tu cuenta fue creada. Si no recibes el correo de confirmación, comunícate con soporte.';

redirigirConMensaje('../login.php', ['message' => $mensajeFinal]);
