<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'cierre_urnas';

// Obtener estado del proceso de votación
$estadoVotacion = obtenerEstadoProcesoVotacion();
$puedeIniciarCierre = $estadoVotacion['estado'] === 'activo';

// Obtener información de urnas/centros
$centrosVotacion = obtenerInformacionCentrosParaCierre();
$resumenCierre = obtenerResumenCierreUrnas();

// Estadísticas del cierre
$totalCentros = count($centrosVotacion);
$centrosCerrados = count(array_filter($centrosVotacion, function($c) { return $c['cerrado']; }));
$centrosPendientes = $totalCentros - $centrosCerrados;

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

// Procesar acciones de cierre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    try {
        switch ($_POST['accion']) {
            case 'iniciar_cierre_general':
                if (iniciarCierreGeneralUrnas()) {
                    $mensaje = 'Proceso de cierre de urnas iniciado correctamente.';
                } else {
                    $error = 'Error al iniciar el cierre de urnas.';
                }
                break;
            
            case 'reabrir_centros':
                if (reabrirCentros()) {
                    $mensaje = 'Todos los centros cerrados fueron reabiertos.';
                } else {
                    $error = 'Error al reabrir centros.';
                }
                break;
                
            case 'cerrar_centro_individual':
                $centroId = (int)$_POST['centro_id'];
                $observaciones = $_POST['observaciones'] ?? '';
                
                if (cerrarCentroVotacion($centroId, $_SESSION['usuario_id'], $observaciones)) {
                    $mensaje = 'Centro de votación cerrado correctamente.';
                } else {
                    $error = 'Error al cerrar el centro de votación.';
                }
                break;
            
            case 'abrir_centro_individual':
                $centroId = (int)$_POST['centro_id'];
                if (abrirCentroVotacion($centroId)) {
                    $mensaje = 'Centro de votación abierto nuevamente.';
                } else {
                    $error = 'Error al abrir el centro de votación.';
                }
                break;
                
            case 'generar_acta_cierre':
                $centroId = (int)$_POST['centro_id'];
                if (generarActaCierre($centroId)) {
                    $mensaje = 'Acta de cierre generada correctamente.';
                } else {
                    $error = 'Error al generar el acta de cierre.';
                }
                break;
                
            case 'finalizar_proceso_electoral':
                if (finalizarProcesoElectoral($_SESSION['usuario_id'])) {
                    $mensaje = 'Proceso electoral finalizado oficialmente.';
                } else {
                    $error = 'Error al finalizar el proceso electoral.';
                }
                break;
        }
        
        // Recargar datos
        $centrosVotacion = obtenerInformacionCentrosParaCierre();
        $resumenCierre = obtenerResumenCierreUrnas();
        $estadoVotacion = obtenerEstadoProcesoVotacion();
        
        $centrosCerrados = count(array_filter($centrosVotacion, function($c) { return $c['cerrado']; }));
        $centrosPendientes = $totalCentros - $centrosCerrados;
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$procesoCompletamenteTerminado = $centrosPendientes == 0 && $estadoVotacion['estado'] === 'finalizado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cierre de urnas · EleccionesHN</title>
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
                <a class="sidebar-link" href="proceso_votacion.php">
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
                <a class="sidebar-link" href="resultados.php">
                    <i class="bi bi-trophy"></i>
                    <span>Resultados</span>
                </a>
                <a class="sidebar-link" href="denuncias_admin.php">
                    <i class="bi bi-flag"></i>
                    <span>Denuncias</span>
                </a>
                <a class="sidebar-link <?php echo $paginaActiva === 'cierre_urnas' ? 'is-active' : ''; ?>" href="cierre_urnas.php">
                    <i class="bi bi-lock"></i>
                    <span>Cierre de urnas</span>
                </a>
            </nav>
            <div class="sidebar-footer">Administración 2025</div>
        </aside>
        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-context">
                    <h1>Cierre de urnas</h1>
                    <span>Finalización del proceso electoral y cierre oficial</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip chip--<?php echo $procesoCompletamenteTerminado ? 'success' : 'warning'; ?>">
                            <i class="bi bi-<?php echo $procesoCompletamenteTerminado ? 'check-circle-fill' : 'clock-fill'; ?>"></i>
                            <?php echo $procesoCompletamenteTerminado ? 'Completado' : $centrosPendientes . ' pendientes'; ?>
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
                    <div class="col-lg-6 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalCentros); ?></h3>
                                <p>Total centros</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($centrosCerrados); ?></h3>
                                <p>Centros cerrados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Control del cierre</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="process-control">
                                            <h6>Estado del proceso</h6>
                                            <div class="status-indicator status-<?php echo $estadoVotacion['estado']; ?>">
                                                <i class="bi bi-<?php 
                                                    echo $estadoVotacion['estado'] === 'finalizado' ? 'check-circle-fill' : 
                                                        ($estadoVotacion['estado'] === 'activo' ? 'play-circle-fill' : 'stop-circle-fill'); 
                                                ?>"></i>
                                                <span><?php echo ucfirst($estadoVotacion['estado']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="process-actions">
                                            <h6>Acciones disponibles</h6>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <?php if ($puedeIniciarCierre && $centrosPendientes > 0): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="iniciar_cierre_general">
                                                        <button type="submit" class="btn btn-warning btn-sm" 
                                                                onclick="return confirm('¿Iniciar el cierre general de urnas? Esto impedirá nuevos votos.')">
                                                            <i class="bi bi-lock-fill"></i> Iniciar cierre
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($centrosCerrados > 0): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="reabrir_centros">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm" 
                                                                onclick="return confirm('¿Reabrir todos los centros cerrados?')">
                                                            <i class="bi bi-unlock"></i> Reabrir todos
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($centrosPendientes == 0 && $estadoVotacion['estado'] !== 'finalizado'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="finalizar_proceso_electoral">
                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('¿Finalizar oficialmente el proceso electoral? Esta acción es irreversible.')">
                                                            <i class="bi bi-check-circle-fill"></i> Finalizar proceso
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card table-admin">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Estado de centros de votación</h5>
                            <button class="btn btn-outline-primary btn-sm" onclick="actualizarEstado()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($centrosVotacion)): ?>
                            <div class="table-responsive">
                                <table class="table table-admin mb-0">
                                    <thead>
                                        <tr>
                                            <th>Centro</th>
                                            <th>Ubicación</th>
                                            <th>Total votos</th>
                                            <th>Último voto</th>
                                            <th>Estado</th>
                                            <th>Hora cierre</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($centrosVotacion as $centro): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($centro['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <br><small class="text-muted">ID: <?php echo $centro['id']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($centro['ubicacion'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo number_format($centro['total_votos']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($centro['ultimo_voto']): ?>
                                                        <small class="text-muted">
                                                            <?php echo date('H:i:s', strtotime($centro['ultimo_voto'])); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin votos</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php echo $centro['cerrado'] ? 'inactive' : 'active'; ?>">
                                                        <?php echo $centro['cerrado'] ? 'Cerrado' : 'Abierto'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($centro['hora_cierre']): ?>
                                                        <small class="text-success">
                                                            <?php echo date('H:i:s', strtotime($centro['hora_cierre'])); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if (!$centro['cerrado']): ?>
                                                            <button class="btn-icon btn-icon--warning" title="Cerrar centro" 
                                                                    onclick="cerrarCentroVotacion(<?php echo $centro['id']; ?>)">
                                                                <i class="bi bi-lock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($centro['cerrado']): ?>
                                                            <button class="btn-icon btn-icon--success" title="Generar acta" 
                                                                    onclick="generarActaCentro(<?php echo $centro['id']; ?>)">
                                                                <i class="bi bi-file-text"></i>
                                                            </button>
                                                            <button class="btn-icon btn-icon--secondary" title="Abrir centro" 
                                                                    onclick="abrirCentroVotacion(<?php echo $centro['id']; ?>)">
                                                                <i class="bi bi-unlock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No hay centros configurados</h4>
                                <p class="text-muted">No se encontraron centros de votación en el sistema.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal cerrar centro -->
    <div class="modal fade" id="modalCerrarCentro" tabindex="-1" aria-labelledby="modalCerrarCentroLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCerrarCentroLabel">Cerrar centro de votación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="accion" value="cerrar_centro_individual">
                    <input type="hidden" name="centro_id" id="centro_id_cierre">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> Una vez cerrado el centro, no se podrán registrar más votos en este centro.
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones del cierre</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="4" 
                                      placeholder="Agregar observaciones sobre el cierre del centro (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-lock me-2"></i>Cerrar centro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/cierre_urnas.js"></script>
</body>
</html>