<?php
session_start();

include_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirConMensaje('../login.php', ['error' => 'Método no permitido.']);
}

$dni = normalizarDni(limpiarTexto(isset($_POST['dni']) ? $_POST['dni'] : ''));
$password = isset($_POST['password']) ? $_POST['password'] : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : '';

if (!dniEsValido($dni)) {
    redirigirConMensaje('../login.php', ['error' => 'El formato de DNI no es válido.']);
}

if (!in_array($rol, ['votante', 'administrador'], true)) {
    redirigirConMensaje('../login.php', ['error' => 'Debes elegir el tipo de acceso adecuado.']);
}

if ($rol === 'votante' && !dniIndicaMayorDeEdad($dni)) {
    redirigirConMensaje('../login.php', ['error' => 'El sistema solo permite el ingreso a votantes mayores de 18 años.']);
}

$usuario = buscarUsuarioPorDni($dni, $rol);

if (!$usuario) {
    redirigirConMensaje('../login.php', ['error' => 'No encontramos una cuenta que coincida con los datos ingresados.']);
}

if (!password_verify($password, $usuario['password_hash'])) {
    redirigirConMensaje('../login.php', ['error' => 'La contraseña no coincide.']);
}

if ($usuario['estado'] !== 'activo') {
    redirigirConMensaje('../login.php', ['error' => 'Tu usuario está inactivo. Comunícate con soporte.']);
}

actualizarUltimoAcceso($usuario['id']);

$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_rol'] = $usuario['rol'];
$_SESSION['usuario_departamento'] = isset($usuario['departamento']) ? $usuario['departamento'] : null;
$_SESSION['usuario_municipio'] = isset($usuario['municipio']) ? $usuario['municipio'] : null;

if (!empty($_POST['recordarme'])) {
    setcookie('recuerda_dni', $dni, time() + 60 * 60 * 24 * 14, '/', '', false, true);
}

$destinos = [
    'administrador' => '../panel/admin/index.php',
    'votante' => '../panel/votante/index.php',
];

redirigirConMensaje(isset($destinos[$rol]) ? $destinos[$rol] : '../login.php', ['message' => 'Bienvenido ' . $usuario['nombre']]);
