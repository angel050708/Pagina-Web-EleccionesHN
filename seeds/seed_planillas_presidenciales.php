<?php
require_once __DIR__ . '/../includes/database.php';

$pdo = db();

$planillasPresidenciales = [
    [
        'nombre' => 'Planilla Presidencial DC',
        'partido' => 'DC',
        'tipo' => 'presidencial',
        'departamento_id' => null,
        'municipio_id' => null
    ],
    [
        'nombre' => 'Planilla Presidencial LIBRE',
        'partido' => 'LIBRE',
        'tipo' => 'presidencial',
        'departamento_id' => null,
        'municipio_id' => null
    ],
    [
        'nombre' => 'Planilla Presidencial PINU',
        'partido' => 'PINU',
        'tipo' => 'presidencial',
        'departamento_id' => null,
        'municipio_id' => null
    ],
    [
        'nombre' => 'Planilla Presidencial Partido Liberal',
        'partido' => 'Partido Liberal',
        'tipo' => 'presidencial',
        'departamento_id' => null,
        'municipio_id' => null
    ],
    [
        'nombre' => 'Planilla Presidencial Partido Nacional',
        'partido' => 'Partido Nacional',
        'tipo' => 'presidencial',
        'departamento_id' => null,
        'municipio_id' => null
    ]
];

$candidatosPresidenciales = [
    1 => ['nombre' => 'Mario Enrique Rivera Callejas'],
    2 => ['nombre' => 'Rixi Ramona Moncada Godoy'],
    3 => ['nombre' => 'Jorge Nelson Avila Gutierrez'],
    4 => ['nombre' => 'Salvador Alejandro Cesar Nasralla Salum'],
    5 => ['nombre' => 'Nasry Juan Asfura Zablah']
];

try {
    $pdo->beginTransaction();
    
    $insertPlanilla = $pdo->prepare("
        INSERT INTO planillas (nombre, partido, tipo, departamento_id, municipio_id, estado, creada_en) 
        VALUES (:nombre, :partido, :tipo, :departamento_id, :municipio_id, 'habilitada', NOW())
    ");
    
    $insertCandidato = $pdo->prepare("
        INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato, creado_en) 
        VALUES (:planilla_id, :nombre, :cargo, :numero, NOW())
    ");
    
    $pdo->query("DELETE FROM candidatos WHERE planilla_id IN (SELECT id FROM planillas WHERE tipo = 'presidencial')");
    $pdo->query("DELETE FROM planillas WHERE tipo = 'presidencial'");
    
    foreach ($planillasPresidenciales as $index => $planilla) {
        $insertPlanilla->execute([
            ':nombre' => $planilla['nombre'],
            ':partido' => $planilla['partido'],
            ':tipo' => $planilla['tipo'],
            ':departamento_id' => $planilla['departamento_id'],
            ':municipio_id' => $planilla['municipio_id']
        ]);
        
        $planillaId = $pdo->lastInsertId();
        $numeroPlanilla = $index + 1;
        
        $candidato = $candidatosPresidenciales[$numeroPlanilla] ?? null;

        if ($candidato) {
            $insertCandidato->execute([
                ':planilla_id' => $planillaId,
                ':nombre' => $candidato['nombre'],
                ':cargo' => 'Presidente',
                ':numero' => 1
            ]);
        }
    }
    
    $pdo->commit();
    echo "Planillas presidenciales creadas exitosamente." . PHP_EOL;
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear planillas presidenciales: " . $e->getMessage() . PHP_EOL;
}