
<?php
include_once __DIR__ . '/database.php';

function limpiarTexto($valor)
{
    return trim(strip_tags((string) $valor));
}


function emailEsValido($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


function telefonoEsValido($telefono)
{
    $soloDigitos = preg_replace('/\D+/', '', (string) $telefono);

    return $soloDigitos !== '' && strlen($soloDigitos) >= 8 && strlen($soloDigitos) <= 15;
}


function normalizarTelefono($telefono)
{
    $limpio = trim((string) $telefono);

    return preg_replace('/\s+/', ' ', $limpio);
}

function enviarCorreoConfirmacionRegistro($destinatario, $nombre, $dni)
{
    if ($destinatario === '' || !emailEsValido($destinatario)) {
        return false;
    }

    $nombreDestino = $nombre !== '' ? $nombre : 'votante';
    $dniNormalizado = normalizarDni($dni);

    $asunto = 'Confirmacion de registro - EleccionesHN';
    $lineas = [
        'Hola ' . $nombreDestino . ',',
        '',
        'Hemos recibido tu solicitud de registro en EleccionesHN con el DNI ' . $dniNormalizado . '.',
        'Si no realizaste este registro, responde a este correo o comunicate con el soporte del CNE.',
        '',
        'Gracias por participar.',
        '',
        'EleccionesHN'
    ];

    $mensaje = implode(PHP_EOL, $lineas);
    $encabezados = [
        'From: EleccionesHN <no-reply@eleccioneshn.local>',
        'Reply-To: soporte@eleccioneshn.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    if (@mail($destinatario, $asunto, $mensaje, implode("\r\n", $encabezados))) {
        return true;
    }

    error_log('[correo_confirmacion] No se pudo enviar el correo a ' . $destinatario);
    return false;
}

function normalizarDni($dni)
{
    $soloDigitos = preg_replace('/[^0-9]/', '', (string) $dni);

    if (strlen($soloDigitos) !== 13) {
        return limpiarTexto($dni);
    }

    return substr($soloDigitos, 0, 4) . '-' . substr($soloDigitos, 4, 4) . '-' . substr($soloDigitos, 8);
}


function obtenerUbicacionPorDni($dni)
{
    if (!dniEsValido($dni)) {
        return ['departamento_id' => null, 'municipio_id' => null];
    }

    try {
        $dniNormalizado = normalizarDni($dni);
        $partes = explode('-', $dniNormalizado);
        $codigoMunicipio = $partes[0]; 

        $resultado = dbQuery('SELECT m.id as municipio_id, m.departamento_id 
                              FROM municipios m 
                              WHERE m.codigo = :codigo 
                              LIMIT 1', [':codigo' => $codigoMunicipio])->fetch();

        if ($resultado) {
            return [
                'departamento_id' => (int) $resultado['departamento_id'],
                'municipio_id' => (int) $resultado['municipio_id']
            ];
        }
    } catch (Exception $e) {
        error_log('Error en obtenerUbicacionPorDni: ' . $e->getMessage());
    }

    return ['departamento_id' => null, 'municipio_id' => null];
}



function redirigirConMensaje($ruta, array $mensajes = [], int $statusCode = 302)
{
    $url = $ruta;

    if (!empty($mensajes)) {
        $query = http_build_query($mensajes);
        $url .= (strpos($ruta, '?') === false ? '?' : '&') . $query;
    }

    header('Location: ' . $url, true, $statusCode);
    exit;
}
 


function dniEsValido($dni)
{
    return preg_match('/^\d{4}-\d{4}-\d{5}$/', (string) $dni) === 1;
}

function dniIndicaMayorDeEdad($dni)
{
    if (!dniEsValido($dni)) {
        return false;
    }

    try {
        $stmt = db()->prepare(
            'SELECT v.fecha_nacimiento
             FROM usuarios u
             LEFT JOIN votantes v ON v.usuario_id = u.id
             WHERE u.dni = :dni
             LIMIT 1'
        );
        $stmt->execute([':dni' => $dni]);
        $fechaNacimiento = $stmt->fetchColumn();

        if ($fechaNacimiento) {
            $nacimiento = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
            if ($nacimiento instanceof DateTimeInterface) {
                $hoy = new DateTimeImmutable('today');
                return $nacimiento->diff($hoy)->y >= 18;
            }
        }

        $partes = explode('-', $dni);
        if (!isset($partes[1]) || !ctype_digit($partes[1])) {
            return false;
        }

        $anio = (int) $partes[1];
        if ($anio === 0) {
            return false;
        }

        $hoy = (int) date('Y');
        return ($hoy - $anio) >= 18;
    } catch (Exception $e) {
        error_log('dniIndicaMayorDeEdad error: ' . $e->getMessage());
        return false;
    }
}

function buscarUsuarioPorDni($dni, $rol = null)
{
    if (!dniEsValido($dni)) {
        return null;
    }

    try {
        $sql = 'SELECT * FROM usuarios WHERE dni = :dni';
        $params = [':dni' => $dni];

        if ($rol) {
            $sql .= ' AND rol = :rol';
            $params[':rol'] = $rol;
        }

        $sql .= ' LIMIT 1';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null;
        }

        $usuario['dni'] = normalizarDni($usuario['dni']);

        return $usuario;
    } catch (Exception $e) {
        error_log('Error en buscarUsuarioPorDni: ' . $e->getMessage());
        return null;
    }
}

function actualizarUltimoAcceso($usuarioId)
{
    try {
        dbQuery(
            'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id',
            [':id' => (int) $usuarioId]
        );
    } catch (Exception $e) {
        error_log('No se pudo actualizar el último acceso: ' . $e->getMessage());
    }
}

function obtenerResumenVotante($identificador)
{
    if (empty($identificador)) {
        return null;
    }

    $filtro = 'u.id = :identificador';
    $parametro = [':identificador' => (int) $identificador];

    if (!is_numeric($identificador)) {
        $dniNormalizado = normalizarDni($identificador);
        if (!dniEsValido($dniNormalizado)) {
            return null;
        }

        $filtro = 'u.dni = :identificador';
        $parametro = [':identificador' => $dniNormalizado];
    }

    try {
        $sql = 'SELECT u.id,
                       u.dni,
                       u.nombre,
                       u.email,
                       u.telefono,
                       u.rol,
                       u.tipo_votante,
                       u.estado,
                       u.departamento_id,
                       u.municipio_id,
                       u.centro_votacion_id,
                       u.centro_votacion_exterior_id,
                       u.tipo_votacion_id,
                       u.ultimo_acceso,
                       u.creado_en,
                       u.actualizado_en,
                       d.nombre AS departamento_nombre,
                       d.diputados_cupos,
                       m.nombre AS municipio_nombre,
                       cv.nombre AS centro_nombre,
                       cv.direccion AS centro_direccion,
                       cv.codigo AS centro_codigo,
                       cve.pais AS centro_exterior_pais,
                       cve.estado AS centro_exterior_estado,
                       cve.ciudad AS centro_exterior_ciudad,
                       tv.codigo AS tipo_votacion_codigo,
                       v.fecha_nacimiento,
                       v.genero,
                       v.direccion AS direccion_residencia,
                       v.municipio_emision,
                       v.habilitado,
                       v.fecha_verificacion
                FROM usuarios u
                LEFT JOIN votantes v ON v.usuario_id = u.id
                LEFT JOIN departamentos d ON d.id = u.departamento_id
                LEFT JOIN municipios m ON m.id = u.municipio_id
                LEFT JOIN centros_votacion cv ON cv.id = u.centro_votacion_id
                LEFT JOIN centros_votacion_exterior cve ON cve.id = u.centro_votacion_exterior_id
                LEFT JOIN tipos_votacion tv ON tv.id = u.tipo_votacion_id
                WHERE ' . $filtro . '
                LIMIT 1';

        $stmt = db()->prepare($sql);
        $stmt->execute($parametro);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            return null;
        }

        $datos['dni'] = normalizarDni($datos['dni']);

        $ubicacion = [
            'departamento_codigo' => null,
            'municipio_codigo' => null,
            'anio_emision' => null,
            'correlativo' => null,
            'departamento' => null,
            'municipio' => null,
        ];

        $partesDni = explode('-', $datos['dni']);
        if (isset($partesDni[0])) {
            $ubicacion['municipio_codigo'] = $partesDni[0];
            $ubicacion['departamento_codigo'] = substr($partesDni[0], 0, 2);
        }
        if (isset($partesDni[1])) {
            $ubicacion['anio_emision'] = $partesDni[1];
        }
        if (isset($partesDni[2])) {
            $ubicacion['correlativo'] = $partesDni[2];
        }

        if (!empty($ubicacion['municipio_codigo'])) {
            $stmtMunicipio = db()->prepare(
                'SELECT 
                    m.id AS municipio_id,
                    m.nombre AS municipio,
                    d.id AS departamento_id,
                    d.nombre AS departamento,
                    d.diputados_cupos AS depto_diputados_cupos
                 FROM municipios m
                 INNER JOIN departamentos d ON d.id = m.departamento_id
                 WHERE m.codigo = :codigo
                 LIMIT 1'
            );
            $stmtMunicipio->execute([':codigo' => $ubicacion['municipio_codigo']]);
            $result = $stmtMunicipio->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                
                $ubicacion['municipio'] = $result['municipio'];
                $ubicacion['departamento'] = $result['departamento'];

                
                if (empty($datos['municipio_id']) && !empty($result['municipio_id'])) {
                    $datos['municipio_id'] = (int) $result['municipio_id'];
                    $datos['municipio_nombre'] = $result['municipio'];
                }
                if (empty($datos['departamento_id']) && !empty($result['departamento_id'])) {
                    $datos['departamento_id'] = (int) $result['departamento_id'];
                    $datos['departamento_nombre'] = $result['departamento'];
                    
                    if (empty($datos['diputados_cupos']) && $result['depto_diputados_cupos'] !== null) {
                        $datos['diputados_cupos'] = (int) $result['depto_diputados_cupos'];
                    }
                }
            }
        }

        $datos['ubicacion_dni'] = $ubicacion;
        $datos['pais_residencia'] = $datos['centro_exterior_pais'] ?? null;

        return $datos;
    } catch (Exception $e) {
        error_log('Error al obtener resumen de votante: ' . $e->getMessage());
        return null;
    }
}

function obtenerCentrosPorMunicipio($municipioId)
{
    if (!$municipioId) {
        return [];
    }

    try {
        return dbQuery(
            'SELECT id, nombre
             FROM centros_votacion
             WHERE municipio_id = :municipio
             ORDER BY nombre ASC',
            [':municipio' => (int) $municipioId]
        )->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function obtenerCentrosExteriorPorPais($pais)
{
    if ($pais === '') {
        return [];
    }

    try {
        return dbQuery('SELECT id, pais, estado, ciudad, sector_electoral, juntas, nombre, direccion
                        FROM centros_votacion_exterior
                        WHERE pais = :pais
                        ORDER BY estado ASC, ciudad ASC', [
            ':pais' => $pais,
        ])->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function obtenerPlanillasPresidenciales()
{
    return dbQuery('SELECT id, nombre, partido, descripcion, logo_url
                    FROM planillas
                    WHERE tipo = :tipo AND estado = :estado
                    ORDER BY partido ASC, nombre ASC', [
        ':tipo' => 'presidencial',
        ':estado' => 'habilitada',
    ])->fetchAll();
}

function obtenerPlanillasDiputadosPorDepartamento($departamentoId)
{
    if (!$departamentoId) {
        return [];
    }

    return dbQuery('SELECT id, nombre, partido, descripcion, logo_url
                    FROM planillas
                    WHERE tipo = :tipo
                      AND estado = :estado
                      AND (departamento_id = :departamento OR departamento_id IS NULL)
                    ORDER BY partido ASC, nombre ASC', [
        ':tipo' => 'diputacion',
        ':estado' => 'habilitada',
        ':departamento' => (int) $departamentoId,
    ])->fetchAll();
}

function obtenerPlanillasAlcaldiaPorMunicipio($municipioId)
{
    if (!$municipioId) {
        return [];
    }

    return dbQuery('SELECT id, nombre, partido, descripcion, logo_url
                    FROM planillas
                    WHERE tipo = :tipo
                      AND estado = :estado
                      AND (municipio_id = :municipio OR municipio_id IS NULL)
                    ORDER BY partido ASC, nombre ASC', [
        ':tipo' => 'alcaldia',
        ':estado' => 'habilitada',
        ':municipio' => (int) $municipioId,
    ])->fetchAll();
}

function obtenerCandidatosPorPlanilla($planillaId)
{
    return dbQuery('SELECT id, nombre, cargo, numero_candidato, foto_url
                    FROM candidatos
                    WHERE planilla_id = :planilla
                    ORDER BY numero_candidato ASC, nombre ASC', [
        ':planilla' => (int) $planillaId,
    ])->fetchAll();
}

function obtenerVotosPorUsuario($usuarioId)
{
    try {
        return dbQuery('SELECT v.id, v.planilla_id, v.candidato_id, v.registrado_en,
                               p.nombre AS planilla_nombre, p.partido, p.tipo,
                               c.nombre AS candidato_nombre, c.cargo
                        FROM votos v
                        INNER JOIN planillas p ON p.id = v.planilla_id
                        INNER JOIN candidatos c ON c.id = v.candidato_id
                        WHERE v.usuario_id = :usuario
                        ORDER BY v.registrado_en DESC', [
            ':usuario' => (int) $usuarioId,
        ])->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function registrarDenunciaActoIrregular($usuarioId, $tipoVotante, $titulo, $descripcion, $evidenciaUrl)
{
    return dbQuery('INSERT INTO denuncias_actos_irregulares (usuario_id, tipo_votante, titulo, descripcion, evidencia_url)
                    VALUES (:usuario_id, :tipo_votante, :titulo, :descripcion, :evidencia_url)', [
        ':usuario_id' => (int) $usuarioId,
        ':tipo_votante' => $tipoVotante,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':evidencia_url' => $evidenciaUrl,
    ]);
}

function obtenerDenunciasPorUsuario($usuarioId)
{
    try {
        return dbQuery('SELECT id, tipo_votante, titulo, descripcion, evidencia_url, estado, creada_en, actualizada_en
                        FROM denuncias_actos_irregulares
                        WHERE usuario_id = :usuario
                        ORDER BY creada_en DESC', [
            ':usuario' => (int) $usuarioId,
        ])->fetchAll();
    } catch (PDOException $e) {
        error_log('Error en obtenerDenunciasPorUsuario: ' . $e->getMessage());
        return [];
    }
}

function verificarSiYaVoto($usuarioId, $tipoEleccion)
{
    static $mapaTipos = [
        'presidencial' => ['presidencial'],
        'diputados' => ['diputacion', 'diputados'],
        'diputacion' => ['diputacion', 'diputados'],
        'alcalde' => ['alcaldia', 'alcalde'],
        'alcaldia' => ['alcaldia', 'alcalde'],
        'vicealcaldia' => ['vicealcaldia'],
    ];

    $tipoNormalizado = strtolower((string) $tipoEleccion);
    $tiposPlanilla = $mapaTipos[$tipoNormalizado] ?? [$tipoNormalizado];

    try {
        $placeholders = implode(', ', array_fill(0, count($tiposPlanilla), '?'));
        $sql = "SELECT COUNT(*)
                FROM votos v
                INNER JOIN planillas p ON p.id = v.planilla_id
                WHERE v.usuario_id = ?
                  AND p.tipo IN ($placeholders)";

        $params = array_merge([(int) $usuarioId], $tiposPlanilla);
        $resultado = dbQuery($sql, $params)->fetchColumn();

        return (int) $resultado > 0;
    } catch (PDOException $e) {
        error_log('Error en verificarSiYaVoto: ' . $e->getMessage());
        return false;
    }
}

 

function obtenerDiputadosPorDepartamento($departamentoId)
{
    try {
        $query = 'SELECT p.id as planilla_id, p.partido, p.nombre as planilla_nombre,
                         GROUP_CONCAT(c.nombre ORDER BY c.numero_candidato SEPARATOR "|||") as candidatos
                  FROM planillas p
                  LEFT JOIN candidatos c ON c.planilla_id = p.id
                  WHERE p.tipo = "diputacion" AND p.departamento_id = :departamento_id AND p.estado = "habilitada"
                  GROUP BY p.id, p.partido, p.nombre
                  ORDER BY p.partido';
        
        $planillas = dbQuery($query, [':departamento_id' => $departamentoId])->fetchAll();
        
        
        foreach ($planillas as &$planilla) {
            $planilla['candidatos'] = !empty($planilla['candidatos']) 
                ? explode('|||', $planilla['candidatos']) 
                : [];
        }
        
        return $planillas;
    } catch (PDOException $e) {
        return [];
    }
}

function obtenerImagenPlanillaDepartamento($departamentoNombre)
{
    $mapeoImagenes = [
        'Atlántida' => 'atlantida.PNG',
        'Colón' => 'colon.PNG',
        'Comayagua' => 'comayagua.PNG',
        'Copán' => 'copan.PNG',
        'Cortés' => 'cortes.PNG',
        'Choluteca' => 'choluteca.PNG',
        'El Paraíso' => 'paraiso.PNG',
        'Francisco Morazán' => 'FranciscoMorazan.PNG',
        'Gracias a Dios' => 'GraciasaDios.PNG',
        'Intibucá' => 'intibuca.PNG',
        'Islas de la Bahía' => 'islasdelabahia.PNG',
        'La Paz' => 'lapaz.PNG',
        'Lempira' => 'lempira.PNG',
        'Ocotepeque' => 'ocotepeque.PNG',
        'Olancho' => 'olancho.PNG',
        'Santa Bárbara' => 'santabarbara.PNG',
        'Valle' => 'valle.PNG',
        'Yoro' => 'yoro.PNG'
    ];

    if (isset($mapeoImagenes[$departamentoNombre])) {
        return $mapeoImagenes[$departamentoNombre];
    }

    $base = iconv('UTF-8', 'ASCII//TRANSLIT', $departamentoNombre);
    if ($base === false) {
        $base = $departamentoNombre;
    }

    $base = trim($base);
    $lower = strtolower($base);
    $upper = strtoupper($base);
    $camel = str_replace(' ', '', ucwords($lower));
    $noSpacesLower = preg_replace('/\s+|[^a-z0-9]/i', '', $lower);

    $rutaBase = __DIR__ . '/../img/';
    $candidatos = array_unique(array_filter([
        $camel ? $camel . '.PNG' : null,
        $camel ? $camel . '.png' : null,
        $noSpacesLower ? $noSpacesLower . '.PNG' : null,
        $noSpacesLower ? $noSpacesLower . '.png' : null,
        $lower ? $lower . '.PNG' : null,
        $lower ? $lower . '.png' : null,
        $upper ? $upper . '.PNG' : null,
        $upper ? $upper . '.png' : null,
    ]));

    foreach ($candidatos as $archivo) {
        if ($archivo && file_exists($rutaBase . $archivo)) {
            return $archivo;
        }
    }

    return 'cne_logo.png';
}

function obtenerAlcaldesPorMunicipio($municipioId)
{
    try {
        $query = 'SELECT p.id as planilla_id, p.partido, p.nombre as planilla_nombre,
                         GROUP_CONCAT(CONCAT(c.nombre, "|", c.cargo) ORDER BY c.numero_candidato SEPARATOR "|||") as candidatos
                  FROM planillas p
                  LEFT JOIN candidatos c ON c.planilla_id = p.id
                  WHERE p.tipo = "alcaldia" AND p.municipio_id = :municipio_id AND p.estado = "habilitada"
                  GROUP BY p.id, p.partido, p.nombre
                  ORDER BY p.partido';
        
        $planillas = dbQuery($query, [':municipio_id' => $municipioId])->fetchAll();
        
        foreach ($planillas as &$planilla) {
            $candidatosRaw = !empty($planilla['candidatos']) 
                ? explode('|||', $planilla['candidatos']) 
                : [];
            
            $planilla['alcalde'] = '';
            $planilla['vicealcalde'] = '';
            
            foreach ($candidatosRaw as $candidatoData) {
                $partes = explode('|', $candidatoData);
                if (count($partes) === 2) {
                    $nombre = $partes[0];
                    $cargo = $partes[1];
                    
                    if ($cargo === 'Alcalde') {
                        $planilla['alcalde'] = $nombre;
                    } elseif ($cargo === 'Vicealcalde') {
                        $planilla['vicealcalde'] = $nombre;
                    }
                }
            }
        }
        
        return $planillas;
    } catch (PDOException $e) {
        error_log("Error al obtener alcaldes: " . $e->getMessage());
        return [];
    }
}

function obtenerNombreMunicipioPorId($municipioId)
{
    if (!$municipioId) {
        return null;
    }

    try {
        $nombre = dbQuery(
            'SELECT nombre FROM municipios WHERE id = :id LIMIT 1',
            [':id' => (int) $municipioId]
        )->fetchColumn();

        return $nombre !== false ? $nombre : null;
    } catch (Exception $e) {
        error_log('Error al resolver municipio: ' . $e->getMessage());
        return null;
    }
}

function obtenerImagenPlanillaMunicipio($municipioNombre)
{
    if (empty($municipioNombre)) {
        return 'cne_logo.png';
    }

    $mapeoImagenes = [
        'Juticalpa' => 'juticalpa.PNG',
        'San Pedro Sula' => 'sanpedrosula.PNG',
        'Choloma' => 'choloma.PNG',
        'El Porvenir' => 'elporvenir.PNG'
    ];

    if (isset($mapeoImagenes[$municipioNombre])) {
        return $mapeoImagenes[$municipioNombre];
    }

    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $municipioNombre);
    if ($slug === false) {
        $slug = $municipioNombre;
    }

    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]/', '', $slug);

    if (!empty($slug)) {
        $rutaBase = __DIR__ . '/../img/';
        $archivoMayus = $slug . '.PNG';
        $archivoMinus = $slug . '.png';

        if (file_exists($rutaBase . $archivoMayus)) {
            return $archivoMayus;
        }

        if (file_exists($rutaBase . $archivoMinus)) {
            return $archivoMinus;
        }
    }

    return 'cne_logo.png';
}

function obtenerMunicipiosConAlcaldes()
{
    try {
        $query = "SELECT DISTINCT m.id, m.nombre
                  FROM municipios m
                  INNER JOIN planillas p ON p.municipio_id = m.id
                  WHERE p.tipo = 'alcaldia' AND p.estado = 'habilitada'
                  ORDER BY m.nombre";
        return dbQuery($query)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener municipios: " . $e->getMessage());
        return [];
    }
}



function obtenerTotalVotantes()
{
    try {
        $query = "SELECT COUNT(*) FROM usuarios WHERE rol = 'votante'";
        return (int) dbQuery($query)->fetchColumn();
    } catch (Exception $e) {
        error_log("Error al obtener total de votantes: " . $e->getMessage());
        return 0;
    }
}

function obtenerTotalVotos()
{
    try {
        $query = "SELECT COUNT(*) FROM votos";
        return (int) dbQuery($query)->fetchColumn();
    } catch (Exception $e) {
        error_log("Error al obtener total de votos: " . $e->getMessage());
        return 0;
    }
}

function obtenerTotalPlanillas()
{
    try {
        $query = "SELECT COUNT(*) FROM planillas WHERE estado = 'habilitada'";
        return (int) dbQuery($query)->fetchColumn();
    } catch (Exception $e) {
        error_log("Error al obtener total de planillas: " . $e->getMessage());
        return 0;
    }
}

function obtenerTotalDenuncias()
{
    try {
        $query = "SELECT COUNT(*) FROM denuncias_actos_irregulares";
        return (int) dbQuery($query)->fetchColumn();
    } catch (Exception $e) {
        error_log("Error al obtener total de denuncias: " . $e->getMessage());
        return 0;
    }
}

function crearPlanillaCompleta($datos, $candidatos)
{
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO planillas (tipo, partido, nombre, departamento_id, municipio_id, estado)
             VALUES (:tipo, :partido, :nombre, :departamento_id, :municipio_id, :estado)'
        );

        $stmt->execute([
            ':tipo' => $datos['tipo'],
            ':partido' => $datos['partido'],
            ':nombre' => $datos['nombre'],
            ':departamento_id' => $datos['departamento_id'] ?: null,
            ':municipio_id' => $datos['municipio_id'] ?: null,
            ':estado' => $datos['estado'] ?? 'habilitada',
        ]);

        $planillaId = (int) $pdo->lastInsertId();

        if (!empty($candidatos)) {
            $stmtCandidato = $pdo->prepare(
                'INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
                 VALUES (:planilla_id, :nombre, :cargo, :numero_candidato)'
            );

            foreach ($candidatos as $candidato) {
                $cargo = isset($candidato['cargo']) ? trim((string) $candidato['cargo']) : '';
                $numero = $candidato['numero_candidato'] !== null
                    ? (int) $candidato['numero_candidato']
                    : null;

                $stmtCandidato->execute([
                    ':planilla_id' => $planillaId,
                    ':nombre' => $candidato['nombre'],
                    ':cargo' => $cargo,
                    ':numero_candidato' => $numero,
                ]);
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error al crear planilla completa: ' . $e->getMessage());
        return false;
    }
}

function obtenerPlanillaConCandidatos($planillaId)
{
    try {
        $planilla = dbQuery(
            'SELECT p.*, d.nombre AS departamento_nombre, m.nombre AS municipio_nombre
             FROM planillas p
             LEFT JOIN departamentos d ON d.id = p.departamento_id
             LEFT JOIN municipios m ON m.id = p.municipio_id
             WHERE p.id = :id
             LIMIT 1',
            [':id' => (int) $planillaId]
        )->fetch();

        if (!$planilla) {
            return null;
        }

        $candidatos = dbQuery(
            'SELECT id, nombre, cargo, numero_candidato
             FROM candidatos
             WHERE planilla_id = :planilla_id
             ORDER BY numero_candidato IS NULL, numero_candidato ASC, id ASC',
            [':planilla_id' => (int) $planillaId]
        )->fetchAll();

        $planilla['candidatos'] = $candidatos;

        return $planilla;
    } catch (Exception $e) {
        error_log('Error al obtener planilla con candidatos: ' . $e->getMessage());
        return null;
    }
}

function actualizarPlanillaCompleta($planillaId, $datos, $candidatos)
{
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'UPDATE planillas
             SET tipo = :tipo,
                 partido = :partido,
                 nombre = :nombre,
                 departamento_id = :departamento_id,
                 municipio_id = :municipio_id,
                 estado = :estado
             WHERE id = :id'
        );

        $stmt->execute([
            ':tipo' => $datos['tipo'],
            ':partido' => $datos['partido'],
            ':nombre' => $datos['nombre'],
            ':departamento_id' => $datos['departamento_id'] ?: null,
            ':municipio_id' => $datos['municipio_id'] ?: null,
            ':estado' => $datos['estado'] ?? 'habilitada',
            ':id' => (int) $planillaId,
        ]);

        $stmtIds = $pdo->prepare('SELECT id FROM candidatos WHERE planilla_id = :planilla_id');
        $stmtIds->execute([':planilla_id' => (int) $planillaId]);
        $idsExistentes = array_map('intval', $stmtIds->fetchAll(PDO::FETCH_COLUMN));

        $idsProcesados = [];

        $stmtActualizar = $pdo->prepare(
            'UPDATE candidatos
             SET nombre = :nombre,
                 cargo = :cargo,
                 numero_candidato = :numero_candidato
             WHERE id = :id AND planilla_id = :planilla_id'
        );

        $stmtInsertar = $pdo->prepare(
            'INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
             VALUES (:planilla_id, :nombre, :cargo, :numero_candidato)'
        );

        foreach ($candidatos as $candidato) {
            $candidatoId = isset($candidato['id']) ? (int) $candidato['id'] : 0;
            $numero = $candidato['numero_candidato'];
            $numero = ($numero !== null && $numero !== '') ? (int) $numero : null;
            $cargo = isset($candidato['cargo']) ? trim((string) $candidato['cargo']) : '';

            if ($candidatoId && in_array($candidatoId, $idsExistentes, true)) {
                $stmtActualizar->execute([
                    ':nombre' => $candidato['nombre'],
                    ':cargo' => $cargo,
                    ':numero_candidato' => $numero,
                    ':id' => $candidatoId,
                    ':planilla_id' => (int) $planillaId,
                ]);

                $idsProcesados[] = $candidatoId;
            } else {
                $stmtInsertar->execute([
                    ':planilla_id' => (int) $planillaId,
                    ':nombre' => $candidato['nombre'],
                    ':cargo' => $cargo,
                    ':numero_candidato' => $numero,
                ]);

                $idsProcesados[] = (int) $pdo->lastInsertId();
            }
        }

        $idsEliminar = array_diff($idsExistentes, $idsProcesados);

        if (!empty($idsEliminar)) {
            $placeholders = implode(',', array_fill(0, count($idsEliminar), '?'));
            $params = array_merge([(int) $planillaId], array_values($idsEliminar));

            $pdo->prepare("DELETE FROM candidatos WHERE planilla_id = ? AND id IN ($placeholders)")
                ->execute($params);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error al actualizar planilla completa: ' . $e->getMessage());
        return false;
    }
}

function obtenerTotalUsuarios() {
    return (int) dbQuery("SELECT COUNT(*) FROM usuarios")->fetchColumn();
}

function obtenerUsuariosActivos() {
    try {
        $query = "SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'";
        return (int) dbQuery($query)->fetchColumn();
    } catch (Exception $e) {
        error_log("Error al obtener usuarios activos: " . $e->getMessage());
        return 0;
    }
}

function obtenerEstadisticasVotacion() {
    try {
        $stats = [];
        $stats['total_votos'] = (int) dbQuery("SELECT COUNT(*) FROM votos")->fetchColumn();

        $stats['centros_activos'] = (int) dbQuery("SELECT COUNT(DISTINCT cv.id)
                                                    FROM votos v
                                                    INNER JOIN usuarios u ON u.id = v.usuario_id
                                                    LEFT JOIN centros_votacion cv ON cv.id = u.centro_votacion_id
                                                    WHERE cv.id IS NOT NULL")->fetchColumn();

        $totalVotantesHabilitados = (int) dbQuery("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante'")->fetchColumn();
        $stats['participacion'] = $totalVotantesHabilitados > 0
            ? round(($stats['total_votos'] / $totalVotantesHabilitados) * 100, 2)
            : 0;

        $stats['incidencias'] = (int) dbQuery("SELECT COUNT(*) FROM denuncias_actos_irregulares")->fetchColumn();

        return $stats;
    } catch (Exception $e) {
        error_log("Error al obtener estadísticas de votación: " . $e->getMessage());
        return [
            'total_votos' => 0,
            'centros_activos' => 0,
            'participacion' => 0,
            'incidencias' => 0,
        ];
    }
}

function obtenerCentrosVotacion() {
    try {
        $query = "SELECT cv.nombre,
                         CONCAT_WS(', ', m.nombre, d.nombre) AS ubicacion,
                         cv.estado
                  FROM centros_votacion cv
                  LEFT JOIN municipios m ON m.id = cv.municipio_id
                  LEFT JOIN departamentos d ON d.id = m.departamento_id
                  ORDER BY cv.nombre ASC";

        $centros = dbQuery($query)->fetchAll();

        foreach ($centros as &$centro) {
            $centro['activo'] = ($centro['estado'] ?? 'activo') === 'activo';
        }

        return $centros;
    } catch (Exception $e) {
        error_log("Error al obtener centros de votación: " . $e->getMessage());
        return [];
    }
}

function obtenerUltimasVotaciones($limite) {
    try {
        $sql = "SELECT u.nombre AS votante_nombre,
                       u.dni,
                       cv.nombre AS centro_votacion,
                       v.registrado_en AS fecha_voto
                FROM votos v
                INNER JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN centros_votacion cv ON cv.id = u.centro_votacion_id
                ORDER BY v.registrado_en DESC
                LIMIT :limite";

        $stmt = db()->prepare($sql);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener últimas votaciones: " . $e->getMessage());
        return [];
    }
}

function obtenerConfiguracionVotacion() {
    return [
        'estado' => 'activo',
        'fecha_inicio' => date('Y-m-d'),
        'fecha_fin' => date('Y-m-d'),
        'hora_inicio' => '06:00',
        'hora_fin' => '18:00',
        'permitir_voto_temprano' => 0
    ];
}

function iniciarProcesoVotacion() {
    return true;
}

function pausarProcesoVotacion() {
    return true;
}

function finalizarProcesoVotacion() {
    return true;
}

function actualizarConfiguracionVotacion($config) {
    return true;
}

function obtenerVotosPorPartido() {
    return dbQuery("SELECT p.partido, COUNT(*) as total_votos FROM votos v JOIN planillas p ON v.planilla_id = p.id GROUP BY p.partido ORDER BY total_votos DESC")->fetchAll();
}

function obtenerVotosPorDepartamento() {
    return dbQuery("SELECT d.nombre as departamento, COUNT(*) as total_votos FROM votos v JOIN usuarios u ON v.usuario_id = u.id LEFT JOIN departamentos d ON u.departamento_id = d.id GROUP BY d.nombre ORDER BY total_votos DESC")->fetchAll();
}

function obtenerVotosEnTiempo() {
    return dbQuery("SELECT DATE_FORMAT(registrado_en, '%Y-%m-%d %H:00:00') as fecha_hora, COUNT(*) as votos_acumulados FROM votos GROUP BY DATE_FORMAT(registrado_en, '%Y-%m-%d %H:00:00') ORDER BY fecha_hora")->fetchAll();
}

function obtenerParticipacionPorEdad() {
    return [];
}

function obtenerParticipacionGeneral() {
    $totalVotos = (int) dbQuery("SELECT COUNT(*) FROM votos")->fetchColumn();
    $totalUsuarios = (int) dbQuery("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante'")->fetchColumn();
    return $totalUsuarios > 0 ? ($totalVotos / $totalUsuarios) * 100 : 0;
}

function obtenerTendenciaActual() {
    $lider = dbQuery("SELECT p.partido FROM votos v JOIN planillas p ON v.planilla_id = p.id GROUP BY p.partido ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn();
    return ['lider' => $lider ?: 'Sin datos'];
}

function asegurarTablaDenunciasResponsables()
{
    static $verificada = false;

    if ($verificada) {
        return;
    }

    try {
        $sql = "CREATE TABLE IF NOT EXISTS denuncias_responsables (
                    denuncia_id INT UNSIGNED PRIMARY KEY,
                    responsable_id INT UNSIGNED NULL,
                    comentario TEXT NULL,
                    asignada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    actualizada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
                    CONSTRAINT fk_denuncia_responsable FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        db()->exec($sql);
        $verificada = true;
    } catch (Exception $e) {
        error_log('No se pudo verificar la tabla denuncias_responsables: ' . $e->getMessage());
    }
}

function obtenerDenunciasAdmin($filtros = [])
{
    try {
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = 'd.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha'])) {
            $where[] = 'DATE(d.creada_en) = :fecha';
            $params[':fecha'] = $filtros['fecha'];
        }

        if (!empty($filtros['busqueda'])) {
            $where[] = '(
                d.titulo LIKE :busqueda OR
                d.descripcion LIKE :busqueda OR
                u.nombre LIKE :busqueda OR
                u.dni LIKE :busqueda OR
                d.id LIKE :busqueda
            )';
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        $query = "SELECT
                        d.id,
                        d.usuario_id,
                        d.tipo_votante,
                        d.titulo,
                        d.descripcion,
                        d.evidencia_url,
                        d.estado,
                        d.creada_en AS fecha_reporte,
                        d.actualizada_en,
                        u.nombre AS reportado_por,
                        u.dni,
                        u.telefono,
                        u.email
                  FROM denuncias_actos_irregulares d
                  INNER JOIN usuarios u ON u.id = d.usuario_id
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY d.creada_en DESC";

        return dbQuery($query, $params)->fetchAll();
    } catch (Exception $e) {
        error_log('Error al obtener denuncias admin: ' . $e->getMessage());
        return [];
    }
}

function cambiarEstadoDenuncia($id, $estado, $usuarioId, $comentario)
{
    $estadosValidos = ['recibida', 'en_revision', 'resuelta', 'rechazada'];

    if (!in_array($estado, $estadosValidos, true)) {
        return false;
    }

    try {
        $campos = ['estado = :estado'];
        $params = [
            ':estado' => $estado,
            ':id' => $id,
        ];

        if (!empty($comentario)) {
            $campos[] = 'respuesta_autoridad = :respuesta';
            $campos[] = 'fecha_respuesta = NOW()';
            $params[':respuesta'] = $comentario;
        }

        $sql = 'UPDATE denuncias SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $stmt = db()->prepare($sql);

        return $stmt->execute($params);
    } catch (Exception $e) {
        error_log('Error al cambiar estado de denuncia: ' . $e->getMessage());
        return false;
    }
}

function asignarResponsableDenuncia($id, $responsableId)
{
    try {
        asegurarTablaDenunciasResponsables();

        if ($responsableId <= 0) {
            $sql = 'DELETE FROM denuncias_responsables WHERE denuncia_id = :denuncia_id';
            return dbQuery($sql, [':denuncia_id' => $id])->rowCount() >= 0;
        }

        $sql = "INSERT INTO denuncias_responsables (denuncia_id, responsable_id, asignada_en, actualizada_en)
                VALUES (:denuncia_id, :responsable_id, NOW(), NOW())
                ON DUPLICATE KEY UPDATE responsable_id = VALUES(responsable_id), actualizada_en = NOW()";

        return dbQuery($sql, [
            ':denuncia_id' => $id,
            ':responsable_id' => $responsableId,
        ])->rowCount() >= 0;
    } catch (Exception $e) {
        error_log('Error al asignar responsable a denuncia: ' . $e->getMessage());
        return false;
    }
}

function obtenerEstadoProcesoVotacion() {
    return [
        'estado' => 'activo',
        'centros_abiertos' => 25,
        'centros_cerrados' => 5,
        'total_centros' => 30
    ];
}

function obtenerInformacionCentrosParaCierre() {
    try {
        $query = "SELECT cv.id,
                         cv.nombre,
                         cv.codigo,
                         cv.direccion,
                         cv.capacidad,
                         cv.estado AS estado_centro,
                         m.nombre AS municipio_nombre,
                         d.nombre AS departamento_nombre,
                         COUNT(v.id) AS total_votos,
                         MAX(v.registrado_en) AS ultimo_voto
                  FROM centros_votacion cv
                  INNER JOIN municipios m ON m.id = cv.municipio_id
                  INNER JOIN departamentos d ON d.id = m.departamento_id
                  LEFT JOIN usuarios u ON u.centro_votacion_id = cv.id
                  LEFT JOIN votos v ON v.usuario_id = u.id
                  GROUP BY cv.id, cv.nombre, cv.codigo, cv.direccion, cv.capacidad, cv.estado,
                           m.nombre, d.nombre
                  ORDER BY cv.nombre ASC";

        $centros = dbQuery($query)->fetchAll();

        foreach ($centros as &$centro) {
            $capacidad = (int) ($centro['capacidad'] ?? 0);
            $totalVotos = (int) ($centro['total_votos'] ?? 0);

            $ubicacionPartes = array_filter([
                $centro['municipio_nombre'] ?? null,
                $centro['departamento_nombre'] ?? null,
            ]);

            $centro['ubicacion'] = $ubicacionPartes ? implode(', ', $ubicacionPartes) : 'Sin ubicación registrada';
            $centro['participacion'] = $capacidad > 0 ? round(($totalVotos / $capacidad) * 100, 2) : 0;
            $centro['ultimo_voto'] = $centro['ultimo_voto'] ?: null;
            $centro['cerrado'] = ($centro['estado_centro'] ?? 'activo') !== 'activo';
            $centro['hora_cierre'] = $centro['cerrado'] ? $centro['ultimo_voto'] : null;
            $centro['total_votos'] = $totalVotos;
        }

        return $centros;
    } catch (Exception $e) {
        error_log('Error al obtener centros para cierre: ' . $e->getMessage());
        return [];
    }
}

function obtenerResumenCierreUrnas() {
    try {
        $centros = obtenerInformacionCentrosParaCierre();
        $totalCentros = count($centros);
        $centrosCerrados = 0;
        $totalVotos = 0;
        $sumaParticipacion = 0;
        $horaInicio = null;
        $horaUltimoCierre = null;

        foreach ($centros as $centro) {
            $totalVotos += (int) ($centro['total_votos'] ?? 0);
            $sumaParticipacion += (float) ($centro['participacion'] ?? 0);

            if (!empty($centro['cerrado'])) {
                $centrosCerrados++;

                if (!empty($centro['hora_cierre'])) {
                    $horaCierre = $centro['hora_cierre'];

                    if ($horaInicio === null || $horaCierre < $horaInicio) {
                        $horaInicio = $horaCierre;
                    }

                    if ($horaUltimoCierre === null || $horaCierre > $horaUltimoCierre) {
                        $horaUltimoCierre = $horaCierre;
                    }
                }
            }
        }

        $participacionPromedio = $totalCentros > 0 ? round($sumaParticipacion / $totalCentros, 2) : 0.0;

        $votosTotalesFinales = 0;
        try {
            $votosTotalesFinales = (int) dbQuery('SELECT COUNT(*) FROM votos')->fetchColumn();
        } catch (Exception $ex) {
            error_log('Error al contar votos totales: ' . $ex->getMessage());
            $votosTotalesFinales = $totalVotos;
        }

        return [
            'total_centros' => $totalCentros,
            'centros_cerrados' => $centrosCerrados,
            'centros_abiertos' => max($totalCentros - $centrosCerrados, 0),
            'votos_totales' => $votosTotalesFinales,
            'participacion_promedio' => $participacionPromedio,
            'hora_inicio_cierre' => $horaInicio,
            'ultimo_centro_cerrado' => $horaUltimoCierre,
            'total_votos_finales' => $votosTotalesFinales,
            'actas_generadas' => $centrosCerrados
        ];
    } catch (Exception $e) {
        error_log('Error al obtener resumen de cierre: ' . $e->getMessage());
        return [
            'total_centros' => 0,
            'centros_cerrados' => 0,
            'centros_abiertos' => 0,
            'votos_totales' => 0,
            'participacion_promedio' => 0,
            'hora_inicio_cierre' => null,
            'ultimo_centro_cerrado' => null,
            'total_votos_finales' => 0,
            'actas_generadas' => 0
        ];
    }
}



function iniciarCierreGeneralUrnas() {
    return true;
}

function cerrarCentroVotacion($centroId, $usuarioId, $observaciones) {
    return true;
}

function generarActaCierre($centroId) {
    return true;
}

function finalizarProcesoElectoral($usuarioId) {
    return true;
}

function obtenerVotosRecientes($limite = 10)
{
    try {
        $query = "SELECT v.registrado_en, u.nombre as votante_nombre, c.nombre as candidato_nombre, p.partido
                  FROM votos v
                  JOIN usuarios u ON v.usuario_id = u.id
                  JOIN candidatos c ON v.candidato_id = c.id
                  JOIN planillas p ON v.planilla_id = p.id
                  ORDER BY v.registrado_en DESC
                  LIMIT :limite";
        
        $stmt = db()->prepare($query);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener votos recientes: " . $e->getMessage());
        return [];
    }
}

function obtenerDenunciasRecientes($limite = 5)
{
    try {
        $query = "SELECT 
                        d.id,
                        d.tipo_votante,
                        d.titulo,
                        d.descripcion,
                        d.estado,
                        d.creada_en
                  FROM denuncias_actos_irregulares d
                  ORDER BY d.creada_en DESC
                  LIMIT :limite";
        $stmt = dbQuery($query, [':limite' => $limite]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener denuncias recientes: " . $e->getMessage());
        return [];
    }
}

function obtenerTendenciasPorPartido()
{
    try {
        $query = "SELECT p.partido, COUNT(v.id) as total_votos
                  FROM planillas p
                  LEFT JOIN votos v ON p.id = v.planilla_id
                  WHERE p.tipo = 'presidencial'
                  GROUP BY p.partido
                  ORDER BY total_votos DESC";
        
        return dbQuery($query)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener tendencias por partido: " . $e->getMessage());
        return [];
    }
}

function obtenerTodasLasPlanillas($filtros = [])
{
    try {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $where[] = 'p.tipo = :tipo';
            $params[':tipo'] = $filtros['tipo'];
        }
        
        if (!empty($filtros['partido'])) {
            $where[] = 'p.partido LIKE :partido';
            $params[':partido'] = '%' . $filtros['partido'] . '%';
        }
        
        if (!empty($filtros['estado'])) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['departamento_id'])) {
            $where[] = 'p.departamento_id = :departamento_id';
            $params[':departamento_id'] = $filtros['departamento_id'];
        }
        
        $query = "SELECT p.*, d.nombre as departamento_nombre, m.nombre as municipio_nombre,
                         COUNT(c.id) as total_candidatos
                  FROM planillas p
                  LEFT JOIN departamentos d ON p.departamento_id = d.id
                  LEFT JOIN municipios m ON p.municipio_id = m.id
                  LEFT JOIN candidatos c ON p.id = c.planilla_id
                  WHERE " . implode(' AND ', $where) . "
                  GROUP BY p.id
                  ORDER BY p.creada_en DESC";
        
        return dbQuery($query, $params)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener planillas: " . $e->getMessage());
        return [];
    }
}

function obtenerTodosLosUsuarios($filtros = [])
{
    try {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filtros['rol'])) {
            $where[] = 'u.rol = :rol';
            $params[':rol'] = $filtros['rol'];
        }
        
        if (!empty($filtros['estado'])) {
            $where[] = 'u.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['departamento_id'])) {
            $where[] = 'u.departamento_id = :departamento_id';
            $params[':departamento_id'] = $filtros['departamento_id'];
        }
        
        if (!empty($filtros['buscar'])) {
            $where[] = '(u.nombre LIKE :buscar OR u.dni LIKE :buscar OR u.email LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }
        
        $query = "SELECT u.*, d.nombre as departamento_nombre, m.nombre as municipio_nombre,
                         v.habilitado, v.fecha_verificacion
                  FROM usuarios u
                  LEFT JOIN departamentos d ON u.departamento_id = d.id
                  LEFT JOIN municipios m ON u.municipio_id = m.id
                  LEFT JOIN votantes v ON u.id = v.usuario_id
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY u.creado_en DESC";
        
        return dbQuery($query, $params)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener usuarios: " . $e->getMessage());
        return [];
    }
}

function obtenerUsuarioDetallado($usuarioId)
{
    try {
        $sql = 'SELECT u.id,
                       u.dni,
                       u.nombre,
                       u.email,
                       u.telefono,
                       u.rol,
                       u.tipo_votante,
                       u.estado,
                       u.departamento_id,
                       u.municipio_id,
                       u.centro_votacion_id,
                       u.centro_votacion_exterior_id,
                       u.tipo_votacion_id,
                       u.creado_en,
                       u.actualizado_en,
                       d.nombre AS departamento_nombre,
                       m.nombre AS municipio_nombre,
                       cv.nombre AS centro_votacion_nombre,
                       cv.codigo AS centro_votacion_codigo,
                       cv.direccion AS centro_votacion_direccion,
                       ce.nombre AS centro_exterior_nombre,
                       ce.ciudad AS centro_exterior_ciudad,
                       ce.pais AS centro_exterior_pais,
                       ce.estado AS centro_exterior_estado,
                       ce.sector_electoral AS centro_exterior_sector
                FROM usuarios u
                LEFT JOIN departamentos d ON d.id = u.departamento_id
                LEFT JOIN municipios m ON m.id = u.municipio_id
                LEFT JOIN centros_votacion cv ON cv.id = u.centro_votacion_id
                LEFT JOIN centros_votacion_exterior ce ON ce.id = u.centro_votacion_exterior_id
                WHERE u.id = :id
                LIMIT 1';

        $stmt = db()->prepare($sql);
        $stmt->execute([':id' => (int) $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null;
        }

        $perfilVotante = dbQuery(
            'SELECT fecha_nacimiento,
                    genero,
                    direccion,
                    municipio_emision,
                    habilitado,
                    fecha_verificacion
             FROM votantes
             WHERE usuario_id = :usuario_id
             LIMIT 1',
            [':usuario_id' => (int) $usuarioId]
        )->fetch(PDO::FETCH_ASSOC);

        $usuario['votante'] = $perfilVotante ?: null;

        return $usuario;
    } catch (Exception $e) {
        error_log('Error al obtener usuario detallado: ' . $e->getMessage());
        return null;
    }
}

function actualizarUsuarioAdmin($usuarioId, $datos)
{
    $usuarioId = (int) $usuarioId;

    if ($usuarioId <= 0) {
        return false;
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $camposPermitidos = [
            'dni',
            'nombre',
            'email',
            'telefono',
            'rol',
            'estado',
            'tipo_votante',
            'departamento_id',
            'municipio_id',
            'centro_votacion_id',
            'centro_votacion_exterior_id',
            'tipo_votacion_id',
        ];

        $campos = [];
        $params = [':id' => $usuarioId];

        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $valor = $datos[$campo];

                if ($campo === 'rol' && !in_array($valor, ['votante', 'administrador', 'observador'], true)) {
                    continue;
                }

                if ($campo === 'estado' && !in_array($valor, ['activo', 'suspendido'], true)) {
                    continue;
                }

                if ($campo === 'tipo_votante' && !in_array($valor, ['nacional', 'internacional'], true)) {
                    continue;
                }

                if (in_array($campo, ['departamento_id', 'municipio_id', 'centro_votacion_id', 'centro_votacion_exterior_id', 'tipo_votacion_id'], true)) {
                    $valor = $valor !== null && $valor !== '' ? (int) $valor : null;
                }

                $campos[] = "$campo = :$campo";
                $params[":" . $campo] = $valor !== '' ? $valor : null;
            }
        }

        if (!empty($campos)) {
            $campos[] = 'actualizado_en = NOW()';
            $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        if (!empty($datos['votante']) && is_array($datos['votante'])) {
            $votanteDatos = $datos['votante'];
            $camposVotantePermitidos = [
                'fecha_nacimiento',
                'genero',
                'direccion',
                'municipio_emision',
                'habilitado',
            ];

            $camposVotante = [];
            $paramsVotante = [':usuario_id' => $usuarioId];

            foreach ($camposVotantePermitidos as $campo) {
                if (array_key_exists($campo, $votanteDatos)) {
                    $valor = $votanteDatos[$campo];

                    if ($campo === 'genero' && !in_array($valor, ['M', 'F', 'X', null, ''], true)) {
                        continue;
                    }

                    if ($campo === 'habilitado') {
                        $valor = $valor ? 1 : 0;
                    }

                    $camposVotante[] = "$campo = :$campo";
                    $paramsVotante[":" . $campo] = $valor !== '' ? $valor : null;
                }
            }

            if (!empty($camposVotante)) {
                $existe = dbQuery(
                    'SELECT 1 FROM votantes WHERE usuario_id = :usuario_id LIMIT 1',
                    [':usuario_id' => $usuarioId]
                )->fetchColumn();

                if ($existe) {
                    $sqlVotante = 'UPDATE votantes SET ' . implode(', ', $camposVotante) . ', fecha_verificacion = NOW() WHERE usuario_id = :usuario_id';
                    $stmtVotante = $pdo->prepare($sqlVotante);
                    $stmtVotante->execute($paramsVotante);
                } else {
                    $columnas = array_merge(['usuario_id'], array_map(static function ($campo) {
                        return $campo;
                    }, array_keys($paramsVotante)));

                    $placeholders = array_map(static function ($columna) {
                        return ':' . $columna;
                    }, array_keys($paramsVotante));

                    $sqlInsert = 'INSERT INTO votantes (usuario_id, ' . implode(', ', array_keys($votanteDatos)) . ')
                                  VALUES (:usuario_id, ' . implode(', ', array_map(static function ($campo) {
                                      return ':' . $campo;
                                  }, array_keys($votanteDatos))) . ')';

                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $paramsInsert = [':usuario_id' => $usuarioId];

                    foreach ($votanteDatos as $campo => $valor) {
                        if ($campo === 'habilitado') {
                            $valor = $valor ? 1 : 0;
                        }
                        $paramsInsert[":" . $campo] = $valor !== '' ? $valor : null;
                    }

                    $stmtInsert->execute($paramsInsert);
                }
            }
        }

        $pdo->commit();

        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error al actualizar usuario: ' . $e->getMessage());
        return false;
    }
}

function obtenerComprobantesVotacion($filtros = [])
{
    try {
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['tipo'])) {
            $where[] = 'p.tipo = :tipo_planilla';
            $params[':tipo_planilla'] = $filtros['tipo'];
        }

        if (!empty($filtros['fecha'])) {
            $where[] = 'DATE(v.registrado_en) = :fecha_voto';
            $params[':fecha_voto'] = $filtros['fecha'];
        }

        if (!empty($filtros['centro_votacion'])) {
            if (ctype_digit((string) $filtros['centro_votacion'])) {
                $where[] = 'cv.id = :centro_id';
                $params[':centro_id'] = (int) $filtros['centro_votacion'];
            } else {
                $where[] = 'cv.nombre LIKE :centro_nombre';
                $params[':centro_nombre'] = '%' . $filtros['centro_votacion'] . '%';
            }
        }

        if (!empty($filtros['busqueda'])) {
            $where[] = '(v.hash_verificacion LIKE :busqueda OR u.dni LIKE :busqueda OR u.nombre LIKE :busqueda)';
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        $query = "SELECT 
                        v.id,
                        v.registrado_en,
                        v.hash_verificacion,
                        v.planilla_id,
                        v.candidato_id,
                        u.nombre AS votante_nombre,
                        u.dni,
                        u.email,
                        p.tipo AS tipo_planilla,
                        p.partido,
                        cv.id AS centro_id,
                        cv.nombre AS centro_nombre,
                        cv.codigo AS centro_codigo,
                        CONCAT_WS(', ', m.nombre, d.nombre) AS centro_ubicacion
                  FROM votos v
                  INNER JOIN usuarios u ON u.id = v.usuario_id
                  LEFT JOIN planillas p ON p.id = v.planilla_id
                  LEFT JOIN centros_votacion cv ON cv.id = u.centro_votacion_id
                  LEFT JOIN municipios m ON m.id = cv.municipio_id
                  LEFT JOIN departamentos d ON d.id = m.departamento_id
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY v.registrado_en DESC";

        $registros = dbQuery($query, $params)->fetchAll();

        foreach ($registros as &$registro) {
            $registro['codigo_comprobante'] = $registro['hash_verificacion'] ?: sprintf('V-%06d', (int) $registro['id']);
            $registro['tipo_voto'] = $registro['tipo_planilla'] ?: 'desconocido';
            $registro['fecha_voto'] = $registro['registrado_en'];
            $registro['centro_votacion'] = $registro['centro_nombre'] ?: 'Sin centro asignado';
            $registro['centro_ubicacion'] = $registro['centro_ubicacion'] ?: '';
            $registro['estado_comprobante'] = $registro['hash_verificacion'] ? 'valido' : 'sin_codigo';
        }

        return $registros;
    } catch (Exception $e) {
        error_log('Error al obtener comprobantes: ' . $e->getMessage());
        return [];
    }
}

function crearPlanilla($datos)
{
    try {
        $pdo = db();
        $pdo->beginTransaction();
        
        $query = "INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, logo_url, estado)
                  VALUES (:tipo, :departamento_id, :municipio_id, :nombre, :partido, :descripcion, :logo_url, :estado)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':tipo' => $datos['tipo'],
            ':departamento_id' => $datos['departamento_id'] ?: null,
            ':municipio_id' => $datos['municipio_id'] ?: null,
            ':nombre' => $datos['nombre'],
            ':partido' => $datos['partido'],
            ':descripcion' => $datos['descripcion'] ?: null,
            ':logo_url' => $datos['logo_url'] ?: null,
            ':estado' => $datos['estado'] ?? 'habilitada'
        ]);
        
        $planillaId = $pdo->lastInsertId();
        
        
        if (!empty($datos['candidatos'])) {
            $queryCandidato = "INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
                               VALUES (:planilla_id, :nombre, :cargo, :numero_candidato)";
            $stmtCandidato = $pdo->prepare($queryCandidato);
            
            foreach ($datos['candidatos'] as $candidato) {
                $stmtCandidato->execute([
                    ':planilla_id' => $planillaId,
                    ':nombre' => $candidato['nombre'],
                    ':cargo' => $candidato['cargo'],
                    ':numero_candidato' => $candidato['numero_candidato'] ?: null
                ]);
            }
        }
        
        $pdo->commit();
        return $planillaId;
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error al crear planilla: " . $e->getMessage());
        throw $e;
    }
}

function actualizarEstadoUsuario($usuarioId, $estado, $habilitado = null)
{
    try {
        $pdo = db();
        $pdo->beginTransaction();
        
        
        $query = "UPDATE usuarios SET estado = :estado WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':estado' => $estado, ':id' => $usuarioId]);
        
        
        if ($habilitado !== null) {
            $query = "UPDATE votantes SET habilitado = :habilitado, fecha_verificacion = NOW() WHERE usuario_id = :usuario_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':habilitado' => $habilitado ? 1 : 0, ':usuario_id' => $usuarioId]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error al actualizar estado de usuario: " . $e->getMessage());
        return false;
    }
}

function obtenerEstadisticasDetalladas()
{
    try {
        $stats = [];
        
        
        $query = "SELECT d.nombre, COUNT(v.id) as total_votos
                  FROM departamentos d
                  LEFT JOIN usuarios u ON d.id = u.departamento_id
                  LEFT JOIN votos v ON u.id = v.usuario_id
                  GROUP BY d.id, d.nombre
                  ORDER BY total_votos DESC";
        $stats['votos_por_departamento'] = dbQuery($query)->fetchAll();
        
        
        $query = "SELECT HOUR(registrado_en) as hora, COUNT(*) as total_votos
                  FROM votos
                  GROUP BY HOUR(registrado_en)
                  ORDER BY hora";
        $stats['votos_por_hora'] = dbQuery($query)->fetchAll();
        
        
        $query = "SELECT u.tipo_votante, 
                         COUNT(DISTINCT u.id) as total_usuarios,
                         COUNT(DISTINCT v.usuario_id) as usuarios_votaron,
                         ROUND((COUNT(DISTINCT v.usuario_id) / COUNT(DISTINCT u.id)) * 100, 2) as porcentaje_participacion
                  FROM usuarios u
                  LEFT JOIN votos v ON u.id = v.usuario_id
                  WHERE u.rol = 'votante'
                  GROUP BY u.tipo_votante";
        $stats['participacion_por_tipo'] = dbQuery($query)->fetchAll();
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error al obtener estadísticas detalladas: " . $e->getMessage());
        return [];
    }
}

function obtenerResultadosPorTipo($tipo) {
    try {
        $query = "SELECT p.partido, COUNT(*) as total_votos 
                  FROM votos v 
                  JOIN planillas p ON v.planilla_id = p.id 
                  WHERE p.tipo = ? 
                  GROUP BY p.partido 
                  ORDER BY total_votos DESC";
        return dbQuery($query, [$tipo])->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener resultados por tipo: " . $e->getMessage());
        return [];
    }
}

function obtenerCandidatosPorTipo($tipo, $departamento_id = null) {
    try {
        $query = "SELECT c.nombre, c.cargo, p.partido, p.nombre as planilla_nombre, 
                         COUNT(v.id) as total_votos, p.departamento_id, d.nombre as departamento_nombre,
                         p.municipio_id, m.nombre as municipio_nombre
                  FROM candidatos c 
                  JOIN planillas p ON c.planilla_id = p.id 
                  LEFT JOIN departamentos d ON p.departamento_id = d.id
                  LEFT JOIN municipios m ON p.municipio_id = m.id
                  LEFT JOIN votos v ON c.id = v.candidato_id 
                  WHERE p.tipo = ?";
        
        $params = [$tipo];
        
        if ($departamento_id) {
            $query .= " AND (p.departamento_id = ? OR p.departamento_id IS NULL)";
            $params[] = $departamento_id;
        }
        
        $query .= " GROUP BY c.id, c.nombre, c.cargo, p.partido, p.nombre, p.departamento_id, p.municipio_id
                   ORDER BY total_votos DESC";
        
        return dbQuery($query, $params)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener candidatos por tipo: " . $e->getMessage());
        return [];
    }
}

function obtenerTiposPlanillas() {
    try {
        $query = "SELECT DISTINCT tipo FROM planillas ORDER BY 
                  CASE tipo 
                      WHEN 'presidencial' THEN 1
                      WHEN 'diputacion' THEN 2 
                      WHEN 'alcaldia' THEN 3
                      WHEN 'vicealcaldia' THEN 4
                      ELSE 5
                  END";
        return dbQuery($query)->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener tipos de planillas: " . $e->getMessage());
        return [];
    }
}

