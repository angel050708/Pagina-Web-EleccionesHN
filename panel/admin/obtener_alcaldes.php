<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

header('Content-Type: application/json');

if (empty($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$municipioId = (int)($_GET['municipio_id'] ?? 0);
$municipioNombre = obtenerNombreMunicipioPorId($municipioId);

if (!$municipioNombre) {
    $municipioNombre = trim((string) ($_GET['municipio_nombre'] ?? ''));
}

if ($municipioId <= 0) {
    echo json_encode(['error' => 'ID de municipio no válido']);
    exit;
}

try {
    $planillas = obtenerAlcaldesPorMunicipio($municipioId);
    $imagenMunicipio = obtenerImagenPlanillaMunicipio($municipioNombre);
    
    echo json_encode([
        'success' => true,
        'planillas' => $planillas,
        'municipio' => $municipioNombre ?? '',
        'imagen' => $imagenMunicipio
    ]);
    
} catch (Exception $e) {
    error_log('Error al obtener alcaldes: ' . $e->getMessage());
    echo json_encode(['error' => 'Error al cargar candidatos']);
}
