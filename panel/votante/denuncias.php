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

$paginaActiva = 'denuncias';
$mensaje = isset($_GET['success']) ? htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') : null;
$alerta = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : null;

$resumen = obtenerResumenVotante($_SESSION['usuario_id']);
if (!$resumen) {
    $resumen = array();
}

// Obtener denuncias del usuario
$denuncias = obtenerDenunciasPorUsuario($_SESSION['usuario_id']);
$totalDenuncias = count($denuncias);

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
    <title>Denuncias de Irregularidades · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <style>
        .denuncia-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .denuncia-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
        }
        .priority-high {
            border-left: 4px solid #dc3545;
        }
        .priority-medium {
            border-left: 4px solid #ffc107;
        }
        .priority-low {
            border-left: 4px solid #28a745;
        }
    </style>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'denuncias' ? 'is-active' : ''; ?>" href="denuncias.php">
                    <i class="bi bi-flag"></i>
                    <span>Denuncias</span>
                </a>
                <a class="sidebar-link" href="votar.php">
                    <i class="bi bi-check2-square"></i>
                    <span>Realizar votación</span>
                </a>
                <a class="sidebar-link" href="recibo.php">
                    <i class="bi bi-receipt"></i>
                    <span>Recibo de voto</span>
                </a>
            </nav>
            <div class="sidebar-footer">Proceso electoral 2025</div>
        </aside>

        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-context">
                    <h1>Denuncias</h1>
                    <span>Reportes de irregularidades electorales</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip"><i class="bi bi-person-vcard"></i>DNI <?php echo htmlspecialchars($resumen['dni'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-flag"></i><?php echo $totalDenuncias; ?> denuncias</span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">

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

                <!-- Formulario para nueva denuncia -->
                <div class="denuncia-form">
                    <h3 class="mb-3">
                        <i class="bi bi-plus-circle text-primary"></i>
                        Nueva Denuncia
                    </h3>
                    
                    <form method="POST" action="../../scripts/procesar_denuncia.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">
                                <i class="bi bi-pencil"></i> Título de la denuncia
                            </label>
                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                   placeholder="Breve resumen de la irregularidad" required maxlength="180">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="bi bi-chat-text"></i> Descripción detallada
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                                      placeholder="Describe detalladamente lo que observaste..." required></textarea>
                            <div class="form-text">
                                Incluye todos los detalles relevantes: lugar, fecha, personas involucradas, acciones observadas, etc.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="evidencia" class="form-label">
                                <i class="bi bi-camera"></i> Evidencia (opcional)
                            </label>
                            <input type="file" class="form-control" id="evidencia" name="evidencia" 
                                   accept="image/*,video/*,.pdf">
                            <div class="form-text">
                                Puedes adjuntar foto, video o documento. Máximo 10MB.
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Enviar Denuncia
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de denuncias anteriores -->
                <div class="section-card">
                    <div class="section-card__header">
                        <h3 class="section-card__title">
                            <i class="bi bi-list-ul"></i>
                            Mis Denuncias (<?php echo $totalDenuncias; ?>)
                        </h3>
                    </div>
                    <div class="section-card__body">
                        <?php if (empty($denuncias)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-3">No has registrado ninguna denuncia aún.</p>
                                <p class="small text-muted">Usa el formulario anterior para reportar cualquier irregularidad.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($denuncias as $denuncia): ?>
                                <?php
                                $estado = isset($denuncia['estado']) ? $denuncia['estado'] : 'recibida';
                                $estadoInfo = isset($mapaEstadosDenuncia[$estado]) ? $mapaEstadosDenuncia[$estado] : $mapaEstadosDenuncia['recibida'];
                                ?>
                                <div class="denuncia-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-fill">
                                            <h5 class="mb-1">
                                                <i class="bi bi-flag text-danger"></i>
                                                <?php echo htmlspecialchars($denuncia['titulo'] ?? 'Denuncia'); ?>
                                            </h5>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-hash"></i> ID: <strong><?php echo $denuncia['id']; ?></strong>
                                            </p>
                                            <p class="text-muted small">
                                                <i class="bi bi-calendar"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($denuncia['creada_en'])); ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="status-chip <?php echo $estadoInfo['class']; ?>">
                                                <?php echo $estadoInfo['label']; ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <span class="badge bg-<?php echo $denuncia['tipo_votante'] === 'nacional' ? 'primary' : 'info'; ?>">
                                                    <?php echo ucfirst($denuncia['tipo_votante']); ?>
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="mb-0 small">
                                            <?php echo nl2br(htmlspecialchars($denuncia['descripcion'])); ?>
                                        </p>
                                    </div>

                                    <?php if ($denuncia['evidencia_url']): ?>
                                        <div class="mt-2">
                                            <a href="../../<?php echo htmlspecialchars($denuncia['evidencia_url']); ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-image"></i> Ver evidencia
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/denuncias.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>