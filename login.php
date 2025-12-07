<?php
session_start();

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : null;
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
$dniRecordado = isset($_COOKIE['recuerda_dni']) ? htmlspecialchars($_COOKIE['recuerda_dni'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · EleccionesHN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand">
                <img src="imagen.php?img=cne_logo.png" alt="EleccionesHN">
                <span>EleccionesHN</span>
            </div>
            <h1 class="mb-1">Bienvenido de vuelta</h1>
            <p class="mb-4">Ingresa tu número de DNI hondureño y contraseña para continuar con el proceso electoral.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="scripts/process_login.php" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="dni" class="form-label">Número de DNI</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" class="form-control" id="dni" name="dni" inputmode="numeric" placeholder="0000-0000-00000" pattern="[0-9\-]{15}" minlength="15" maxlength="15" value="<?php echo $dniRecordado; ?>" required>
                        <div class="invalid-feedback">Ingresa un DNI con formato válido (0000-0000-00000).</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        <div class="invalid-feedback">Ingresa tu contraseña.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="rol" class="form-label">Tipo de acceso</label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="" selected disabled>Selecciona una opción</option>
                        <option value="votante">Votante</option>
                        <option value="administrador">Administrador</option>
                    </select>
                    <div class="invalid-feedback">Indica el tipo de acceso.</div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="recordarme" name="recordarme">
                        <label class="form-check-label" for="recordarme">Recordarme en este dispositivo</label>
                    </div>
                    <a class="btn btn-link p-0" href="restablecer_contrasena.php">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </form>

            <div class="auth-meta">
                <p class="mb-2">¿Primer ingreso en EleccionesHN?</p>
                <a href="registro.php">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            const dniInput = document.getElementById('dni');
            if (dniInput) {
                dniInput.addEventListener('input', function () {
                    const digits = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                    const parts = [];
                    if (digits.length > 0) {
                        parts.push(digits.slice(0, 4));
                    }
                    if (digits.length >= 5) {
                        parts.push(digits.slice(4, 8));
                    }
                    if (digits.length >= 9) {
                        parts.push(digits.slice(8, 13));
                    }
                    this.value = parts.join('-');
                });
            }
        })();
    </script>
</body>
</html>
