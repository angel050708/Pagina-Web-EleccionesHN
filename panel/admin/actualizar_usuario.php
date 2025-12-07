<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../../includes/funciones.php';

// Verificar autenticación
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuarioId = $_POST['id'] ?? 0;

if (!$usuarioId) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = $_POST['rol'] ?? 'votante';
$estado = $_POST['estado'] ?? 'activo';
$genero = $_POST['genero'] ?? null;
$fechaNacimiento = $_POST['fecha_nacimiento'] ?? null;
$direccion = trim($_POST['direccion'] ?? '');
$departamentoId = $_POST['departamento_id'] ?? null;
$municipioId = $_POST['municipio_id'] ?? null;
$password = trim($_POST['password'] ?? '');

// Validaciones básicas
if (empty($nombre) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Nombre y email son requeridos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email no válido']);
    exit;
}

if (!in_array($rol, ['votante', 'administrador', 'observador'])) {
    echo json_encode(['success' => false, 'error' => 'Rol no válido']);
    exit;
}

if (!in_array($estado, ['activo', 'suspendido'])) {
    echo json_encode(['success' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    $pdo = db();
    
    // Verificar si el email ya existe en otro usuario
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1');
    $stmt->execute([':email' => $email, ':id' => (int) $usuarioId]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'El email ya está en uso por otro usuario']);
        exit;
    }
    
    // Construir la consulta de actualización
    $campos = [
        'nombre = :nombre',
        'email = :email',
        'dni = :dni',
        'telefono = :telefono',
        'rol = :rol',
        'estado = :estado',
        'genero = :genero',
        'fecha_nacimiento = :fecha_nacimiento',
        'direccion = :direccion',
        'departamento_id = :departamento_id',
        'municipio_id = :municipio_id',
        'actualizado_en = NOW()'
    ];
    
    $params = [
        ':nombre' => $nombre,
        ':email' => $email,
        ':dni' => $dni ?: null,
        ':telefono' => $telefono ?: null,
        ':rol' => $rol,
        ':estado' => $estado,
        ':genero' => $genero ?: null,
        ':fecha_nacimiento' => $fechaNacimiento ?: null,
        ':direccion' => $direccion ?: null,
        ':departamento_id' => $departamentoId ?: null,
        ':municipio_id' => $municipioId ?: null,
        ':id' => (int) $usuarioId
    ];
    
    // Si se proporciona una nueva contraseña, incluirla
    if (!empty($password)) {
        $campos[] = 'password_hash = :password_hash';
        $params[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    $query = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = :id';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error al actualizar usuario: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al actualizar el usuario']);
}
