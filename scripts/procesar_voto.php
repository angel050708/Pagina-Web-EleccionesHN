<?php
session_start();

include_once '../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/votar.php?error=Método no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'votante') {
    header('Location: ../login.php?error=Acceso denegado');
    exit;
}

$tipoVoto = strtolower(trim((string) ($_POST['tipo_voto'] ?? '')));

if ($tipoVoto === '') {
    header('Location: ../panel/votante/votar.php?error=' . rawurlencode('Datos incompletos'));
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

if (verificarSiYaVoto($usuarioId, $tipoVoto)) {
    header('Location: ../panel/votante/votar.php?error=' . rawurlencode('Ya registraste tu voto para esta elección.'));
    exit;
}

$pdo = db();
$pdo->beginTransaction();

try {
    $registros = [];

    if ($tipoVoto === 'presidencial') {
        $seleccion = trim((string) ($_POST['candidato_id'] ?? ''));

        $mapaPartidos = [
            '1' => 'DC',
            '2' => 'LIBRE',
            '3' => 'PINU',
            '4' => 'Partido Liberal',
            '5' => 'Partido Nacional',
        ];

        if (!isset($mapaPartidos[$seleccion])) {
            throw new RuntimeException('Selección presidencial inválida.');
        }

        $planilla = dbQuery('SELECT id FROM planillas WHERE tipo = :tipo AND partido = :partido LIMIT 1', [
            ':tipo' => 'presidencial',
            ':partido' => $mapaPartidos[$seleccion],
        ])->fetch();

        if (!$planilla) {
            throw new RuntimeException('No se encontró la planilla presidencial seleccionada.');
        }

        $candidato = dbQuery('SELECT id FROM candidatos WHERE planilla_id = :planilla ORDER BY numero_candidato ASC, id ASC LIMIT 1', [
            ':planilla' => (int) $planilla['id'],
        ])->fetchColumn();

        if (!$candidato) {
            throw new RuntimeException('No hay candidato registrado para la planilla seleccionada.');
        }

        $registros[] = [
            'planilla_id' => (int) $planilla['id'],
            'candidato_id' => (int) $candidato,
        ];
    } elseif ($tipoVoto === 'diputados') {
        $resumenVotante = obtenerResumenVotante($usuarioId);
        $maxDiputados = (int) ($resumenVotante['diputados_cupos'] ?? 0);

        if ($maxDiputados <= 0) {
            throw new RuntimeException('No se pudo determinar el límite de votos para diputados.');
        }

        $seleccionesRaw = $_POST['candidatos_seleccionados'] ?? '';
        $selecciones = json_decode($seleccionesRaw, true);

        if (!is_array($selecciones) || empty($selecciones)) {
            throw new RuntimeException('Debes seleccionar al menos un candidato a diputado.');
        }

        if (count($selecciones) > $maxDiputados) {
            throw new RuntimeException('Superaste el máximo de candidatos permitidos (' . $maxDiputados . ').');
        }

        $candidatosMarcados = [];

        foreach ($selecciones as $seleccion) {
            $planillaId = isset($seleccion['planilla']) ? (int) $seleccion['planilla'] : 0;
            $nombreCandidato = isset($seleccion['nombre']) ? trim($seleccion['nombre']) : '';

            if ($planillaId <= 0 || $nombreCandidato === '') {
                throw new RuntimeException('Los datos de los candidatos seleccionados están incompletos.');
            }

            $planilla = dbQuery('SELECT id, tipo FROM planillas WHERE id = :id LIMIT 1', [
                ':id' => $planillaId,
            ])->fetch();

            if (!$planilla || !in_array($planilla['tipo'], ['diputacion', 'diputados'], true)) {
                throw new RuntimeException('La planilla seleccionada no es válida.');
            }

            $candidatoId = dbQuery('SELECT id FROM candidatos WHERE planilla_id = :planilla AND nombre = :nombre LIMIT 1', [
                ':planilla' => $planillaId,
                ':nombre' => $nombreCandidato,
            ])->fetchColumn();

            if (!$candidatoId) {
                throw new RuntimeException('No se encontró el candidato seleccionado.');
            }

            if (isset($candidatosMarcados[$candidatoId])) {
                throw new RuntimeException('El candidato seleccionado ya fue agregado.');
            }

            $candidatosMarcados[$candidatoId] = true;
            $registros[] = [
                'planilla_id' => $planillaId,
                'candidato_id' => (int) $candidatoId,
            ];
        }

        if (count($registros) > $maxDiputados) {
            throw new RuntimeException('Superaste el máximo de candidatos permitidos (' . $maxDiputados . ').');
        }
    } elseif ($tipoVoto === 'alcalde') {
        $planillaId = isset($_POST['planilla_id']) ? (int)$_POST['planilla_id'] : 0;
        
        if ($planillaId <= 0) {
            throw new RuntimeException('Debes seleccionar un candidato a alcalde.');
        }
        
        $planilla = dbQuery('SELECT id, tipo FROM planillas WHERE id = :id LIMIT 1', [
            ':id' => $planillaId,
        ])->fetch();
        
        if (!$planilla || $planilla['tipo'] !== 'alcaldia') {
            throw new RuntimeException('La planilla seleccionada no es válida para alcalde.');
        }
        
        $candidatos = dbQuery('SELECT id FROM candidatos WHERE planilla_id = :planilla ORDER BY numero_candidato ASC', [
            ':planilla' => $planillaId,
        ])->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($candidatos)) {
            throw new RuntimeException('No se encontraron candidatos para la planilla seleccionada.');
        }
        
        foreach ($candidatos as $candidatoId) {
            $registros[] = [
                'planilla_id' => $planillaId,
                'candidato_id' => (int)$candidatoId,
            ];
        }
    } else {
        throw new RuntimeException('Tipo de votación no soportado.');
    }

    if (!$registros) {
        throw new RuntimeException('No se encontraron votos para registrar.');
    }

    $ipOrigen = $_SERVER['REMOTE_ADDR'] ?? null;
    $hashBase = bin2hex(random_bytes(16));

    $insert = $pdo->prepare('INSERT INTO votos (usuario_id, planilla_id, candidato_id, ip_origen, hash_verificacion) VALUES (:usuario, :planilla, :candidato, :ip, :hash)');

    foreach ($registros as $indice => $registro) {
        $hashVerificacion = hash('sha256', $hashBase . '-' . $registro['planilla_id'] . '-' . microtime(true) . '-' . random_int(0, PHP_INT_MAX));

        $insert->execute([
            ':usuario' => $usuarioId,
            ':planilla' => $registro['planilla_id'],
            ':candidato' => $registro['candidato_id'],
            ':ip' => $ipOrigen,
            ':hash' => $hashVerificacion,
        ]);
    }

    $pdo->commit();

    header('Location: ../panel/votante/votar.php?success=' . rawurlencode('Tu voto se registró correctamente.'));
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Error al procesar voto: ' . $e->getMessage());

    $mensaje = $e instanceof RuntimeException ? $e->getMessage() : 'Error al procesar voto';
    header('Location: ../panel/votante/votar.php?error=' . rawurlencode($mensaje));
    exit;
}
?>