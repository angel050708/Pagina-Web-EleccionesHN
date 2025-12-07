<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'votante') {
    header('Location: ../../login.php?error=Debes iniciar sesión como votante.');
    exit;
}

$paginaActiva = 'recibo';
$resumen = obtenerResumenVotante($_SESSION['usuario_id']);
$ubicacion = $resumen ? $resumen['ubicacion_dni'] : null;

$departamentoNombre = $resumen['departamento_nombre'] ?? ($ubicacion['departamento'] ?? 'Sin asignar');
$municipioNombre = $resumen['municipio_nombre'] ?? ($ubicacion['municipio'] ?? 'Sin asignar');
$votosRegistrados = obtenerVotosPorUsuario($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recibo de voto · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <link rel="stylesheet" href="../assets/css/recibo.css" />
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
                <a class="sidebar-link" href="index.php">
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
                <a class="sidebar-link <?php echo $paginaActiva === 'recibo' ? 'is-active' : ''; ?>" href="recibo.php">
                    <i class="bi bi-receipt"></i>
                    <span>Recibo de voto</span>
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
                    <h1>Recibo de voto</h1>
                    <span>Comprobante oficial de participación electoral</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip"><i class="bi bi-person-vcard"></i>DNI <?php echo htmlspecialchars($resumen['dni'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($departamentoNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-building"></i><?php echo htmlspecialchars($municipioNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">

                        <div class="card recibo-card mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Resumen de votos emitidos</h5>
                                    <small class="text-muted">Información oficial del Tribunal Supremo de Elecciones</small>
                                </div>
                                <a class="btn btn-outline-primary" href="#" role="button" aria-disabled="true">Descargar PDF (Próximamente)</a>
                            </div>
                            <div class="card-body">
                                <?php if ($votosRegistrados): ?>
                                    <?php foreach ($votosRegistrados as $voto): ?>
                                        <div class="recibo-entry">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="text-uppercase">Planilla <?php echo htmlspecialchars($voto['tipo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <span class="text-muted small">Registrado el <?php echo htmlspecialchars($voto['registrado_en'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <div>
                                                <span class="d-block">Partido: <strong><?php echo htmlspecialchars($voto['partido'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                                <span class="d-block">Planilla: <strong><?php echo htmlspecialchars($voto['planilla_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                                <span class="d-block">Candidato seleccionado: <strong><?php echo htmlspecialchars($voto['candidato_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong> <span class="text-muted">(<?php echo htmlspecialchars($voto['cargo'], ENT_QUOTES, 'UTF-8'); ?>)</span></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-secondary mb-0">Aún no registras votos en el sistema. Regresa al módulo de votación cuando estés listo.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <a class="btn btn-outline-secondary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Volver al inicio</a>
                            <a class="btn btn-danger" href="../../scripts/logout.php"><i class="fas fa-door-open me-2"></i>Cerrar sesión</a>
                        </div>
                    </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
