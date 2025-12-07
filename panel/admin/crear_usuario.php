<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../../includes/funciones.php';

// Verificar autenticación
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$rol = $_POST['rol'] ?? 'votante';
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Nombre, email y contraseña son requeridos']);
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

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    $pdo = db();
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'El email ya está registrado']);
        exit;
    }
    
    // Verificar si el DNI ya existe (si se proporcionó)
    if (!empty($cedula)) {
        $dniNormalizado = normalizarDni($cedula);
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE dni = :dni LIMIT 1');
        $stmt->execute([':dni' => $dniNormalizado]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'El DNI ya está registrado']);
            exit;
        }
    }
    
    // Crear el usuario
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, dni, rol, password_hash, estado, creado_en)
         VALUES (:nombre, :email, :dni, :rol, :password_hash, :estado, NOW())'
    );
    
    $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':dni' => !empty($cedula) ? normalizarDni($cedula) : null,
        ':rol' => $rol,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':estado' => 'activo'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado correctamente',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Error al crear usuario: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al crear el usuario']);
}
