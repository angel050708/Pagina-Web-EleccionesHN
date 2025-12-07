<?php
session_start();

include_once '../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel/votante/denuncias.php?error=Acceso no permitido');
    exit;
}

if (empty($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'votante') {
    header('Location: ../login.php?error=Debes iniciar sesión como votante');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

// Validar datos del formulario
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$tipoVotante = isset($_SESSION['tipo_votante']) ? $_SESSION['tipo_votante'] : 'nacional';

// Validaciones
if (empty($titulo)) {
    header('Location: ../panel/votante/denuncias.php?error=Proporciona un título para la denuncia');
    exit;
}

if (empty($descripcion)) {
    header('Location: ../panel/votante/denuncias.php?error=Proporciona una descripción detallada');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Insertar la denuncia
    $sql = 'INSERT INTO denuncias_actos_irregulares (usuario_id, tipo_votante, titulo, descripcion, estado, creada_en)
            VALUES (:usuario_id, :tipo_votante, :titulo, :descripcion, :estado, NOW())';
    
    dbQuery($sql, [
        ':usuario_id' => $usuarioId,
        ':tipo_votante' => $tipoVotante,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':estado' => 'recibida'
    ]);

    $denunciaId = $pdo->lastInsertId();

    // Procesar archivos adjuntos si los hay
    if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/denuncias/';
        
        // Crear directorio si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $_FILES['evidencia']['name'];
        $fileSize = $_FILES['evidencia']['size'];
        $fileTmp = $_FILES['evidencia']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validar extensión
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4', 'avi', 'mov'];
        if (in_array($fileExt, $allowedExt) && $fileSize <= 10 * 1024 * 1024) {
            // Generar nombre único
            $newFileName = $denunciaId . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Actualizar la URL de evidencia
                dbQuery('UPDATE denuncias_actos_irregulares SET evidencia_url = :url WHERE id = :id', [
                    ':url' => 'uploads/denuncias/' . $newFileName,
                    ':id' => $denunciaId
                ]);
            }
        }
    }

    $pdo->commit();

    header("Location: ../panel/votante/denuncias.php?success=" . urlencode("Denuncia registrada exitosamente. Número de seguimiento: #{$denunciaId}"));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error en procesar_denuncia.php: " . $e->getMessage());
    header('Location: ../panel/votante/denuncias.php?error=Error al procesar la denuncia. Inténtalo de nuevo.');
    exit;
}
?>