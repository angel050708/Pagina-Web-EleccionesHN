<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo '<div class="alert alert-danger">Acceso denegado</div>';
    exit;
}

$denunciaId = (int)($_GET['id'] ?? 0);

if ($denunciaId <= 0) {
    echo '<div class="alert alert-warning">ID de denuncia no válido</div>';
    exit;
}

try {
    $denuncia = dbQuery(
        "SELECT d.id,
                d.usuario_id,
                d.tipo_votante,
                d.titulo,
                d.descripcion,
                d.evidencia_url,
                d.estado,
                d.creada_en,
                d.actualizada_en,
                u.nombre AS reportado_por_nombre,
                u.dni AS reportado_por_dni,
                u.email AS reportado_por_email,
                u.telefono AS telefono
         FROM denuncias_actos_irregulares d
         LEFT JOIN usuarios u ON d.usuario_id = u.id
         WHERE d.id = :id
         LIMIT 1",
        [':id' => $denunciaId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$denuncia) {
        echo '<div class="alert alert-warning">Denuncia no encontrada</div>';
        exit;
    }

    $estadosDenuncia = [
        'recibida' => 'Recibida',
        'en_revision' => 'En revisión',
        'resuelta' => 'Resuelta',
        'rechazada' => 'Rechazada',
    ];

    $estadoLabel = $estadosDenuncia[$denuncia['estado']] ?? 'Desconocido';
    ?>
    <div class="denuncia-detalle">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-muted mb-1">ID de denuncia</h6>
                <p class="mb-0"><code class="fs-5">#<?php echo htmlspecialchars($denuncia['id'], ENT_QUOTES, 'UTF-8'); ?></code></p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="text-muted mb-1">Estado</h6>
                <p class="mb-0">
                    <span class="badge bg-<?php 
                        echo $denuncia['estado'] === 'resuelta' ? 'success' : 
                            ($denuncia['estado'] === 'rechazada' ? 'danger' : 'warning'); 
                    ?> fs-6">
                        <?php echo htmlspecialchars($estadoLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </p>
            </div>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-muted mb-1">Tipo de votante</h6>
                <p class="mb-0 fw-medium">
                    <span class="badge bg-<?php echo $denuncia['tipo_votante'] === 'nacional' ? 'primary' : 'info'; ?>">
                        <?php echo ucfirst($denuncia['tipo_votante']); ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-1">Título</h6>
                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($denuncia['titulo'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <div class="mb-3">
            <h6 class="text-muted mb-1">Descripción completa</h6>
            <div class="border rounded p-3 bg-light">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($denuncia['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
        </div>

        <?php if ($denuncia['evidencia_url']): ?>
        <div class="mb-3">
            <h6 class="text-muted mb-1">Evidencia adjunta</h6>
            <?php 
            $evidenciaPath = '../../' . $denuncia['evidencia_url'];
            $extension = strtolower(pathinfo($denuncia['evidencia_url'], PATHINFO_EXTENSION));
            $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            ?>
            <?php if ($esImagen): ?>
            <div class="border rounded p-2 bg-light">
                <img src="<?php echo htmlspecialchars($evidenciaPath, ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="Evidencia" class="img-fluid" style="max-height: 400px; object-fit: contain;">
            </div>
            <?php endif; ?>
            <p class="mb-0 mt-2">
                <a href="<?php echo htmlspecialchars($evidenciaPath, ENT_QUOTES, 'UTF-8'); ?>" 
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i> Descargar evidencia
                </a>
            </p>
        </div>
        <?php endif; ?>

        <hr>

        <h6 class="text-muted mb-2">Datos del reportante</h6>
        <div class="row mb-3">
            <div class="col-md-4">
                <small class="text-muted d-block">Nombre completo</small>
                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($denuncia['reportado_por_nombre'] ?? 'No disponible', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">DNI</small>
                <p class="mb-0"><?php echo htmlspecialchars($denuncia['reportado_por_dni'] ?? 'No disponible', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Teléfono</small>
                <p class="mb-0"><?php echo htmlspecialchars($denuncia['telefono'] ?? 'No proporcionado', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <?php if ($denuncia['reportado_por_email']): ?>
        <div class="row mb-3">
            <div class="col-md-12">
                <small class="text-muted d-block">Correo electrónico</small>
                <p class="mb-0"><?php echo htmlspecialchars($denuncia['reportado_por_email'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <hr>

        <div class="row mb-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Fecha de creación</small>
                <p class="mb-0"><?php echo date('d/m/Y H:i:s', strtotime($denuncia['creada_en'])); ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Última actualización</small>
                <p class="mb-0"><?php echo date('d/m/Y H:i:s', strtotime($denuncia['actualizada_en'])); ?></p>
            </div>
        </div>

        <?php if ($denuncia['estado'] !== 'resuelta' && $denuncia['estado'] !== 'rechazada'): ?>
        <hr>
        <div class="mt-3">
            <h6 class="text-muted mb-2">Cambiar estado de denuncia</h6>
            <form id="formRespuestaDenuncia" onsubmit="enviarRespuestaDenuncia(event, <?php echo $denuncia['id']; ?>)">
                <div class="mb-3">
                    <label for="respuesta_estado" class="form-label">Actualizar estado a:</label>
                    <select class="form-select" id="respuesta_estado" name="nuevo_estado" required>
                        <option value="en_revision">En revisión</option>
                        <option value="resuelta">Resuelta</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Actualizar estado
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php

} catch (Exception $e) {
    error_log('Error al obtener denuncia: ' . $e->getMessage());
    echo '<div class="alert alert-danger">Error al cargar los detalles de la denuncia</div>';
}
