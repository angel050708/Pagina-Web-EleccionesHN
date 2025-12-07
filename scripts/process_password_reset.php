<?php
session_start();

include_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'Método no permitido.']);
}

$dni = normalizarDni(limpiarTexto(isset($_POST['dni']) ? $_POST['dni'] : ''));
$password = isset($_POST['password']) ? $_POST['password'] : '';
$passwordConfirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

if (!dniEsValido($dni)) {
    redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'El DNI debe tener el formato 0000-0000-00000.']);
}

if (strlen($password) < 8) {
    redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'La contraseña debe tener al menos 8 caracteres.']);
}

if ($password !== $passwordConfirm) {
    redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'Las contraseñas no coinciden.']);
}

try {
    $usuario = buscarUsuarioPorDni($dni, 'votante');

    if (!$usuario) {
        redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'No encontramos una cuenta con esos datos.']);
    }

    if (isset($usuario['rol']) && $usuario['rol'] !== 'votante') {
        redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'Solo los votantes pueden restablecer la contraseña desde aquí.']);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    dbQuery(
        'UPDATE usuarios
         SET password_hash = :password_hash,
             actualizado_en = NOW()
         WHERE id = :id',
        [
            ':password_hash' => $passwordHash,
            ':id' => (int) $usuario['id'],
        ]
    );
} catch (Throwable $e) {
    error_log('[password_reset] ' . $e->getMessage());
    redirigirConMensaje('../restablecer_contrasena.php', ['error' => 'No se pudo actualizar la contraseña. Intenta nuevamente.']);
}

redirigirConMensaje('../login.php', ['message' => 'Contraseña actualizada. Ya puedes iniciar sesión.']);
