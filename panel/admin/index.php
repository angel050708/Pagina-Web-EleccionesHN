<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'inicio';
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Administrador';

// Obtener estadísticas generales
$totalVotantes = obtenerTotalVotantes();
$totalVotos = obtenerTotalVotos();
$totalPlanillas = obtenerTotalPlanillas();
$totalDenuncias = obtenerTotalDenuncias();

// Obtener actividad reciente
$votosRecientes = obtenerVotosRecientes(10);
$denunciasRecientes = obtenerDenunciasRecientes(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de administración · EleccionesHN</title>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'inicio' ? 'is-active' : ''; ?>" href="index.php">
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
                    <h1>Panel de administración</h1>
                    <span>Gestión electoral Honduras 2025</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-people"></i><?php echo number_format($totalVotantes); ?> votantes</span>
                        <span class="chip"><i class="bi bi-check-square"></i><?php echo number_format($totalVotos); ?> votos</span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <section class="hero-panel">
                    <div>
                        <h2 class="hero-title">Hola, <?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="hero-subtitle">Supervisa el proceso electoral, gestiona planillas y monitorea la actividad de votación en tiempo real.</p>
                    </div>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="crear_planilla.php"><i class="bi bi-plus-circle me-2"></i>Nueva planilla</a>
                        <a class="btn btn-soft" href="tendencias.php"><i class="bi bi-bar-chart me-2"></i>Ver tendencias</a>
                    </div>
                </section>

                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalVotantes); ?></h3>
                                <p>Votantes registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-check-square"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalVotos); ?></h3>
                                <p>Votos emitidos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalPlanillas); ?></h3>
                                <p>Planillas activas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--warning">
                                <i class="bi bi-flag"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalDenuncias); ?></h3>
                                <p>Denuncias recibidas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Actividad reciente de votación</h5>
                                    <a class="btn btn-sm btn-outline-primary" href="proceso_votacion.php">Ver todo</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($votosRecientes)): ?>
                                    <div class="activity-list">
                                        <?php foreach ($votosRecientes as $voto): ?>
                                            <div class="activity-item">
                                                <div class="activity-item__icon">
                                                    <i class="bi bi-check-circle"></i>
                                                </div>
                                                <div class="activity-item__content">
                                                    <p class="mb-1"><strong><?php echo htmlspecialchars($voto['votante_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong> votó por <strong><?php echo htmlspecialchars($voto['candidato_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                                    <small class="text-muted"><?php echo htmlspecialchars($voto['partido'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo date('d/m/Y H:i', strtotime($voto['registrado_en'])); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No hay actividad de votación reciente.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Denuncias pendientes</h5>
                                    <a class="btn btn-sm btn-outline-primary" href="denuncias_admin.php">Ver todas</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($denunciasRecientes)): ?>
                                    <div class="denuncias-list">
                                        <?php foreach ($denunciasRecientes as $denuncia): ?>
                                            <div class="denuncia-item-mini">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($denuncia['titulo'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                <p class="small text-muted mb-1"><?php echo htmlspecialchars(substr($denuncia['descripcion'], 0, 80), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($denuncia['creada_en'])); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No hay denuncias pendientes.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="cta-panel mt-4">
                    <div>
                        <h3 class="fw-semibold mb-2">Control electoral</h3>
                        <p>Gestiona todos los aspectos del proceso electoral desde este panel centralizado.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-primary" href="cierre_urnas.php">Gestionar cierre</a>
                        <a class="btn btn-soft" href="tendencias.php">Ver resultados</a>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
