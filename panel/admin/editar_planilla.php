<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$planillaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($planillaId <= 0) {
    redirigirConMensaje('planillas.php', ['error' => 'Planilla no válida.']);
}

$planilla = obtenerPlanillaConCandidatos($planillaId);

if (!$planilla) {
    redirigirConMensaje('planillas.php', ['error' => 'La planilla seleccionada no existe.']);
}

$paginaActiva = 'planillas';

$departamentos = dbQuery('SELECT id, nombre FROM departamentos ORDER BY nombre')->fetchAll();

$formValues = [
    'tipo' => $planilla['tipo'] ?? '',
    'partido' => $planilla['partido'] ?? '',
    'nombre' => $planilla['nombre'] ?? '',
    'departamento_id' => $planilla['departamento_id'] ?? '',
    'municipio_id' => $planilla['municipio_id'] ?? '',
    'estado' => ($planilla['estado'] ?? 'habilitada') === 'inhabilitada' ? 'deshabilitada' : ($planilla['estado'] ?? 'habilitada'),
];

$candidatosFormulario = array_map(static function ($candidato) {
    return [
        'id' => isset($candidato['id']) ? (int) $candidato['id'] : null,
        'nombre' => $candidato['nombre'] ?? '',
        'cargo' => $candidato['cargo'] ?? '',
        'numero_candidato' => $candidato['numero_candidato'] ?? null,
    ];
}, $planilla['candidatos'] ?? []);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planillaIdPost = (int) ($_POST['planilla_id'] ?? 0);

    if ($planillaIdPost !== $planillaId) {
        $error = 'La referencia de la planilla no coincide.';
    } else {
        try {
            $estadoSeleccionado = ($_POST['estado'] ?? 'habilitada') === 'deshabilitada' ? 'deshabilitada' : 'habilitada';

            $datos = [
                'tipo' => trim($_POST['tipo'] ?? ''),
                'partido' => trim($_POST['partido'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'departamento_id' => !empty($_POST['departamento_id']) ? (int) $_POST['departamento_id'] : null,
                'municipio_id' => !empty($_POST['municipio_id']) ? (int) $_POST['municipio_id'] : null,
                'estado' => $estadoSeleccionado === 'deshabilitada' ? 'inhabilitada' : 'habilitada',
            ];

            $candidatos = [];
            if (!empty($_POST['candidatos']) && is_array($_POST['candidatos'])) {
                foreach ($_POST['candidatos'] as $candidato) {
                    $nombre = trim($candidato['nombre'] ?? '');

                    if ($nombre === '') {
                        continue;
                    }

                    $candidatos[] = [
                        'id' => isset($candidato['id']) && $candidato['id'] !== '' ? (int) $candidato['id'] : null,
                        'nombre' => $nombre,
                        'cargo' => trim($candidato['cargo'] ?? ''),
                        'numero_candidato' => isset($candidato['numero_candidato']) && $candidato['numero_candidato'] !== ''
                            ? (int) $candidato['numero_candidato']
                            : null,
                    ];
                }
            }

            $candidatosFormulario = $candidatos;
            $formValues = [
                'tipo' => $datos['tipo'],
                'partido' => $datos['partido'],
                'nombre' => $datos['nombre'],
                'departamento_id' => $datos['departamento_id'],
                'municipio_id' => $datos['municipio_id'],
                'estado' => $estadoSeleccionado,
            ];

            if ($datos['tipo'] === '' || $datos['partido'] === '' || $datos['nombre'] === '') {
                $error = 'Completa los campos obligatorios de la planilla.';
            } elseif (empty($candidatos)) {
                $error = 'Debes mantener al menos un candidato válido en la planilla.';
            } elseif (actualizarPlanillaCompleta($planillaId, $datos, $candidatos)) {
                redirigirConMensaje('planillas.php', ['mensaje' => 'Planilla actualizada correctamente.']);
            } else {
                $error = 'Error al actualizar la planilla. Verifica los datos proporcionados.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

$municipiosDisponibles = [];
if (!empty($formValues['departamento_id'])) {
    try {
        $municipiosDisponibles = dbQuery(
            'SELECT id, nombre FROM municipios WHERE departamento_id = ? ORDER BY nombre',
            [(int) $formValues['departamento_id']]
        )->fetchAll();
    } catch (Exception $e) {
        $municipiosDisponibles = [];
    }
}

$planillaFormConfig = [
    'municipiosUrl' => 'obtener_municipios.php',
    'initialDepartamentoId' => $formValues['departamento_id'] !== '' ? (int) $formValues['departamento_id'] : null,
    'initialMunicipioId' => $formValues['municipio_id'] !== '' ? (int) $formValues['municipio_id'] : null,
    'initialCandidatos' => array_map(static function ($candidato) {
        return [
            'id' => $candidato['id'] ?? null,
            'nombre' => $candidato['nombre'] ?? '',
            'cargo' => $candidato['cargo'] ?? '',
            'numero_candidato' => $candidato['numero_candidato'] ?? null,
        ];
    }, $candidatosFormulario),
    'isEdit' => true,
];

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar planilla · EleccionesHN</title>
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
                    <h1>Editar planilla</h1>
                    <span>Actualiza la información de la planilla seleccionada</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-list-ul"></i>#<?php echo $planillaId; ?> · <?php echo htmlspecialchars(strtoupper($formValues['tipo'] ?: 'sin tipo'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="chip"><i class="bi bi-flag"></i><?php echo htmlspecialchars($formValues['partido'] ?: 'Sin partido', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Datos de la planilla</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formEditarPlanilla">
                                    <input type="hidden" name="planilla_id" value="<?php echo $planillaId; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="tipo" class="form-label">Tipo de planilla *</label>
                                            <select class="form-select" name="tipo" id="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="presidencial" <?php echo $formValues['tipo'] === 'presidencial' ? 'selected' : ''; ?>>Presidencial</option>
                                                <option value="diputados" <?php echo $formValues['tipo'] === 'diputados' ? 'selected' : ''; ?>>Diputados</option>
                                                <option value="alcaldia" <?php echo $formValues['tipo'] === 'alcaldia' ? 'selected' : ''; ?>>Alcaldía</option>
                                                <option value="vicealcaldia" <?php echo $formValues['tipo'] === 'vicealcaldia' ? 'selected' : ''; ?>>Vicealcaldía</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="partido" class="form-label">Partido político *</label>
                                            <input type="text" class="form-control" name="partido" id="partido"
                                                   value="<?php echo htmlspecialchars($formValues['partido'], ENT_QUOTES, 'UTF-8'); ?>"
                                                   placeholder="Nombre del partido" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre de la planilla *</label>
                                        <input type="text" class="form-control" name="nombre" id="nombre"
                                               value="<?php echo htmlspecialchars($formValues['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                               placeholder="Nombre descriptivo de la planilla" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="departamento_id" class="form-label">Departamento</label>
                                            <select class="form-select" name="departamento_id" id="departamento_id">
                                                <option value="">Nacional (todos los departamentos)</option>
                                                <?php foreach ($departamentos as $depto): ?>
                                                    <option value="<?php echo $depto['id']; ?>"
                                                            <?php echo (string) $formValues['departamento_id'] === (string) $depto['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($depto['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="municipio_id" class="form-label">Municipio</label>
                                            <select class="form-select" name="municipio_id" id="municipio_id" <?php echo empty($municipiosDisponibles) ? 'disabled' : ''; ?>>
                                                <option value="">Seleccionar municipio</option>
                                                <?php foreach ($municipiosDisponibles as $municipio): ?>
                                                    <option value="<?php echo $municipio['id']; ?>"
                                                            <?php echo (string) $formValues['municipio_id'] === (string) $municipio['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($municipio['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" name="estado" id="estado">
                                            <option value="habilitada" <?php echo $formValues['estado'] === 'habilitada' ? 'selected' : ''; ?>>Habilitada</option>
                                            <option value="deshabilitada" <?php echo $formValues['estado'] === 'deshabilitada' ? 'selected' : ''; ?>>Inhabilitada</option>
                                        </select>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="bi bi-people me-2"></i>Candidatos de la planilla</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="planillaForm.agregarCandidato()">
                                            <i class="bi bi-plus"></i> Agregar candidato
                                        </button>
                                    </div>

                                    <div id="candidatos-container"></div>

                                    <div class="d-flex justify-content-end gap-3 mt-4">
                                        <a href="planillas.php" class="btn btn-outline-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check2-circle me-2"></i>Actualizar planilla
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white py-3">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Ayuda rápida</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Verifica la información antes de guardar los cambios.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Puedes agregar, editar o eliminar candidatos según sea necesario.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Los números de candidatos deben ser únicos dentro de la planilla.</li>
                                    <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Los cambios se reflejarán inmediatamente en las planillas disponibles.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header bg-warning text-dark py-3">
                                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Importante</h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-0">Si esta planilla ya tiene votos registrados, evita cambiar su tipo o eliminar candidatos existentes sin revisar las implicaciones.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.planillaFormConfig = <?php echo json_encode(
            $planillaFormConfig,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ); ?>;
    </script>
    <script src="../assets/js/crear_planilla.js"></script>
</body>
</html>
