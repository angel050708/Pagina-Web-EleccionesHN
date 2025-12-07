<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../../login.php?error=Debes iniciar sesión como votante.');
    exit;
}

$rolUsuario = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : '';
if ($rolUsuario !== 'votante') {
    header('Location: ../../login.php?error=Debes iniciar sesión como votante.');
    exit;
}

$resumen = obtenerResumenVotante($_SESSION['usuario_id']);
if (!$resumen) {
    $resumen = array();
}

// Si no tiene tipo de votante configurado, redirigir a selección
if (empty($resumen['tipo_votante'])) {
    header('Location: seleccionar_tipo_votacion.php');
    exit;
}

$paginaActiva = 'inicio';
$mensaje = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
$alerta = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : null;
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') : null;

$tipoVotante = isset($resumen['tipo_votante']) ? $resumen['tipo_votante'] : 'nacional';
$esInternacional = $tipoVotante === 'internacional';

$ubicacion = (isset($resumen['ubicacion_dni']) && is_array($resumen['ubicacion_dni'])) ? $resumen['ubicacion_dni'] : array();

$departamentoNombre = 'Sin asignar';
if (!empty($resumen['departamento_nombre'])) {
    $departamentoNombre = $resumen['departamento_nombre'];
} elseif (!empty($ubicacion['departamento'])) {
    $departamentoNombre = $ubicacion['departamento'];
}

$municipioNombre = 'Sin asignar';
if (!empty($resumen['municipio_nombre'])) {
    $municipioNombre = $resumen['municipio_nombre'];
} elseif (!empty($ubicacion['municipio'])) {
    $municipioNombre = $ubicacion['municipio'];
}

$centros = array();
if (!$esInternacional && !empty($resumen['municipio_id'])) {
    $centros = obtenerCentrosPorMunicipio($resumen['municipio_id']);
}

$paisPreferido = 'Estados Unidos de América';
if ($esInternacional) {
    if (!empty($resumen['pais_residencia'])) {
        $paisPreferido = $resumen['pais_residencia'];
    } elseif (!empty($_SESSION['usuario_pais'])) {
        $paisPreferido = $_SESSION['usuario_pais'];
    }
}

$centrosExterior = array();
if ($esInternacional) {
    $centrosExterior = obtenerCentrosExteriorPorPais($paisPreferido);
}

$centroAsignado = null;
if ($esInternacional && !empty($resumen['centro_votacion_exterior_id'])) {
    $centroAsignado = array(
        'tipo' => 'exterior',
        'nombre' => !empty($resumen['centro_exterior_nombre']) ? $resumen['centro_exterior_nombre'] : '',
        'codigo' => !empty($resumen['centro_exterior_sector']) ? $resumen['centro_exterior_sector'] : '',
        'direccion' => !empty($resumen['centro_exterior_direccion']) ? $resumen['centro_exterior_direccion'] : '',
        'ciudad' => !empty($resumen['centro_exterior_ciudad']) ? $resumen['centro_exterior_ciudad'] : '',
        'estado' => !empty($resumen['centro_exterior_estado']) ? $resumen['centro_exterior_estado'] : '',
        'pais' => $paisPreferido,
    );
} elseif (!empty($resumen['centro_votacion_id'])) {
    $centroAsignado = array(
        'tipo' => 'nacional',
        'nombre' => !empty($resumen['centro_nombre']) ? $resumen['centro_nombre'] : '',
        'codigo' => !empty($resumen['centro_codigo']) ? $resumen['centro_codigo'] : '',
        'direccion' => !empty($resumen['centro_direccion']) ? $resumen['centro_direccion'] : '',
        'municipio' => $municipioNombre,
        'departamento' => $departamentoNombre,
    );
}

$diputadosPermitidos = 0;
if (!empty($resumen['diputados_cupos'])) {
    $diputadosPermitidos = (int) $resumen['diputados_cupos'];
} elseif (!empty($ubicacion['diputados_cupos'])) {
    $diputadosPermitidos = (int) $ubicacion['diputados_cupos'];
}

$denuncias = obtenerDenunciasPorUsuario($_SESSION['usuario_id']);
$denunciasRecientes = array_slice($denuncias, 0, 3);
$totalDenuncias = count($denuncias);

$votosEmitidos = count(obtenerVotosPorUsuario($_SESSION['usuario_id']));

$dniUsuario = isset($resumen['dni']) ? $resumen['dni'] : '';
$habilitado = !empty($resumen['habilitado']);

$ultimoAccesoFormateado = null;
if (!empty($resumen['ultimo_acceso'])) {
    try {
        $ultimoAcceso = new DateTime($resumen['ultimo_acceso']);
        $ultimoAccesoFormateado = $ultimoAcceso->format('d/m/Y H:i');
    } catch (Exception $e) {
        $ultimoAccesoFormateado = $resumen['ultimo_acceso'];
    }
}

$pasosTotales = 4;
$pasosCompletados = 0;
if (!empty($resumen)) {
    $pasosCompletados++;
}
if ($centroAsignado) {
    $pasosCompletados++;
}
if ($habilitado) {
    $pasosCompletados++;
}
if ($votosEmitidos > 0) {
    $pasosCompletados++;
}

$avance = $pasosTotales > 0 ? (int) round(($pasosCompletados / $pasosTotales) * 100) : 0;
$avanceDescripcion = 'Proceso en progreso';
if ($avance >= 100) {
    $avanceDescripcion = 'Proceso completado';
} elseif ($avance >= 75) {
    $avanceDescripcion = 'Listo para votar';
}

$puedeVotar = $habilitado && $centroAsignado;
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Votante';
$tipoVotanteEtiqueta = $esInternacional ? 'Exterior' : 'Nacional';
$estadoHabilitado = $habilitado ? 'Habilitado' : 'Pendiente';
$centroEstadoEtiqueta = $centroAsignado ? 'Asignado' : 'Pendiente';
$notaEstado = $estadoHabilitado === 'Habilitado' ? 'Puedes emitir tu voto cuando lo decidas.' : 'Completa tu validación para participar.';
$notaCentro = $centroAsignado ? 'Centro confirmado para esta jornada.' : ($esInternacional ? 'Selecciona una sede consular disponible.' : 'Elige un centro dentro de tu municipio.');
$textoCentroChip = $centroAsignado ? ($esInternacional ? trim(($centroAsignado['ciudad'] ? $centroAsignado['ciudad'] . ', ' : '') . $centroAsignado['estado']) : $centroAsignado['nombre']) : 'Centro pendiente';
$notaDenuncias = $totalDenuncias > 0 ? 'Última registrada recientemente.' : 'Sin reportes ingresados aún.';

$pasosProceso = array(
    array(
        'titulo' => 'Registro validado',
        'descripcion' => 'Tu cuenta está activa en la plataforma.',
        'completado' => !empty($resumen)
    ),
    array(
        'titulo' => $esInternacional ? 'Centro consular asignado' : 'Centro de votación asignado',
        'descripcion' => $esInternacional ? 'Selecciona tu sede consular para el voto exterior.' : 'Confirma el centro asignado según tu DNI.',
        'completado' => (bool) $centroAsignado
    ),
    array(
        'titulo' => 'Habilitación en padrón',
        'descripcion' => 'Revisa que tu estado esté marcado como habilitado.',
        'completado' => $habilitado
    ),
    array(
        'titulo' => 'Voto emitido',
        'descripcion' => 'Ingresa al módulo de votación y registra tus planillas.',
        'completado' => $votosEmitidos > 0
    ),
);

$mapaEstadosDenuncia = array(
    'recibida' => array('label' => 'Recibida', 'class' => 'status-chip--info'),
    'en_revision' => array('label' => 'En revisión', 'class' => 'status-chip--warning'),
    'resuelta' => array('label' => 'Resuelta', 'class' => 'status-chip--success'),
    'rechazada' => array('label' => 'Rechazada', 'class' => 'status-chip--danger'),
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel del votante · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
</head>
<body>
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar">
            <div class="sidebar-brand">
                <img src="../../imagen.php?img=cne_logo.png" alt="EleccionesHN">
                <span>EleccionesHN</span>
                <small>Portal del votante</small>
            </div>
            <nav class="sidebar-menu">
                <a class="sidebar-link <?php echo $paginaActiva === 'inicio' ? 'is-active' : ''; ?>" href="index.php">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Inicio</span>
                </a>
                <a class="sidebar-link" href="datos.php">
                    <i class="bi bi-person-vcard"></i>
                    <span>Datos personales</span>
                </a>
                <a class="sidebar-link" href="denuncias.php">
                    <i class="bi bi-flag"></i>
                    <span>Denuncias</span>
                </a>
                <a class="sidebar-link" href="votar.php">
                    <i class="bi bi-check2-square"></i>
                    <span>Realizar votación</span>
                </a>
                <a class="sidebar-link" href="recibo.php">
                    <i class="bi bi-receipt"></i>
                    <span>Mi recibo</span>
                </a>
                <a class="sidebar-link" href="resultados.php">
                    <i class="bi bi-trophy"></i>
                    <span>Resultados</span>
                </a>
            </nav>
            <div class="sidebar-footer">Proceso electoral 2025</div>
        </aside>
        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-context">
                    <h1>Panel del votante</h1>
                    <span>Elecciones Generales Honduras 2025</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip"><i class="bi bi-person-vcard"></i>DNI <?php echo htmlspecialchars($dniUsuario !== '' ? $dniUsuario : 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($departamentoNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-building"></i><?php echo htmlspecialchars($municipioNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-compass"></i><?php echo htmlspecialchars($tipoVotanteEtiqueta, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-pin-map"></i><?php echo htmlspecialchars($textoCentroChip, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <section class="hero-panel">
                    <div>
                        <h2 class="hero-title">Hola, <?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="hero-subtitle">Gestiona tus datos, confirma tu centro y completa el proceso de votación desde un solo lugar.</p>
                    </div>
                    <div class="hero-meta">
                        <span class="badge-light">Proceso 2025</span>
                        <?php if ($habilitado): ?>
                            <span class="badge-light">Habilitado </span>
                        <?php else: ?>
                            <span class="badge-light">Verificación pendiente</span>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($alerta): ?>
                    <div class="notice notice--danger">
                        <span class="notice__icon"><i class="bi bi-exclamation-triangle"></i></span>
                        <div><?php echo $alerta; ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje): ?>
                    <div class="notice notice--success">
                        <span class="notice__icon"><i class="bi bi-check-circle"></i></span>
                        <div><?php echo $mensaje; ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="notice notice--success">
                        <span class="notice__icon"><i class="bi bi-check-circle"></i></span>
                        <div><?php echo $success; ?></div>
                    </div>
                <?php endif; ?>

                <section class="section-card progress-card">
                    <div class="section-card__body progress-card__body">
                        <div class="progress-card__metric">
                            <span class="progress-card__label">Avance del proceso</span>
                            <span class="progress-card__value"><?php echo (int) $avance; ?>%</span>
                            <span class="progress-card__hint"><?php echo htmlspecialchars($avanceDescripcion, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($ultimoAccesoFormateado): ?>
                                <span class="progress-card__meta"><i class="bi bi-clock-history"></i>Último acceso: <?php echo htmlspecialchars($ultimoAccesoFormateado, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-card__steps">
                            <?php foreach ($pasosProceso as $indice => $paso): ?>
                                <?php $numeroPaso = $indice + 1; ?>
                                <div class="step-item<?php echo $paso['completado'] ? ' is-complete' : ''; ?>">
                                    <div class="step-item__marker"><?php echo (int) $numeroPaso; ?></div>
                                    <div class="step-item__info">
                                        <h3 class="step-item__title"><?php echo htmlspecialchars($paso['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <p class="step-item__desc"><?php echo htmlspecialchars($paso['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section class="kpi-grid">
                    <article class="kpi-card">
                        <span class="kpi-icon"><i class="bi bi-geo-alt"></i></span>
                        <span class="kpi-label">Departamento</span>
                        <span class="kpi-value"><?php echo htmlspecialchars($departamentoNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                    <article class="kpi-card">
                        <span class="kpi-icon"><i class="bi bi-building"></i></span>
                        <span class="kpi-label">Municipio</span>
                        <span class="kpi-value"><?php echo htmlspecialchars($municipioNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                    <article class="kpi-card">
                        <span class="kpi-icon"><i class="bi bi-shield-check"></i></span>
                        <span class="kpi-label">Estado</span>
                        <span class="kpi-value"><?php echo $habilitado ? 'Habilitado' : 'Pendiente'; ?></span>
                        <div class="kpi-note"><?php echo htmlspecialchars($notaEstado, ENT_QUOTES, 'UTF-8'); ?></div>
                    </article>
                    <article class="kpi-card">
                        <span class="kpi-icon"><i class="bi bi-list-check"></i></span>
                        <span class="kpi-label">Votos permitidos</span>
                        <span class="kpi-value"><?php echo $diputadosPermitidos > 0 ? $diputadosPermitidos : 'N/D'; ?></span>
                        <div class="kpi-note">Diputados para tu departamento</div>
                    </article>
                    <article class="kpi-card">
                        <span class="kpi-icon"><i class="bi bi-pin-map-fill"></i></span>
                        <span class="kpi-label">Centro</span>
                        <span class="kpi-value"><?php echo htmlspecialchars($centroEstadoEtiqueta, ENT_QUOTES, 'UTF-8'); ?></span>
                        <div class="kpi-note"><?php echo htmlspecialchars($notaCentro, ENT_QUOTES, 'UTF-8'); ?></div>
                    </article>
                </section>

                <section class="section-card" id="consulta-votacion">
                    <div class="section-card__header">
                        <h2 class="section-card__title">Consulta tu lugar de votación</h2>
                        <p class="section-card__subtitle">Validamos el centro asignado con los datos de tu DNI.</p>
                    </div>
                    <div class="section-card__body">
                        <div class="data-grid">
                            <div>
                                <label>DNI</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($dniUsuario !== '' ? $dniUsuario : 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            <div>
                                <label>Departamento</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($departamentoNombre, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            <div>
                                <label>Municipio</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($municipioNombre, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                        </div>
                        <hr class="my-4">
                        <?php if ($centroAsignado): ?>
                            <div class="info-block assigned-center">
                                <div><strong><?php echo $esInternacional ? 'Sede consular' : 'Centro asignado'; ?>:</strong> <?php echo htmlspecialchars($centroAsignado['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php if (!empty($centroAsignado['codigo'])): ?>
                                    <div><strong>Sector / código:</strong> <?php echo htmlspecialchars($centroAsignado['codigo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($centroAsignado['direccion'])): ?>
                                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($centroAsignado['direccion'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($centroAsignado['ciudad'])): ?>
                                    <div><strong>Ciudad:</strong> <?php echo htmlspecialchars($centroAsignado['ciudad'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($centroAsignado['estado'])): ?>
                                    <div><strong>Estado / provincia:</strong> <?php echo htmlspecialchars($centroAsignado['estado'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($centroAsignado['municipio'])): ?>
                                    <div><strong>Municipio:</strong> <?php echo htmlspecialchars($centroAsignado['municipio'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($centroAsignado['departamento'])): ?>
                                    <div><strong>Departamento:</strong> <?php echo htmlspecialchars($centroAsignado['departamento'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <div class="info-note"><?php echo htmlspecialchars($notaCentro, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="notice notice--muted mb-4">
                                <span class="notice__icon"><i class="bi bi-search"></i></span>
                                <div><?php echo $esInternacional ? 'Selecciona la sede consular disponible en tu país de residencia.' : 'Selecciona un centro disponible en tu municipio para habilitar la votación digital.'; ?></div>
                            </div>
                            <?php if ($esInternacional): ?>
                                <?php if ($centrosExterior): ?>
                                    <div class="table-responsive">
                                        <table class="table-dashboard">
                                            <thead>
                                                <tr>
                                                    <th>Ciudad</th>
                                                    <th>Estado</th>
                                                    <th>Sede</th>
                                                    <th>Sector electoral</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($centrosExterior as $centro): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($centro['ciudad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($centro['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($centro['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($centro['sector_electoral'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <form method="post" action="../../scripts/seleccionar_centro_exterior.php" class="d-inline">
                                                                <input type="hidden" name="centro_id" value="<?php echo (int) $centro['id']; ?>">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm">Seleccionar sede</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">Aún no se han registrado sedes para el país seleccionado. Contacta a la embajada para obtener asistencia.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($centros): ?>
                                    <div class="table-responsive">
                                        <table class="table-dashboard">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Código</th>
                                                    <th>Dirección</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($centros as $centro): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($centro['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($centro['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($centro['direccion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <form method="post" action="../../scripts/seleccionar_centro.php" class="d-inline">
                                                                <input type="hidden" name="centro_id" value="<?php echo (int) $centro['id']; ?>">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm">Elegir centro</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">Aún no hay centros registrados para tu municipio. Contacta a tu junta receptora.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-card__header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="section-card__title">Acciones rápidas</h2>
                            <p class="section-card__subtitle">Salta directamente a los módulos que necesitas.</p>
                        </div>
                        <span class="tag">Portal del votante</span>
                    </div>
                    <div class="section-card__body">
                        <div class="quick-actions">
                            <a class="quick-link" href="datos.php">
                                <span class="quick-link-icon"><i class="bi bi-person-lines-fill"></i></span>
                                <div>
                                    <h5>Datos personales</h5>
                                    <p>Confirma que tu información de contacto y documento sea correcta.</p>
                                </div>
                                <span>Revisar datos</span>
                            </a>
                            <a class="quick-link" href="votar.php">
                                <span class="quick-link-icon"><i class="bi bi-check2-square"></i></span>
                                <div>
                                    <h5>Realizar votación</h5>
                                    <p>Ingresa al módulo seguro para emitir tus votos por planilla.</p>
                                </div>
                                <span>Ingresar al módulo</span>
                            </a>
                            <a class="quick-link" href="recibo.php">
                                <span class="quick-link-icon"><i class="bi bi-receipt"></i></span>
                                <div>
                                    <h5>Recibo digital</h5>
                                    <p>Consulta y descarga tu comprobante oficial de participación.</p>
                                </div>
                                <span>Ver comprobante</span>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-card__header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="section-card__title">Denuncias de actos irregulares</h2>
                            <p class="section-card__subtitle">Comparte incidencias detectadas durante el proceso electoral.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="denuncias.php"><i class="bi bi-flag me-1"></i>Nueva denuncia</a>
                    </div>
                    <div class="section-card__body">
                        <div class="denuncias-summary">
                            <div>
                                <span class="denuncias-summary__count"><?php echo (int) $totalDenuncias; ?></span>
                                <span class="denuncias-summary__label">Denuncias registradas</span>
                            </div>
                            <span class="denuncias-summary__hint"><?php echo htmlspecialchars($notaDenuncias, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <?php if ($denunciasRecientes): ?>
                            <div class="denuncias-list">
                                <?php foreach ($denunciasRecientes as $denuncia): ?>
                                    <?php
                                        $estadoClave = isset($denuncia['estado']) ? $denuncia['estado'] : 'recibida';
                                        $estadoDenuncia = isset($mapaEstadosDenuncia[$estadoClave]) ? $mapaEstadosDenuncia[$estadoClave] : $mapaEstadosDenuncia['recibida'];
                                        $marcaTiempo = isset($denuncia['creada_en']) ? strtotime($denuncia['creada_en']) : false;
                                        $fechaPresentacion = $marcaTiempo ? date('d/m/Y H:i', $marcaTiempo) : 'Sin fecha';
                                    ?>
                                    <article class="denuncia-item">
                                        <header class="denuncia-item__header">
                                            <h3 class="denuncia-item__title"><?php echo htmlspecialchars($denuncia['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <span class="status-chip <?php echo $estadoDenuncia['class']; ?>"><?php echo htmlspecialchars($estadoDenuncia['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </header>
                                        <p class="denuncia-item__description"><?php echo htmlspecialchars($denuncia['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <footer class="denuncia-item__footer">
                                            <span><i class="bi bi-calendar-event"></i><?php echo htmlspecialchars($fechaPresentacion, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php if (!empty($denuncia['evidencia_url'])): ?>
                                                <a class="denuncia-item__evidence" href="<?php echo htmlspecialchars($denuncia['evidencia_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                                    <i class="bi bi-paperclip"></i>Ver evidencia
                                                </a>
                                            <?php endif; ?>
                                        </footer>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="mb-0 text-muted">No has registrado denuncias todavía. Usa el botón &ldquo;Nueva denuncia&rdquo; para iniciar un reporte.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="cta-panel">
                    <div>
                        <h3 class="fw-semibold mb-2">¿Listo para votar?</h3>
                        <p>El proceso es totalmente digital y respaldado por el Tribunal Supremo Electoral.</p>
                    </div>
                    <a class="btn btn-primary" href="votar.php">Ir al módulo de votación</a>
                </section>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
