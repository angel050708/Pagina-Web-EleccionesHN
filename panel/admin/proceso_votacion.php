<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'proceso_votacion';

// Obtener estadísticas del proceso
$estadisticasVotacion = obtenerEstadisticasVotacion();
$centrosVotacion = obtenerCentrosVotacion();
$ultimasVotaciones = obtenerUltimasVotaciones(10);

// Obtener configuración del proceso
$configuracionActual = obtenerConfiguracionVotacion();

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

// Procesar cambios en configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    try {
        switch ($_POST['accion']) {
            case 'iniciar_votacion':
                if (iniciarProcesoVotacion()) {
                    $mensaje = 'Proceso de votación iniciado correctamente.';
                } else {
                    $error = 'Error al iniciar el proceso de votación.';
                }
                break;
            
            case 'pausar_votacion':
                if (pausarProcesoVotacion()) {
                    $mensaje = 'Proceso de votación pausado.';
                } else {
                    $error = 'Error al pausar el proceso de votación.';
                }
                break;
            
            case 'finalizar_votacion':
                if (finalizarProcesoVotacion()) {
                    $mensaje = 'Proceso de votación finalizado correctamente.';
                } else {
                    $error = 'Error al finalizar el proceso de votación.';
                }
                break;
                
            case 'actualizar_configuracion':
                $config = [
                    'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                    'fecha_fin' => $_POST['fecha_fin'] ?? '',
                    'hora_inicio' => $_POST['hora_inicio'] ?? '',
                    'hora_fin' => $_POST['hora_fin'] ?? '',
                    'permitir_voto_temprano' => isset($_POST['permitir_voto_temprano']) ? 1 : 0
                ];
                
                if (actualizarConfiguracionVotacion($config)) {
                    $mensaje = 'Configuración actualizada correctamente.';
                } else {
                    $error = 'Error al actualizar la configuración.';
                }
                break;
        }
        
        // Recargar datos después de los cambios
        $estadisticasVotacion = obtenerEstadisticasVotacion();
        $configuracionActual = obtenerConfiguracionVotacion();
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Proceso de votación · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
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
                <a class="sidebar-link <?php echo $paginaActiva === 'proceso_votacion' ? 'is-active' : ''; ?>" href="proceso_votacion.php">
                    <i class="bi bi-check2-square"></i>
                    <span>Proceso de votación</span>
                </a>
                <a class="sidebar-link" href="comprobantes.php">
                    <i class="bi bi-receipt"></i>
                    <span>Comprobantes</span>
                </a>
                <a class="sidebar-link" href="tendencias.php">
                    <i class="bi bi-bar-chart"></i>
                    <span>Informe de tendencia</span>
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
                    <h1>Proceso de votación</h1>
                    <span>Control y monitoreo del proceso electoral</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip chip--<?php 
                            echo $configuracionActual['estado'] === 'activo' ? 'success' : 
                                ($configuracionActual['estado'] === 'pausado' ? 'warning' : 'secondary'); 
                        ?>">
                            <i class="bi bi-<?php 
                                echo $configuracionActual['estado'] === 'activo' ? 'play-fill' : 
                                    ($configuracionActual['estado'] === 'pausado' ? 'pause-fill' : 'stop-fill'); 
                            ?>"></i>
                            <?php echo ucfirst($configuracionActual['estado'] ?? 'Inactivo'); ?>
                        </span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($estadisticasVotacion['total_votos'] ?? 0); ?></h3>
                                <p>Votos registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($estadisticasVotacion['centros_activos'] ?? 0); ?></h3>
                                <p>Centros activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-percent"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($estadisticasVotacion['participacion'] ?? 0, 1); ?>%</h3>
                                <p>Participación</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($estadisticasVotacion['incidencias'] ?? 0); ?></h3>
                                <p>Incidencias</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Control del proceso</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="process-control">
                                            <h6>Estado actual</h6>
                                            <div class="status-indicator status-<?php echo $configuracionActual['estado'] ?? 'inactivo'; ?>">
                                                <i class="bi bi-<?php 
                                                    echo $configuracionActual['estado'] === 'activo' ? 'play-circle-fill' : 
                                                        ($configuracionActual['estado'] === 'pausado' ? 'pause-circle-fill' : 'stop-circle-fill'); 
                                                ?>"></i>
                                                <span><?php echo ucfirst($configuracionActual['estado'] ?? 'Inactivo'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="process-actions">
                                            <h6>Acciones disponibles</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <?php if (($configuracionActual['estado'] ?? 'inactivo') === 'inactivo'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="iniciar_votacion">
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Iniciar el proceso de votación?')">
                                                            <i class="bi bi-play-fill"></i> Iniciar
                                                        </button>
                                                    </form>
                                                <?php elseif ($configuracionActual['estado'] === 'activo'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="pausar_votacion">
                                                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Pausar el proceso de votación?')">
                                                            <i class="bi bi-pause-fill"></i> Pausar
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="finalizar_votacion">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Finalizar el proceso de votación? Esta acción no se puede deshacer.')">
                                                            <i class="bi bi-stop-fill"></i> Finalizar
                                                        </button>
                                                    </form>
                                                <?php elseif ($configuracionActual['estado'] === 'pausado'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="iniciar_votacion">
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Reanudar el proceso de votación?')">
                                                            <i class="bi bi-play-fill"></i> Reanudar
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="finalizar_votacion">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Finalizar el proceso de votación? Esta acción no se puede deshacer.')">
                                                            <i class="bi bi-stop-fill"></i> Finalizar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Últimas votaciones</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($ultimasVotaciones)): ?>
                                    <div class="timeline">
                                        <?php foreach ($ultimasVotaciones as $votacion): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-primary"></div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between">
                                                        <strong><?php echo htmlspecialchars($votacion['votante_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <small class="text-muted"><?php echo date('H:i:s', strtotime($votacion['fecha_voto'])); ?></small>
                                                    </div>
                                                    <div class="text-muted small">
                                                        Centro: <?php echo htmlspecialchars($votacion['centro_votacion'], ENT_QUOTES, 'UTF-8'); ?>
                                                        <br>Cédula: <?php echo htmlspecialchars($votacion['cedula'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No hay votaciones registradas aún</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Centros de votación</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($centrosVotacion)): ?>
                                    <div class="centros-list">
                                        <?php foreach ($centrosVotacion as $centro): ?>
                                            <div class="centro-item mb-2 p-2 bg-light rounded">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="fw-medium"><?php echo htmlspecialchars($centro['nombre'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($centro['ubicacion'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $centro['activo'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $centro['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="bi bi-geo-alt text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No hay centros configurados</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/proceso_votacion.js"></script>
</body>
</html>