<?php
session_start();

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : null;
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de votante · EleccionesHN</title>

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
            <h1 class="mb-1">Crea tu acceso de votante</h1>
            <p class="mb-4">Completa los datos para generar tus credenciales. Conserva tu DNI con el formato oficial.</p>

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

            <form action="scripts/process_register.php" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="dni" class="form-label">Número de DNI</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" class="form-control" id="dni" name="dni" inputmode="numeric" placeholder="0000-0000-00000" pattern="[0-9\-]{15}" minlength="15" maxlength="15" required>
                        <div class="invalid-feedback">Debes ingresar un DNI válido (0000-0000-00000).</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="nombre" name="nombre" minlength="6" maxlength="120" required>
                        <div class="invalid-feedback">Ingresa tu nombre completo.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" maxlength="150" required>
                        <div class="invalid-feedback">Ingresa un correo válido. Lo usaremos para enviarte la confirmación.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="25" placeholder="+504 0000-0000" inputmode="tel" pattern="[0-9+\s-]{8,25}" required>
                        <div class="invalid-feedback">Ingresa un teléfono de contacto (entre 8 y 25 caracteres).</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="tipo_votante" class="form-label">Tipo de votante</label>
                    <select class="form-select" id="tipo_votante" name="tipo_votante" required>
                        <option value="nacional" selected>Votaré dentro de Honduras</option>
                        <option value="internacional">Resido en el extranjero y votaré en un centro consular</option>
                    </select>
                    <div class="form-text" id="tipo_votante_ayuda">Si resides fuera del país podrás elegir tu sede consular dentro del panel.</div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        <div class="invalid-feedback">Debe tener al menos 8 caracteres.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Confirma la contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
                <p class="text-muted small text-center mt-3 mb-0">Te enviaremos un correo de confirmacion para validar tu identidad.</p>
            </form>

            <div class="auth-meta">
                <p class="mb-2">¿Ya tienes cuenta?</p>
                <a href="login.php">Regresa al inicio de sesión</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const password = form.querySelector('#password');
                    const confirm = form.querySelector('#password_confirm');

                    if (password.value !== confirm.value) {
                        confirm.setCustomValidity('Las contraseñas no coinciden');
                    } else {
                        confirm.setCustomValidity('');
                    }

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

            const tipoSelect = document.getElementById('tipo_votante');
            const ayuda = document.getElementById('tipo_votante_ayuda');
            if (tipoSelect && ayuda) {
                tipoSelect.addEventListener('change', function () {
                    if (this.value === 'internacional') {
                        ayuda.textContent = 'Desde tu panel podrás elegir una de las sedes consulares en Estados Unidos de América.';
                    } else {
                        ayuda.textContent = 'Se asignarán tus datos departamentales de acuerdo a tu DNI.';
                    }
                });
            }
        })();
    </script>
</body>
</html>
