<?php
session_start();

if (empty($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'votante') {
    header('Location: ../../login.php?error=Acceso denegado');
    exit;
}

include_once '../../includes/funciones.php';

$usuarioId = (int) $_SESSION['usuario_id'];
$resumen = obtenerResumenVotante($usuarioId);

if (!$resumen) {
    header('Location: ../../login.php?error=No se pudo cargar tu información');
    exit;
}


if (!empty($resumen['tipo_votante'])) {
    header('Location: index.php');
    exit;
}

$nombre = isset($resumen['nombre']) ? htmlspecialchars($resumen['nombre']) : 'Votante';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Tipo de Votación - Sistema Electoral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="../assets/css/seleccionar_tipo_votacion.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="display-6 fw-bold text-primary">
                        <i class="bi bi-ballot-check"></i> Sistema Electoral Honduras 2025
                    </h1>
                    <p class="lead text-muted">Selecciona tu tipo de votación</p>
                </div>

                <!-- Saludo personalizado -->
                <div class="alert alert-info text-center mb-4">
                    <i class="bi bi-person-circle fs-4"></i>
                    <h4 class="mb-1">¡Bienvenido(a), <?= $nombre ?>!</h4>
                    <p class="mb-0">Para continuar con el proceso de votación, selecciona tu ubicación:</p>
                </div>

                <!-- Formulario de selección -->
                <form method="POST" action="../../scripts/procesar_tipo_votacion.php" id="formTipoVotacion">
                    <div class="row g-4">
                        <!-- Votación Nacional -->
                        <div class="col-md-6">
                            <div class="card voting-type-card h-100" data-type="nacional">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="../../imagen.php?img=bandera-honduras.png" alt="Honduras" class="country-flag mb-3">
                                    </div>
                                    <h3 class="card-title text-primary">
                                        <i class="bi bi-geo-alt-fill"></i> Votación Nacional
                                    </h3>
                                    <p class="card-text">
                                        Voto desde territorio hondureño
                                    </p>
                                    <ul class="list-unstyled text-start">
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Centros de votación locales</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Todas las elecciones disponibles</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Diputados por departamento</li>
                                    </ul>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="radio" name="tipo_votante" value="nacional" id="nacional">
                                        <label class="form-check-label fw-bold" for="nacional">
                                            Seleccionar Votación Nacional
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Votación Internacional -->
                        <div class="col-md-6">
                            <div class="card voting-type-card h-100" data-type="internacional">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="../../imagen.php?img=bandera-usa.png" alt="Estados Unidos" class="country-flag mb-3">
                                    </div>
                                    <h3 class="card-title text-primary">
                                        <i class="bi bi-globe-americas"></i> Votación Internacional
                                    </h3>
                                    <p class="card-text">
                                        Voto desde el extranjero
                                    </p>
                                    <ul class="list-unstyled text-start">
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Consulados y embajadas</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Elección presidencial</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> Parlamento Centroamericano</li>
                                    </ul>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="radio" name="tipo_votante" value="internacional" id="internacional">
                                        <label class="form-check-label fw-bold" for="internacional">
                                            Seleccionar Votación Internacional
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de confirmación -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="btnConfirmar" disabled>
                            <i class="bi bi-arrow-right-circle"></i> Continuar
                        </button>
                    </div>
                </form>

                <!-- Información adicional -->
                <div class="mt-4">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle"></i> Información Importante
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Votación Nacional:</h6>
                                    <ul class="small">
                                        <li>Para ciudadanos que votan en Honduras</li>
                                        <li>Incluye elección presidencial, diputados y alcaldes</li>
                                        <li>Centro de votación según domicilio</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Votación Internacional:</h6>
                                    <ul class="small">
                                        <li>Para ciudadanos hondureños en el extranjero</li>
                                        <li>Solo elección presidencial y PARLACEN</li>
                                        <li>En consulados y embajadas habilitados</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/seleccionar_tipo_votacion.js"></script>
</body>
</html>