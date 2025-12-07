<?php
// Servir imágenes del sistema
header('Content-Type: image/png');
header('Cache-Control: max-age=3600');
header('Pragma: cache');

// Obtener el parámetro de imagen
$imagen = $_GET['img'] ?? '';

// Lista de imágenes autorizadas
$imagenesPermitidas = [
    'cne_logo.png',
    'escudo_honduras.png',
    'logo-tse.jpg',
    'presidentes.PNG',
    'ciudadano.jpg',
    'bandera-honduras.png',
    'bandera-usa.png',
    // Departamentos
    'atlantida.PNG',
    'choloma.PNG',
    'choluteca.PNG',
    'colon.PNG',
    'comayagua.PNG',
    'copan.PNG',
    'cortes.PNG',
    'FranciscoMorazan.PNG',
    'GraciasaDios.PNG',
    'intibuca.PNG',
    'islasdelabahia.PNG',
    'juticalpa.PNG',
    'lapaz.PNG',
    'lempira.PNG',
    'ocotepeque.PNG',
    'olancho.PNG',
    'paraiso.PNG',
    'sanpedrosula.PNG',
    'santabarbara.PNG',
    'Tegucigalpa.PNG',
    'valle.PNG',
    'yoro.PNG',
    // Galería
    'galeria-1.jpeg',
    'galeria-2.PNG',
    'galeria-3.webp',
    'galeria-4.jpg',
    'galeria-5.jpg',
    'galeria-6.jpg',
    // Testimonios
    'testimonio-1.png',
    'testimonio-2.jpg',
    'testimonio-3.jpg'
];

// Verificar que la imagen esté en la lista permitida
if (!in_array($imagen, $imagenesPermitidas)) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

// Construir la ruta completa
$rutaImagen = __DIR__ . '/img/' . $imagen;

// Verificar que el archivo existe
if (!file_exists($rutaImagen)) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

// Obtener información del archivo
$infoArchivo = getimagesize($rutaImagen);
if ($infoArchivo === false) {
    http_response_code(500);
    exit('Error al procesar imagen');
}

// Establecer el tipo MIME correcto
header('Content-Type: ' . $infoArchivo['mime']);

// Obtener el tamaño del archivo
$tamanoArchivo = filesize($rutaImagen);
header('Content-Length: ' . $tamanoArchivo);

// Servir el archivo
readfile($rutaImagen);
exit;
?>