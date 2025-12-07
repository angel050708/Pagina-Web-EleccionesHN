<?php

include_once __DIR__ . '/../includes/funciones.php';

try {
    // Primero eliminar planillas y candidatos existentes de alcaldía
    dbQuery("DELETE FROM candidatos WHERE planilla_id IN (SELECT id FROM planillas WHERE tipo = 'alcaldia')");
    dbQuery("DELETE FROM planillas WHERE tipo = 'alcaldia'");
    
    // Datos de alcaldías por municipio
    $alcaldes = [
        'Juticalpa' => [
            'municipio_id' => 228,
            'departamento_id' => 15, // Olancho
            'candidatos' => [
                [
                    'partido' => 'DC',
                    'alcalde' => 'Maria de jesus padilla carias',
                    'vicealcalde' => 'ever eduardo guillen rodriguez'
                ],
                [
                    'partido' => 'Libre',
                    'alcalde' => 'victor manuel moreno torres',
                    'vicealcalde' => 'martha argentina saenz rosales'
                ],
                [
                    'partido' => 'PINU',
                    'alcalde' => 'meyrida yaneth tejeda coleman',
                    'vicealcalde' => 'francisco emilio rivera rivera'
                ],
                [
                    'partido' => 'Liberal',
                    'alcalde' => 'jose guillermo trochez montalvan',
                    'vicealcalde' => 'Rolando antonio ordoñez montalvan'
                ],
                [
                    'partido' => 'Nacional',
                    'alcalde' => 'walner reginaldo castro rivera',
                    'vicealcalde' => 'mailin ibeth padilla rivera'
                ]
            ]
        ],
        'San Pedro Sula' => [
            'municipio_id' => 63,
            'departamento_id' => 5, // Cortés
            'candidatos' => [
                [
                    'partido' => 'DC',
                    'alcalde' => 'jose delio boquin rapalo',
                    'vicealcalde' => 'jorge mendoza'
                ],
                [
                    'partido' => 'Libre',
                    'alcalde' => 'rodoldo padilla',
                    'vicealcalde' => 'mauricio ramos'
                ],
                [
                    'partido' => 'PINU',
                    'alcalde' => 'higiinio abarca',
                    'vicealcalde' => 'rosa emilia mejia gutierrez'
                ],
                [
                    'partido' => 'Liberal',
                    'alcalde' => 'roberto contreras mendoza',
                    'vicealcalde' => 'maritza anotnia soto portillo'
                ],
                [
                    'partido' => 'Nacional',
                    'alcalde' => 'yaudel burbara canahuati',
                    'vicealcalde' => 'jenny carolina fernandez erazo'
                ]
            ]
        ],
        'Choloma' => [
            'municipio_id' => 64,
            'departamento_id' => 5, // Cortés
            'candidatos' => [
                [
                    'partido' => 'DC',
                    'alcalde' => 'luis german miranda irias',
                    'vicealcalde' => 'rosa herminia'
                ],
                [
                    'partido' => 'Libre',
                    'alcalde' => 'Gustavo antonio Mejia escobar',
                    'vicealcalde' => 'juan molina'
                ],
                [
                    'partido' => 'PINU',
                    'alcalde' => 'elmer edgardo ortega',
                    'vicealcalde' => 'Andrea isabel cartagena velasquez'
                ],
                [
                    'partido' => 'Liberal',
                    'alcalde' => 'ramin edgardo miranda',
                    'vicealcalde' => 'clarissa giselle garcia talbott'
                ],
                [
                    'partido' => 'Nacional',
                    'alcalde' => 'carlos zerron',
                    'vicealcalde' => 'karla esperwanza escoto tosta'
                ]
            ]
        ],

    ];
    
    foreach ($alcaldes as $municipio => $data) {
        foreach ($data['candidatos'] as $index => $candidato) {
            // Crear planilla para el partido en este municipio
            $stmtPlanilla = db()->prepare(
                "INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado) 
                 VALUES ('alcaldia', :departamento_id, :municipio_id, :nombre, :partido, :descripcion, 'habilitada')"
            );
            
            $nombrePlanilla = "Alcaldía {$municipio} - {$candidato['partido']}";
            $descripcion = "Candidatos a alcalde y vicealcalde de {$municipio} por el partido {$candidato['partido']}";
            
            $stmtPlanilla->execute([
                ':departamento_id' => $data['departamento_id'],
                ':municipio_id' => $data['municipio_id'],
                ':nombre' => $nombrePlanilla,
                ':partido' => $candidato['partido'],
                ':descripcion' => $descripcion
            ]);
            
            $planillaId = db()->lastInsertId();
            
            // Insertar alcalde
            $stmtCandidato = db()->prepare(
                "INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato) 
                 VALUES (:planilla_id, :nombre, :cargo, :numero)"
            );

            $stmtCandidato->execute([
                ':planilla_id' => $planillaId,
                ':nombre' => $candidato['alcalde'],
                ':cargo' => 'Alcalde',
                ':numero' => 1
            ]);
            
            // Insertar vicealcalde
            $stmtCandidato->execute([
                ':planilla_id' => $planillaId,
                ':nombre' => $candidato['vicealcalde'],
                ':cargo' => 'Vicealcalde',
                ':numero' => 2
            ]);
        }
    }
    
    
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
