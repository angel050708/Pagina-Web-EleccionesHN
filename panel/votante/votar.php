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

$resumen = obtenerResumenVotante($_SESSION['usuario_id']);
if (!$resumen) {
    $resumen = array();
}


if (empty($resumen['tipo_votante'])) {
    header('Location: seleccionar_tipo_votacion.php');
    exit;
}

$paginaActiva = 'votar';
$mensaje = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Ver que votaciones ya hizo el usuario
$yaVotoPresidente = verificarSiYaVoto($_SESSION['usuario_id'], 'presidencial');
$yaVotoDiputados = verificarSiYaVoto($_SESSION['usuario_id'], 'diputados');  
$yaVotoAlcalde = verificarSiYaVoto($_SESSION['usuario_id'], 'alcalde');

// Obtener datos específicos para diputados del departamento del votante
$planillasDiputados = [];
$imagenPlanillaDiputados = '';
$maxDiputados = 0;

if (!empty($resumen['departamento_id']) && !empty($resumen['departamento_nombre'])) {
    $planillasDiputados = obtenerDiputadosPorDepartamento($resumen['departamento_id']);
    $imagenPlanillaDiputados = obtenerImagenPlanillaDepartamento($resumen['departamento_nombre']);
    $maxDiputados = (int) ($resumen['diputados_cupos'] ?? 0);
}

// Datos para votación de alcaldías basados en el municipio del votante
$municipioVotanteId = $resumen['municipio_id'] ?? null;
$municipioVotanteNombre = $resumen['municipio_nombre'] ?? '';
$imagenPlanillaAlcaldia = $municipioVotanteNombre ? obtenerImagenPlanillaMunicipio($municipioVotanteNombre) : '';
$planillasMunicipioVotante = $municipioVotanteId ? obtenerAlcaldesPorMunicipio($municipioVotanteId) : [];
$hayPlanillaAlcaldia = !empty($planillasMunicipioVotante);


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Votación Presidencial · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <link rel="stylesheet" href="../assets/css/votar.css" />
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
                <a class="sidebar-link <?php echo $paginaActiva === 'votar' ? 'is-active' : ''; ?>" href="votar.php">
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
                    <h1>Votación</h1>
                    <span>Planilla presidencial</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip"><i class="bi bi-person-vcard"></i>DNI <?php echo htmlspecialchars($resumen['dni'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-ballot-check"></i>Presidencial</span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <h1>Sistema de Votación - Elecciones 2025</h1>
                
                <!-- Votación Presidencial -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-person-badge"></i> Votación Presidencial</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($yaVotoPresidente): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Ya votaste por presidente
                                <a href="recibo.php" class="btn btn-sm btn-outline-primary ms-2">Ver recibo</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <img src="../../imagen.php?img=presidentes.PNG" alt="Planilla Presidencial" style="max-width: 100%; height: auto; cursor: pointer;" onclick="abrirVotacion('presidente')" id="planillaPresidencial">
                                <br><br>
                                <button type="button" class="btn btn-primary btn-lg" onclick="abrirVotacion('presidente')">
                                    <i class="bi bi-ballot-check"></i> Votar por Presidente
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Votación Diputados -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-people"></i> Votación Diputados - <?php echo htmlspecialchars($resumen['departamento_nombre'] ?? ''); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($yaVotoDiputados): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Ya votaste por diputados
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <?php if (!empty($imagenPlanillaDiputados)): ?>
                                <img src="../../imagen.php?img=<?php echo htmlspecialchars($imagenPlanillaDiputados); ?>" 
                                     alt="Planilla de Diputados - <?php echo htmlspecialchars($resumen['departamento_nombre'] ?? ''); ?>" 
                                     style="max-width: 100%; height: auto; cursor: pointer;" 
                                     onclick="abrirVotacion('diputados')" 
                                     id="planillaDiputados">
                                <br><br>
                                <?php endif; ?>
                                <p class="mb-3">Candidatos a diputados por <?php echo htmlspecialchars($resumen['departamento_nombre'] ?? ''); ?></p>
                                <button type="button" class="btn btn-primary btn-lg" onclick="abrirVotacion('diputados')">
                                     Votar por Diputados
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Votación Alcaldía -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-building"></i> Votación Alcaldía</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($yaVotoAlcalde): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Ya votaste por alcalde
                            </div>
                        <?php else: ?>
                            <?php if (!$municipioVotanteId): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No encontramos un municipio asignado en tu perfil. Por favor contacta al administrador.
                                </div>
                            <?php elseif (!$hayPlanillaAlcaldia): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Aún no hay planillas de alcaldía disponibles para <?php echo htmlspecialchars($municipioVotanteNombre ?? 'tu municipio', ENT_QUOTES, 'UTF-8'); ?>.
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <?php if (!empty($imagenPlanillaAlcaldia)): ?>
                                     <img src="../../imagen.php?img=<?php echo htmlspecialchars($imagenPlanillaAlcaldia, ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="Planilla <?php echo htmlspecialchars($municipioVotanteNombre, ENT_QUOTES, 'UTF-8'); ?>"
                                         style="max-width: 100%; height: auto; max-height: 220px; object-fit: contain; cursor: pointer;"
                                         onclick="abrirVotacionAlcalde(<?php echo (int) $municipioVotanteId; ?>, <?php echo htmlspecialchars(json_encode($municipioVotanteNombre, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <br><br>
                                    <?php endif; ?>
                                    <p class="mb-3">Candidatos a alcalde de <?php echo htmlspecialchars($municipioVotanteNombre, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <button type="button" class="btn btn-primary btn-lg"
                                            onclick="abrirVotacionAlcalde(<?php echo (int) $municipioVotanteId; ?>, <?php echo htmlspecialchars(json_encode($municipioVotanteNombre, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="bi bi-building"></i> Votar por Alcalde
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modal para votación presidencial -->
                <div class="modal fade" id="modalVotacionPresidente" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content modal-planilla">
                            <div class="modal-header">
                                <h5 class="modal-title">Selecciona tu candidato presidencial</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <div class="planilla-container">
                                        <img src="../../imagen.php?img=presidentes.PNG" alt="Planilla Presidencial" class="planilla-image">
                                        <button type="button" class="planilla-opcion" data-id="1" style="--left:8%; --width:18%; --top:74%; --height:20%;" aria-label="Seleccionar candidato 1"></button>
                                        <button type="button" class="planilla-opcion" data-id="2" style="--left:26%; --width:18%; --top:74%; --height:20%;" aria-label="Seleccionar candidato 2"></button>
                                        <button type="button" class="planilla-opcion" data-id="3" style="--left:43.5%; --width:18%; --top:74%; --height:20%;" aria-label="Seleccionar candidato 3"></button>
                                        <button type="button" class="planilla-opcion" data-id="4" style="--left:61%; --width:18%; --top:74%; --height:20%;" aria-label="Seleccionar candidato 4"></button>
                                        <button type="button" class="planilla-opcion" data-id="5" style="--left:78.5%; --width:18%; --top:74%; --height:20%;" aria-label="Seleccionar candidato 5"></button>
                                    </div>
                                </div>
                                <form method="POST" action="../../scripts/procesar_voto.php" id="formVotacionPresidente">
                                    <input type="hidden" name="tipo_voto" value="presidencial">
                                    <input type="hidden" name="candidato_id" id="candidatoSeleccionadoPresidente">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-lg" id="btnConfirmarVotoPresidente" disabled>
                                            <i class="bi bi-check-circle"></i> Confirmar Voto
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para votación diputados -->
                <div class="modal fade" id="modalVotacionDiputados" tabindex="-1" data-max-diputados="<?php echo $maxDiputados; ?>">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content modal-planilla">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-people"></i> Candidatos a Diputados - <?php echo htmlspecialchars($resumen['departamento_nombre'] ?? ''); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (empty($planillasDiputados)): ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <p>No hay candidatos a diputados disponibles para tu departamento en este momento.</p>
                                </div>
                                <?php else: ?>
                                
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Instrucciones:</strong> Selecciona los partidos de tu preferencia para ver sus candidatos.
                                    Puedes elegir hasta <strong><?php echo $maxDiputados; ?> diputados</strong> en total.
                                </div>

                                <div class="row g-3 mb-4">
                                    <?php foreach ($planillasDiputados as $planilla): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 partido-card" role="button" 
                                             data-bs-toggle="modal" data-bs-target="#modalCandidatos<?php echo $planilla['planilla_id']; ?>">
                                            <div class="card-body text-center">
                                                <h6 class="card-title fw-bold"><?php echo htmlspecialchars($planilla['partido']); ?></h6>
                                                <p class="card-text text-muted small">
                                                    <?php echo count($planilla['candidatos']); ?> candidatos disponibles
                                                </p>
                                                <div class="mt-2">
                                                    <i class="bi bi-eye text-primary"></i>
                                                    <span class="small text-primary">Ver candidatos</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <form method="POST" action="../../scripts/procesar_voto.php" id="formVotacionDiputados">
                                    <input type="hidden" name="tipo_voto" value="diputados">
                                    <input type="hidden" name="candidatos_seleccionados" id="candidatosSeleccionados" value="">
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <span id="contadorSeleccionados">0</span> de <?php echo $maxDiputados; ?> seleccionados
                                        </div>
                                        <button type="submit" class="btn btn-success" id="btnConfirmarVotoDiputados" disabled>
                                            <i class="bi bi-check-circle"></i> Confirmar Voto por Diputados
                                        </button>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modales de candidatos por partido -->
                <?php foreach ($planillasDiputados as $planilla): ?>
                <div class="modal fade" id="modalCandidatos<?php echo $planilla['planilla_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title">
                                    <i class="bi bi-person-check"></i> 
                                    Candidatos - <?php echo htmlspecialchars($planilla['partido']); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">
                                    Selecciona los candidatos de tu preferencia (máximo <?php echo $maxDiputados; ?>):
                                </p>
                                
                                <div class="list-group">
                                    <?php foreach ($planilla['candidatos'] as $index => $candidato): ?>
                                    <label class="list-group-item d-flex align-items-center">
                                        <input class="form-check-input me-3 candidato-checkbox" 
                                               type="checkbox" 
                                               name="candidato_<?php echo $planilla['planilla_id']; ?>_<?php echo $index; ?>"
                                               value="<?php echo htmlspecialchars($candidato); ?>"
                                               data-partido="<?php echo htmlspecialchars($planilla['partido']); ?>"
                                               data-planilla="<?php echo $planilla['planilla_id']; ?>">
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($candidato); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($planilla['partido']); ?> - Candidato <?php echo ($index + 1); ?></small>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-secondary" onclick="volverASeleccionPartido(<?php echo $planilla['planilla_id']; ?>)">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Modal para votación alcalde -->
                <div class="modal fade" id="modalVotacionAlcalde" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content modal-planilla">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalAlcalde">Candidatos a Alcalde</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="contenidoModalAlcalde">
                                <p class="text-center text-muted">Cargando candidatos...</p>
                            </div>
                        </div>
                    </div>
                </div>


            </main>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-primary">
                        <i class="bi bi-shield-check"></i>
                        Confirmar Votación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-ballot-check display-4 text-primary"></i>
                    </div>
                    <h6>Has seleccionado votar por:</h6>
                    <div class="candidate-selected p-3 bg-light rounded mt-2 mb-3" id="candidatoSeleccionado">
                        <!-- Se llena dinámicamente -->
                    </div>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>IMPORTANTE:</strong> Una vez confirmado tu voto, no podrá ser modificado.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left"></i> Revisar
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmarVoto">
                        <i class="bi bi-check-circle"></i> Confirmar Voto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/votar.js"></script>
    <script>

    </script>
</body>
</html>
