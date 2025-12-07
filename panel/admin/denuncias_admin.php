<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'denuncias_admin';

// Obtener filtros
$filtroEstado = $_GET['estado'] ?? '';
$filtroTipo = $_GET['tipo'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

// Construir filtros para consulta
$filtros = [];
if ($filtroEstado) $filtros['estado'] = $filtroEstado;
if ($filtroTipo) $filtros['tipo'] = $filtroTipo;
if ($filtroFecha) $filtros['fecha'] = $filtroFecha;
if ($busqueda) $filtros['busqueda'] = $busqueda;

// Obtener denuncias
$denuncias = obtenerDenunciasAdmin($filtros);
$tiposDenuncia = [
    'compra_votos' => ['label' => 'Compra de votos', 'badge' => 'danger'],
    'coaccion' => ['label' => 'Coacción o intimidación', 'badge' => 'pending'],
    'propaganda_ilegal' => ['label' => 'Propaganda en lugares prohibidos', 'badge' => 'active'],
    'centro_cerrado' => ['label' => 'Centro de votación cerrado', 'badge' => 'inactive'],
    'personal_inadecuado' => ['label' => 'Personal inadecuado en centro', 'badge' => 'inactive'],
    'material_danado' => ['label' => 'Material electoral dañado', 'badge' => 'inactive'],
    'voto_multiple' => ['label' => 'Intento de voto múltiple', 'badge' => 'active'],
    'fraude_actas' => ['label' => 'Alteración de actas', 'badge' => 'danger'],
    'transporte_ilegal' => ['label' => 'Transporte ilegal de personas', 'badge' => 'pending'],
    'otros' => ['label' => 'Otros', 'badge' => 'inactive'],
];
$estadosDenuncia = [
    'recibida' => ['label' => 'Recibida', 'badge' => 'pending'],
    'en_revision' => ['label' => 'En revisión', 'badge' => 'pending'],
    'resuelta' => ['label' => 'Resuelta', 'badge' => 'active'],
    'rechazada' => ['label' => 'Rechazada', 'badge' => 'danger'],
];
$ordenEstados = ['recibida', 'en_revision', 'resuelta', 'rechazada'];

// Estadísticas
$totalDenuncias = count($denuncias);
$denunciasPendientes = count(array_filter($denuncias, function($d) { return ($d['estado'] ?? '') === 'recibida'; }));
$denunciasResueltas = count(array_filter($denuncias, function($d) { return ($d['estado'] ?? '') === 'resuelta'; }));
$denunciasHoy = count(array_filter($denuncias, function($d) { 
    return !empty($d['fecha_reporte']) && date('Y-m-d', strtotime($d['fecha_reporte'])) === date('Y-m-d'); 
}));

// Procesar acciones
$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    try {
        switch ($_POST['accion']) {
            case 'cambiar_estado':
                $denunciaId = (int)$_POST['denuncia_id'];
                $nuevoEstado = $_POST['nuevo_estado'];
                $comentario = $_POST['comentario'] ?? '';
                
                if (cambiarEstadoDenuncia($denunciaId, $nuevoEstado, $_SESSION['usuario_id'], $comentario)) {
                    $mensaje = 'Estado de la denuncia actualizado correctamente.';
                } else {
                    $error = 'Error al actualizar el estado de la denuncia.';
                }
                break;
                
            case 'asignar_responsable':
                $denunciaId = (int)$_POST['denuncia_id'];
                $responsableId = (int)$_POST['responsable_id'];
                
                if (asignarResponsableDenuncia($denunciaId, $responsableId)) {
                    $mensaje = 'Responsable asignado correctamente.';
                } else {
                    $error = 'Error al asignar responsable.';
                }
                break;
        }
        
        // Recargar denuncias después de los cambios
        $denuncias = obtenerDenunciasAdmin($filtros);
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Obtener administradores para asignación
$administradores = [];
try {
    $administradores = dbQuery("SELECT id, nombre FROM usuarios WHERE rol = 'administrador' AND estado = 'activo' ORDER BY nombre ASC")->fetchAll();
} catch (Exception $e) {
    error_log('No se pudieron obtener administradores: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de denuncias · EleccionesHN</title>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'denuncias_admin' ? 'is-active' : ''; ?>" href="denuncias_admin.php">
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
                    <h1>Gestión de denuncias</h1>
                    <span>Administración de reportes e incidencias electorales</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip chip--<?php echo $denunciasPendientes > 0 ? 'warning' : 'success'; ?>">
                            <i class="bi bi-flag"></i><?php echo $denunciasPendientes; ?> pendientes
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
                                <i class="bi bi-flag-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalDenuncias); ?></h3>
                                <p>Total denuncias</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--warning">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($denunciasPendientes); ?></h3>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($denunciasResueltas); ?></h3>
                                <p>Resueltas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($denunciasHoy); ?></h3>
                                <p>Hoy</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-bar">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="busqueda" class="form-label">Buscar denuncia</label>
                            <input type="text" name="busqueda" id="busqueda" class="form-control" 
                                   value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" 
                                   placeholder="Código o descripción">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($estadosDenuncia as $clave => $datosEstado): ?>
                                    <option value="<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo $filtroEstado === $clave ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($datosEstado['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($tiposDenuncia as $claveTipo => $configTipo): ?>
                                    <option value="<?php echo htmlspecialchars($claveTipo, ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo $filtroTipo === $claveTipo ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($configTipo['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="<?php echo htmlspecialchars($filtroFecha, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="denuncias_admin.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card table-admin">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Registro de denuncias</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaDenuncia">
                                <i class="bi bi-plus-circle me-2"></i>Nueva denuncia
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($denuncias)): ?>
                            <div class="table-responsive">
                                <table class="table table-admin mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Reportado por</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Responsable</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($denuncias as $denuncia): ?>
                                            <?php
                                                $estadoClave = $denuncia['estado'] ?? 'recibida';
                                                $estadoConfig = $estadosDenuncia[$estadoClave] ?? ['label' => ucfirst($estadoClave), 'badge' => 'secondary'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <code class="denuncia-codigo">
                                                        #<?php echo htmlspecialchars($denuncia['id'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $denuncia['tipo_votante'] === 'nacional' ? 'primary' : 'info'; ?>">
                                                        <?php echo ucfirst($denuncia['tipo_votante']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="descripcion-corta">
                                                        <strong><?php echo htmlspecialchars($denuncia['titulo'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars(substr($denuncia['descripcion'], 0, 80), ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php if (strlen($denuncia['descripcion']) > 80): ?>...<?php endif; ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($denuncia['reportado_por'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if ($denuncia['telefono']): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($denuncia['telefono'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($denuncia['fecha_reporte'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php echo htmlspecialchars($estadoConfig['badge'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlspecialchars($estadoConfig['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-muted">-</span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn-icon btn-icon--primary" title="Ver detalles" 
                                                                onclick="mostrarDetallesDenuncia(<?php echo $denuncia['id']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn-icon btn-icon--warning" title="Cambiar estado" 
                                                            onclick="cambiarEstadoDenuncia(<?php echo $denuncia['id']; ?>, '<?php echo htmlspecialchars($estadoClave, ENT_QUOTES, 'UTF-8'); ?>')">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <button class="btn-icon btn-icon--info" title="Asignar responsable" 
                                                            onclick="asignarResponsableDenuncia(<?php echo $denuncia['id']; ?>, '<?php echo htmlspecialchars((string) ($denuncia['responsable_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')">
                                                            <i class="bi bi-person-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-flag text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No hay denuncias</h4>
                                <p class="text-muted">No se encontraron denuncias con los filtros aplicados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal cambiar estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambiarEstadoLabel">Cambiar estado de denuncia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="denuncia_id" id="denuncia_id_estado">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nuevo_estado" class="form-label">Nuevo estado</label>
                            <select class="form-select" name="nuevo_estado" id="nuevo_estado" required>
                                <?php foreach ($ordenEstados as $estadoClave): ?>
                                    <option value="<?php echo htmlspecialchars($estadoClave, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($estadosDenuncia[$estadoClave]['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario (opcional)</label>
                            <textarea class="form-control" name="comentario" id="comentario" rows="3" 
                                      placeholder="Agregar comentario sobre el cambio de estado"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cambiar estado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal asignar responsable -->
    <div class="modal fade" id="modalAsignarResponsable" tabindex="-1" aria-labelledby="modalAsignarResponsableLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAsignarResponsableLabel">Asignar responsable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="accion" value="asignar_responsable">
                    <input type="hidden" name="denuncia_id" id="denuncia_id_responsable">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="responsable_id" class="form-label">Seleccionar responsable</label>
                            <select class="form-select" name="responsable_id" id="responsable_id" required>
                                <option value="0">Sin responsable asignado</option>
                                <?php foreach ($administradores as $admin): ?>
                                    <option value="<?php echo $admin['id']; ?>">
                                        <?php echo htmlspecialchars($admin['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal ver detalles de denuncia -->
    <div class="modal fade" id="modalVerDenuncia" tabindex="-1" aria-labelledby="modalVerDenunciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerDenunciaLabel">Detalles de la denuncia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoDenuncia">
                    <!-- Contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/denuncias_admin.js"></script>
</body>
</html>