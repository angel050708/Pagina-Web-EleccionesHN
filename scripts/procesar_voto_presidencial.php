<?php
session_start();

include_once '../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/votar.php?error=Acceso no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'votante') {
    header('Location: ../login.php?error=Debes iniciar sesión como votante');
    exit;
}

// Verificar estado global del proceso de votación
$configProceso = obtenerConfiguracionVotacion();
if (($configProceso['estado'] ?? 'inactivo') !== 'activo') {
    header('Location: ../panel/votante/votar.php?error=' . rawurlencode('El proceso de votación está cerrado.'));
    exit;
}

// Verificar estado del centro de votación del usuario (si aplica)
try {
    $datosUsuario = dbQuery('SELECT centro_votacion_id FROM usuarios WHERE id = :id LIMIT 1', [
        ':id' => (int)($_SESSION['usuario_id'] ?? 0),
    ])->fetch();
    $centroId = isset($datosUsuario['centro_votacion_id']) ? (int) $datosUsuario['centro_votacion_id'] : 0;
    if ($centroId > 0) {
        $estadoCentro = dbQuery('SELECT estado FROM centros_votacion WHERE id = :id LIMIT 1', [
            ':id' => $centroId,
        ])->fetchColumn();
        if ($estadoCentro !== 'activo') {
            header('Location: ../panel/votante/votar.php?error=' . rawurlencode('Tu centro de votación está cerrado.'));
            exit;
        }
    }
} catch (Exception $e) {
    error_log('Error al verificar estado de centro: ' . $e->getMessage());
    header('Location: ../panel/votante/votar.php?error=' . rawurlencode('No se pudo verificar el estado del centro.'));
    exit;
}
$usuarioId = (int) $_SESSION['usuario_id'];
$votoPresidencial = isset($_POST['voto_presidencial']) ? (int) $_POST['voto_presidencial'] : 0;

if ($votoPresidencial <= 0) {
    header('Location: ../panel/votante/votar.php?error=Debes seleccionar un candidato presidencial');
    exit;
}

// Verificar si ya votó
if (verificarSiYaVoto($usuarioId, 'presidencial')) {
    header('Location: ../panel/votante/votar.php?error=Ya has emitido tu voto presidencial');
    exit;
}

// Candidatos válidos
$candidatosValidos = [1, 2, 3, 4, 5]; // IDs de los candidatos
if (!in_array($votoPresidencial, $candidatosValidos)) {
    header('Location: ../panel/votante/votar.php?error=Candidato seleccionado no válido');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Registrar el voto
    dbQuery('INSERT INTO votos (usuario_id, tipo_eleccion, candidato_id, fecha_voto, ip_address) 
             VALUES (:usuario_id, :tipo, :candidato_id, NOW(), :ip)', [
        ':usuario_id' => $usuarioId,
        ':tipo' => 'presidencial',
        ':candidato_id' => $votoPresidencial,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // Actualizar el estado del votante
    dbQuery('UPDATE usuarios SET estado_voto = "votado", fecha_ultimo_voto = NOW() 
             WHERE id = :id', [
        ':id' => $usuarioId
    ]);

    $pdo->commit();

    // Obtener información del candidato votado para el mensaje
    $candidatos = [
        1 => 'Mario Enrique Rivera Callejas (DC)',
        2 => 'Rixi Ramona Moncada Godoy (Libre)', 
        3 => 'Jorge Nelson Ávila Gutiérrez (PINU)',
        4 => 'Salvador Alejandro César Nasralla Salum (PNH)',
        5 => 'Nasry Juan Asfura Zablah (PN)'
    ];
    
    $candidatoNombre = isset($candidatos[$votoPresidencial]) ? $candidatos[$votoPresidencial] : 'Candidato';

    header("Location: ../panel/votante/votar.php?success=" . urlencode("¡Voto registrado exitosamente! Has votado por: {$candidatoNombre}"));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error en procesar_voto_presidencial.php: " . $e->getMessage());
    header('Location: ../panel/votante/votar.php?error=Error al procesar el voto. Inténtalo de nuevo.');
    exit;
}
?>