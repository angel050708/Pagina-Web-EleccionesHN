<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'votante') {
    header('Location: ../../login.php?error=Debes iniciar sesión como votante.');
    exit;
}

$paginaActiva = 'datos';
$resumen = obtenerResumenVotante($_SESSION['usuario_id']);
$ubicacion = $resumen ? $resumen['ubicacion_dni'] : null;

$departamentoNombre = $resumen['departamento_nombre'] ?? ($ubicacion['departamento'] ?? 'Sin asignar');
$municipioNombre = $resumen['municipio_nombre'] ?? ($ubicacion['municipio'] ?? 'Sin asignar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Datos personales · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <link rel="stylesheet" href="../assets/css/datos.css" />
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
                <a class="sidebar-link <?php echo $paginaActiva === 'datos' ? 'is-active' : ''; ?>" href="datos.php">
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
                    <h1>Datos personales</h1>
                    <span>Información de identificación y ubicación</span>
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

                        <?php if ($resumen): ?>
                            <div class="row g-4">
                                <div class="col-xl-7">
                                    <div class="card datos-card">
                                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Información personal</h5>
                                                <small class="text-muted">Última actualización: <?php echo htmlspecialchars($resumen['actualizado_en'] ?? 'No disponible', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary text-uppercase">Votante</span>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <span class="text-muted">Nombre completo</span>
                                                    <strong><?php echo htmlspecialchars($resumen['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">DNI</span>
                                                    <strong><?php echo htmlspecialchars($resumen['dni'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">Correo electrónico</span>
                                                    <strong><?php echo htmlspecialchars($resumen['email'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">Teléfono</span>
                                                    <strong><?php echo htmlspecialchars($resumen['telefono'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">Dirección de residencia</span>
                                                    <strong><?php echo htmlspecialchars($resumen['direccion_residencia'] ?? 'No registrada', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">Departamento / Municipio</span>
                                                    <strong><?php echo htmlspecialchars($departamentoNombre, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($municipioNombre, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-muted">Centro de votación</span>
                                                    <strong><?php echo htmlspecialchars($resumen['centro_nombre'] ?? 'Pendiente de asignación', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5">
                                    <div class="card datos-card h-100">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Situación electoral</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <span class="text-muted d-block">Estado del padrón</span>
                                                <span class="badge <?php echo !empty($resumen['habilitado']) ? 'bg-success' : 'bg-warning text-dark'; ?> text-uppercase">
                                                    <?php echo !empty($resumen['habilitado']) ? 'Habilitado' : 'Pendiente'; ?>
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="text-muted d-block">Fecha de verificación</span>
                                                <strong><?php echo htmlspecialchars($resumen['fecha_verificacion'] ?? 'En proceso', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                            <div class="mb-3">
                                                <span class="text-muted d-block">Último acceso</span>
                                                <strong><?php echo htmlspecialchars($resumen['ultimo_acceso'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                            <div class="mb-3">
                                                <span class="text-muted d-block">Cupos de diputados</span>
                                                <strong><?php echo !empty($resumen['diputados_cupos']) ? (int) $resumen['diputados_cupos'] : 'No determinado'; ?></strong>
                                            </div>
                                            <hr>
                                            <h6 class="text-muted text-uppercase">Historial</h6>
                                            <ul class="timeline mt-3">
                                                <li>
                                                    <strong>Creación de cuenta</strong>
                                                    <div class="text-muted small">Registrado el <?php echo htmlspecialchars($resumen['creado_en'] ?? 'Fecha no disponible', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </li>
                                                <li>
                                                    <strong>Verificación de identidad</strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($resumen['fecha_verificacion'] ?? 'Pendiente', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </li>
                                                <li>
                                                    <strong>Última actualización</strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($resumen['actualizado_en'] ?? 'Fecha no disponible', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">No se encontraron datos asociados a tu cuenta.</div>
                        <?php endif; ?>
                    </div>
                </main>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
