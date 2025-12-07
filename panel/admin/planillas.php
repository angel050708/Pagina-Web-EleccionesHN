<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'planillas';

// Obtener filtros
$filtroTipo = $_GET['tipo'] ?? '';
$filtroPartido = $_GET['partido'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';
$filtroDepartamento = $_GET['departamento'] ?? '';

$filtros = [];
if ($filtroTipo) $filtros['tipo'] = $filtroTipo;
if ($filtroPartido) $filtros['partido'] = $filtroPartido;
if ($filtroEstado) $filtros['estado'] = $filtroEstado;
if ($filtroDepartamento) $filtros['departamento_id'] = $filtroDepartamento;

// Obtener planillas
$planillas = obtenerTodasLasPlanillas($filtros);

// Obtener datos para filtros
$departamentos = dbQuery("SELECT id, nombre FROM departamentos ORDER BY nombre")->fetchAll();
$partidos = dbQuery("SELECT DISTINCT partido FROM planillas ORDER BY partido")->fetchAll();

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consultar planillas · EleccionesHN</title>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'planillas' ? 'is-active' : ''; ?>" href="planillas.php">
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
                    <h1>Consultar planillas</h1>
                    <span>Gestión y administración de planillas electorales</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-list-ul"></i><?php echo count($planillas); ?> planillas</span>
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

                <div class="filter-bar">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo de planilla</label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="presidencial" <?php echo $filtroTipo === 'presidencial' ? 'selected' : ''; ?>>Presidencial</option>
                                <option value="diputados" <?php echo $filtroTipo === 'diputados' ? 'selected' : ''; ?>>Diputados</option>
                                <option value="alcaldia" <?php echo $filtroTipo === 'alcaldia' ? 'selected' : ''; ?>>Alcaldía</option>
                                <option value="vicealcaldia" <?php echo $filtroTipo === 'vicealcaldia' ? 'selected' : ''; ?>>Vicealcaldía</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="partido" class="form-label">Partido político</label>
                            <select name="partido" id="partido" class="form-select">
                                <option value="">Todos los partidos</option>
                                <?php foreach ($partidos as $partido): ?>
                                    <option value="<?php echo htmlspecialchars($partido['partido'], ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo $filtroPartido === $partido['partido'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($partido['partido'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="departamento" class="form-label">Departamento</label>
                            <select name="departamento" id="departamento" class="form-select">
                                <option value="">Todos los departamentos</option>
                                <?php foreach ($departamentos as $depto): ?>
                                    <option value="<?php echo $depto['id']; ?>" 
                                            <?php echo $filtroDepartamento == $depto['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($depto['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="planillas.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card table-admin">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Planillas registradas</h5>
                            <a class="btn btn-primary" href="crear_planilla.php">
                                <i class="bi bi-plus-circle me-2"></i>Nueva planilla
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($planillas)): ?>
                            <div class="table-responsive">
                                <table class="table table-admin mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Partido</th>
                                            <th>Nombre</th>
                                            <th>Ubicación</th>
                                            <th>Candidatos</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($planillas as $planilla): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php echo $planilla['tipo'] === 'presidencial' ? 'danger' : 'info'; ?>">
                                                        <?php echo ucfirst($planilla['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($planilla['partido'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($planilla['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php if ($planilla['departamento_nombre']): ?>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($planilla['departamento_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php if ($planilla['municipio_nombre']): ?>
                                                                <br><?php echo htmlspecialchars($planilla['municipio_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Nacional</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        <?php echo $planilla['total_candidatos']; ?> candidatos
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php echo $planilla['estado'] === 'habilitada' ? 'active' : 'inactive'; ?>">
                                                        <?php echo ucfirst($planilla['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn-icon" title="Ver detalles" onclick="verDetalles(<?php echo $planilla['id']; ?>)">
                                                            <i class="bi bi-eye text-muted"></i>
                                                        </button>
                                                        <a class="btn-icon" href="editar_planilla.php?id=<?php echo $planilla['id']; ?>" title="Editar">
                                                            <i class="bi bi-pencil text-muted"></i>
                                                        </a>
                                                        <button class="btn-icon" title="Eliminar" onclick="eliminarPlanilla(<?php echo $planilla['id']; ?>)">
                                                            <i class="bi bi-trash text-muted"></i>
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
                                <i class="bi bi-list-ul text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No hay planillas registradas</h4>
                                <p class="text-muted">No se encontraron planillas con los filtros aplicados.</p>
                                <a class="btn btn-primary" href="crear_planilla.php">
                                    <i class="bi bi-plus-circle me-2"></i>Crear primera planilla
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/planillas.js"></script>
</body>
</html>