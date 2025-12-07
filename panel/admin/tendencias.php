<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'tendencias';

// Obtener estadísticas para las gráficas
$votosPorPartido = obtenerVotosPorPartido();
$votosPorDepartamento = obtenerVotosPorDepartamento();
$votosEnTiempo = obtenerVotosEnTiempo();
$participacionPorEdad = obtenerParticipacionPorEdad();

// Estadísticas generales
$totalVotos = obtenerTotalVotos();
$tendenciaActual = obtenerTendenciaActual();

// Preparar datos para JavaScript (gráficas)
$datosPartidos = [];
$coloresPartidos = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];
$i = 0;
foreach ($votosPorPartido as $partido) {
    $datosPartidos[] = [
        'name' => $partido['partido'],
        'y' => (int)$partido['total_votos'],
        'color' => $coloresPartidos[$i % count($coloresPartidos)]
    ];
    $i++;
}

$datosDepartamentos = [];
foreach ($votosPorDepartamento as $depto) {
    $datosDepartamentos[] = [
        'name' => $depto['departamento'],
        'y' => (int)$depto['total_votos']
    ];
}

$datosEnTiempo = [];
foreach ($votosEnTiempo as $tiempo) {
    $datosEnTiempo[] = [
        'x' => strtotime($tiempo['fecha_hora']) * 1000, // timestamp en milisegundos para Highcharts
        'y' => (int)$tiempo['votos_acumulados']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Informe de tendencias · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar admin-sidebar">
            <div class="sidebar-brand">
                <img src="../../imagen.php?img=cne_logo.png" alt="EleccionesHN">
                <span>EleccionesHN</span>
                <small>Panel de administración</small>
            </div>
            <nav class="sidebar-menu">
                <a class="sidebar-link" href="index.php">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
                <a class="sidebar-link" href="planillas.php">
                    <i class="bi bi-list-ul"></i>
                    <span>Consultar planillas</span>
                </a>
                <a class="sidebar-link" href="crear_planilla.php">
                    <i class="bi bi-plus-circle"></i>
                    <span>Crear planilla</span>
                </a>
                <a class="sidebar-link" href="usuarios.php">
                    <i class="bi bi-people"></i>
                    <span>Gestionar usuarios</span>
                </a>
                <a class="sidebar-link" href="proceso_votacion.php">
                    <i class="bi bi-check2-square"></i>
                    <span>Proceso de votación</span>
                </a>
                <a class="sidebar-link" href="comprobantes.php">
                    <i class="bi bi-receipt"></i>
                    <span>Comprobantes</span>
                </a>
                <a class="sidebar-link <?php echo $paginaActiva === 'tendencias' ? 'is-active' : ''; ?>" href="tendencias.php">
                    <i class="bi bi-bar-chart"></i>
                    <span>Informe de tendencia</span>
                </a>
                <a class="sidebar-link" href="resultados.php">
                    <i class="bi bi-trophy"></i>
                    <span>Resultados</span>
                </a>
                <a class="sidebar-link" href="denuncias_admin.php">
                    <i class="bi bi-flag"></i>
                    <span>Denuncias</span>
                </a>
                <a class="sidebar-link" href="cierre_urnas.php">
                    <i class="bi bi-lock"></i>
                    <span>Cierre de urnas</span>
                </a>
            </nav>
            <div class="sidebar-footer">Administración 2025</div>
        </aside>
        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-context">
                    <h1>Informe de tendencias</h1>
                    <span>Análisis estadístico y tendencias electorales</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-graph-up"></i>Datos en tiempo real</span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalVotos); ?></h3>
                                <p>Total de votos</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo htmlspecialchars($tendenciaActual['lider'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p>Líder actual</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--warning">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo date('H:i'); ?></h3>
                                <p>Última actualización</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Votos por partido</h5>
                                    <button class="btn btn-outline-primary btn-sm" onclick="actualizarGraficas()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="grafica-partidos" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Votos por departamento</h5>
                            </div>
                            <div class="card-body">
                                <div id="grafica-departamentos" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tendencia de votación en tiempo real</h5>
                            </div>
                            <div class="card-body">
                                <div id="grafica-tiempo" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Resultados detallados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-admin">
                                        <thead>
                                            <tr>
                                                <th>Partido</th>
                                                <th>Votos</th>
                                                <th>Porcentaje</th>
                                                <th>Tendencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($votosPorPartido as $partido): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($partido['partido'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo number_format($partido['total_votos']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $porcentaje = $totalVotos > 0 ? ($partido['total_votos'] / $totalVotos) * 100 : 0;
                                                        echo number_format($porcentaje, 2); ?>%
                                                    </td>
                                                    <td>
                                                        <?php if ($porcentaje > 25): ?>
                                                            <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                                        <?php elseif ($porcentaje > 15): ?>
                                                            <i class="bi bi-arrow-right-circle-fill text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-arrow-down-circle-fill text-danger"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script type="application/json" id="datos-partidos"><?php echo json_encode($datosPartidos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
    <script type="application/json" id="datos-departamentos"><?php echo json_encode($datosDepartamentos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
    <script type="application/json" id="datos-tiempo"><?php echo json_encode($datosEnTiempo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/tendencias.js"></script>
</body>
</html>