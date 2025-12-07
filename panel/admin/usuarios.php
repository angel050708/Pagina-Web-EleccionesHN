<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    header('Location: ../../login.php?error=Debes iniciar sesión como administrador.');
    exit;
}

$paginaActiva = 'usuarios';

// Obtener filtros
$filtroRol = $_GET['rol'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

// Construir WHERE clause para filtros
$whereConditions = [];
$params = [];

if ($filtroRol) {
    $whereConditions[] = "u.rol = ?";
    $params[] = $filtroRol;
}

if ($filtroEstado) {
    $estadoFiltrado = $filtroEstado === 'inactivo' ? 'suspendido' : $filtroEstado;
    $whereConditions[] = "u.estado = ?";
    $params[] = $estadoFiltrado;
}

if ($busqueda) {
    $whereConditions[] = "(u.nombre LIKE ? OR u.email LIKE ? OR u.dni LIKE ?)";
    $searchTerm = "%$busqueda%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

// Obtener usuarios con información adicional
$query = "
    SELECT u.*, d.nombre as departamento_nombre, m.nombre as municipio_nombre
    FROM usuarios u
    LEFT JOIN departamentos d ON u.departamento_id = d.id
    LEFT JOIN municipios m ON u.municipio_id = m.id
    $whereClause
    ORDER BY u.creado_en DESC
";

$usuarios = [];

try {
    $usuarios = dbQuery($query, $params)->fetchAll();
} catch (PDOException $e) {
    error_log('Error al cargar usuarios: ' . $e->getMessage());
    if (empty($error)) {
        $error = 'No se pudieron cargar los usuarios. Revisa la base de datos.';
    }
}

// Obtener estadísticas
$totalUsuarios = obtenerTotalUsuarios();
$usuariosActivos = obtenerUsuariosActivos();
$votantesRegistrados = obtenerTotalVotantes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestionar usuarios · EleccionesHN</title>
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
                <a class="sidebar-link <?php echo $paginaActiva === 'usuarios' ? 'is-active' : ''; ?>" href="usuarios.php">
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
                    <h1>Gestionar usuarios</h1>
                    <span>Administración de cuentas y permisos del sistema</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip admin-chip"><i class="bi bi-shield-check"></i>Administrador</span>
                        <span class="chip"><i class="bi bi-people"></i><?php echo count($usuarios); ?> usuarios</span>
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
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalUsuarios); ?></h3>
                                <p>Total usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($usuariosActivos); ?></h3>
                                <p>Usuarios activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--info">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($votantesRegistrados); ?></h3>
                                <p>Votantes registrados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-bar">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="busqueda" class="form-label">Buscar usuario</label>
                            <input type="text" name="busqueda" id="busqueda" class="form-control" 
                                   value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" 
                                   placeholder="Nombre, email o DNI">
                        </div>
                        <div class="col-md-2">
                            <label for="rol" class="form-label">Rol</label>
                            <select name="rol" id="rol" class="form-select">
                                <option value="">Todos los roles</option>
                                <option value="votante" <?php echo $filtroRol === 'votante' ? 'selected' : ''; ?>>Votante</option>
                                <option value="administrador" <?php echo $filtroRol === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="observador" <?php echo $filtroRol === 'observador' ? 'selected' : ''; ?>>Observador</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $filtroEstado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="suspendido" <?php echo $filtroEstado === 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <a href="usuarios.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                <i class="bi bi-person-plus"></i> Nuevo
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card table-admin">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Usuarios registrados</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                <i class="bi bi-person-plus"></i> Nuevo usuario
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($usuarios)): ?>
                            <div class="table-responsive">
                                <table class="table table-admin mb-0">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Ubicación</th>
                                            <th>Registro</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            <i class="bi bi-person-circle"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <?php if (!empty($usuario['email'])): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                            <?php endif; ?>
                                                            <?php if (!empty($usuario['dni'])): ?>
                                                                <br><small class="text-muted">DNI: <?php echo htmlspecialchars($usuario['dni'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-status badge-status--<?php 
                                                        echo $usuario['rol'] === 'administrador' ? 'danger' : 
                                                            ($usuario['rol'] === 'observador' ? 'warning' : 'info'); 
                                                    ?>">
                                                        <?php echo ucfirst($usuario['rol']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['departamento_nombre']): ?>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($usuario['departamento_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php if ($usuario['municipio_nombre']): ?>
                                                                <br><?php echo htmlspecialchars($usuario['municipio_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">No especificada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php 
                                                            $fechaRegistro = $usuario['creado_en'] ?? null;
                                                            echo $fechaRegistro ? date('d/m/Y H:i', strtotime($fechaRegistro)) : 'Sin registro';
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php $estaActivo = ($usuario['estado'] ?? '') === 'activo'; ?>
                                                    <span class="badge badge-status badge-status--<?php echo $estaActivo ? 'active' : 'inactive'; ?>">
                                                        <?php echo $estaActivo ? 'Activo' : 'Suspendido'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn-icon btn-icon--primary" title="Ver detalles" onclick="mostrarDetallesUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn-icon btn-icon--warning" title="Editar" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn-icon btn-icon--<?php echo $estaActivo ? 'secondary' : 'success'; ?>" 
                                                                title="<?php echo $estaActivo ? 'Suspender' : 'Reactivar'; ?>" 
                                                                onclick="cambiarEstadoUsuario(<?php echo (int) $usuario['id']; ?>, '<?php echo $estaActivo ? 'suspendido' : 'activo'; ?>')">
                                                            <i class="bi bi-<?php echo $estaActivo ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                                            <button class="btn-icon btn-icon--danger" title="Eliminar" onclick="borrarUsuario(<?php echo $usuario['id']; ?>)">
                                                                <i class="bi bi-trash"></i>
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
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No se encontraron usuarios</h4>
                                <p class="text-muted">No hay usuarios que coincidan con los filtros aplicados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Ver Detalles Usuario -->
    <div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-labelledby="modalVerUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerUsuarioLabel">Detalles del usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesUsuarioContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarUsuario">
                    <input type="hidden" id="editar_usuario_id" name="id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editar_nombre" class="form-label">Nombre completo *</label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="editar_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_dni" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="editar_dni" name="dni" placeholder="0000-0000-00000">
                            </div>
                            <div class="col-md-6">
                                <label for="editar_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="editar_telefono" name="telefono" placeholder="0000-0000">
                            </div>
                            <div class="col-md-4">
                                <label for="editar_rol" class="form-label">Rol *</label>
                                <select class="form-select" id="editar_rol" name="rol" required>
                                    <option value="votante">Votante</option>
                                    <option value="observador">Observador</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="editar_estado" name="estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_genero" class="form-label">Género</label>
                                <select class="form-select" id="editar_genero" name="genero">
                                    <option value="">Seleccionar</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="editar_fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                            <div class="col-md-6">
                                <label for="editar_direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="editar_direccion" name="direccion">
                            </div>
                            <div class="col-md-6">
                                <label for="editar_departamento" class="form-label">Departamento</label>
                                <select class="form-select" id="editar_departamento" name="departamento_id">
                                    <option value="">Seleccionar departamento</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_municipio" class="form-label">Municipio</label>
                                <select class="form-select" id="editar_municipio" name="municipio_id">
                                    <option value="">Seleccionar municipio</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <hr class="my-3">
                                <h6 class="mb-3">Cambiar contraseña (opcional)</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_password" class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" id="editar_password" name="password">
                                <small class="text-muted">Dejar en blanco para no cambiar</small>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_password_confirm" class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control" id="editar_password_confirm" name="password_confirm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal crear usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearUsuarioLabel">Crear nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCrearUsuario">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="cedula" class="form-label">Cédula de identidad</label>
                                <input type="text" class="form-control" id="cedula" name="cedula">
                            </div>
                            <div class="col-md-6">
                                <label for="rol_nuevo" class="form-label">Rol *</label>
                                <select class="form-select" id="rol_nuevo" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="votante">Votante</option>
                                    <option value="observador">Observador</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">Confirmar contraseña *</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/usuarios.js"></script>
</body>
</html>