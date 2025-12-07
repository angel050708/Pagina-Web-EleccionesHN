<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'comprobantes';

// Obtener filtros
$filtroTipo = $_GET['tipo'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';
$filtroCentro = $_GET['centro'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

// Construir filtros para la consulta
$filtros = [];
if ($filtroTipo) $filtros['tipo'] = $filtroTipo;
if ($filtroFecha) $filtros['fecha'] = $filtroFecha;
if ($filtroCentro) $filtros['centro_votacion'] = $filtroCentro;
if ($busqueda) $filtros['busqueda'] = $busqueda;

// Obtener comprobantes
$comprobantes = obtenerComprobantesVotacion($filtros);

// Obtener datos para filtros
$centrosVotacion = [];
try {
    $centrosVotacion = dbQuery("
        SELECT DISTINCT cv.id, cv.nombre
        FROM centros_votacion cv
        INNER JOIN usuarios u ON u.centro_votacion_id = cv.id
        INNER JOIN votos v ON v.usuario_id = u.id
        ORDER BY cv.nombre ASC
    ")->fetchAll();
} catch (Exception $e) {
    error_log('No se pudieron obtener los centros de votación: ' . $e->getMessage());
}

// Estadísticas
$totalComprobantes = count($comprobantes);
$comprobantesHoy = count(array_filter($comprobantes, function($c) {
    return date('Y-m-d', strtotime($c['fecha_voto'])) === date('Y-m-d');
}));

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Comprobantes · EleccionesHN</title>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'comprobantes' ? 'is-active' : ''; ?>" href="comprobantes.php">
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
                    <h1>Comprobantes de votación</h1>
                    <span>Registro y validación de comprobantes electorales</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-receipt"></i><?php echo $totalComprobantes; ?> comprobantes</span>
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
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalComprobantes); ?></h3>
                                <p>Total comprobantes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($comprobantesHoy); ?></h3>
                                <p>Comprobantes hoy</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format(count($centrosVotacion)); ?></h3>
                                <p>Centros activos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-bar">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="busqueda" class="form-label">Buscar comprobante</label>
                            <input type="text" name="busqueda" id="busqueda" class="form-control" 
                                value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" 
                                placeholder="DNI o código">
                        </div>
                        <div class="col-md-2">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="presidencial" <?php echo $filtroTipo === 'presidencial' ? 'selected' : ''; ?>>Presidencial</option>
                                <option value="diputados" <?php echo $filtroTipo === 'diputados' ? 'selected' : ''; ?>>Diputados</option>
                                <option value="alcaldia" <?php echo $filtroTipo === 'alcaldia' ? 'selected' : ''; ?>>Alcaldía</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="<?php echo htmlspecialchars($filtroFecha, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="centro" class="form-label">Centro de votación</label>
                            <select name="centro" id="centro" class="form-select">
                                <option value="">Todos los centros</option>
                                <?php foreach ($centrosVotacion as $centro): ?>
                                    <option value="<?php echo htmlspecialchars($centro['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo (string) $filtroCentro === (string) $centro['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($centro['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="comprobantes.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card table-admin">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Registro de comprobantes</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($comprobantes)): ?>
                            <div class="table-responsive">
                                <table class="table table-admin mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Votante</th>
                                            <th>Tipo de voto</th>
                                            <th>Centro votación</th>
                                            <th>Fecha y hora</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comprobantes as $comprobante): ?>
                                            <tr>
                                                <td>
                                                    <code class="comprobante-codigo">
                                                        <?php echo htmlspecialchars($comprobante['codigo_comprobante'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($comprobante['votante_nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($comprobante['dni'] ?? 'No disponible', ENT_QUOTES, 'UTF-8'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php 
                                                        echo $comprobante['tipo_voto'] === 'presidencial' ? 'danger' : 
                                                            ($comprobante['tipo_voto'] === 'diputados' ? 'warning' : 'info'); 
                                                    ?>">
                                                        <?php echo ucfirst($comprobante['tipo_voto']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <div class="fw-medium"><?php echo htmlspecialchars($comprobante['centro_votacion'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if (!empty($comprobante['centro_ubicacion'])): ?>
                                                            <small class="text-muted d-block"><?php echo htmlspecialchars($comprobante['centro_ubicacion'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="text-muted small">
                                                        <?php echo date('d/m/Y', strtotime($comprobante['fecha_voto'])); ?>
                                                        <br><?php echo date('H:i:s', strtotime($comprobante['fecha_voto'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                        $estadoClases = [
                                                            'valido' => 'active',
                                                            'sin_codigo' => 'inactive',
                                                        ];
                                                        $estadoLabels = [
                                                            'valido' => 'Válido',
                                                            'sin_codigo' => 'Sin código',
                                                        ];
                                                        $estado = $comprobante['estado_comprobante'] ?? 'valido';
                                                        $claseEstado = $estadoClases[$estado] ?? 'inactive';
                                                        $textoEstado = $estadoLabels[$estado] ?? ucfirst($estado);
                                                    ?>
                                                    <span class="badge badge-status badge-status--<?php echo $claseEstado; ?>">
                                                        <?php echo htmlspecialchars($textoEstado, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No hay comprobantes</h4>
                                <p class="text-muted">No se encontraron comprobantes con los filtros aplicados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>