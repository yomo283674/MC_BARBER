<?php 
session_start();
require_once __DIR__ . '/../../config/database.php';
global $globalConfig;
$nombre_negocio = $globalConfig['nombre_negocio'] ?? 'MC BARBER';
$logo_url = $globalConfig['logo_url'] ?? 'public/img/logo_corona.jpg';
$logo_src = str_starts_with($logo_url, 'http') ? $logo_url : '../../' . $logo_url;

$swal = $_SESSION['swal'] ?? null;
unset($_SESSION['swal']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../public/js/swal-custom.js"></script>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <main class="register-page">
        <section class="register-container">
            <div class="register-image">
                <img
                    src="../../public/img/barberia_registro.jpg"
                    alt="Barberia Premium">
                <div class="image-overlay"></div>
                <div class="brand-content">
                    <div class="brand-name">
                    </div>
                    <h1>
                        ÚNETE A LA MEJOR<br>
                        <span>EXPERIENCIA</span><br>
                        PARA CLIENTES
                    </h1>
                    <div class="brand-line"></div>
                    <p>
                        Agenda tu cita, Barbero,
                        servicios y mucho más.
                    </p>
                </div>
                <div class="image-brand">
                </div>
            </div>
            <div class="register-form-container">
                <div class="register-form">
                    <div class="logo-container">
                        <div class="logo">
                            <img src="<?= htmlspecialchars($logo_src) ?>" alt="<?= htmlspecialchars($nombre_negocio) ?>">
                        </div>
                        <span class="logo-name">
                            <?= htmlspecialchars($nombre_negocio) ?>
                        </span>
                        <small>
                            PREMIUM
                        </small>
                    </div>

                    <div class="register-header">
                        <h2>
                            Crea Tu Cuenta
                        </h2>
                    </div>
                    <form action="../../controllers/auth/registerControllers.php" method="POST">
                        
                        <div class="input-group-custom">
                            <label for="nombre">Nombre</label>
                            <div class="input-container">
                                <i class="bi bi-person"></i>
                                <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required>
                            </div>
                        </div>
                        
                        <div class="input-group-custom">
                            <label for="gmail">Email</label>
                            <div class="input-container">
                                <i class="bi bi-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Tu email" required>
                            </div>
                        </div>

                        <div class="row-inputs">
                            <div class="input-group-custom">
                                <label for="password">
                                    Password
                                </label>
                                <div class="input-container">
                                    <i class="bi bi-lock"></i>
                                    <input type="password" id="password" name="password" placeholder="Tu password" required>
                                    <button type="button" class="password-button" onclick="mostrarPassword('password', 'icon-password')" required>
                                        <i class="bi bi-eye" id="icon-password"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="input-group-custom">
                                <label for="confirm_password">
                                    Confirmar Password
                                </label>
                                <div class="input-container">
                                    <i class="bi bi-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirma tu password" required>
                                    <button type="button" class="password-button" onclick="mostrarPassword('confirm_password', 'icon-confirm-password')">
                                        <i class="bi bi-eye" id="icon-confirm-password"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="input-group-custom">
                            <label for="telefono">
                                Telephone Number
                            </label>
                            <div class="input-container">
                                <i class="bi bi-telephone"></i>
                                <input type="tel" id="telefono" name="telefono" placeholder="Numero de telefono" autocomplete="tel" required>
                            </div>
                        </div>

                        <div class="terms">
                            <input type="checkbox" id="terms" name="terms">
                            <label for="terms">
                                Estoy de acuerdo con los
                                <a href="#">
                                    Terminos servicios
                                </a>
                                y
                                <a href="#">
                                    Politica de privacidad
                                </a>
                            </label>
                        </div>

                        <button type="submit" class="btn-register">
                            <span>
                                Crear Cuenta
                            </span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                        <div class="divider">
                            <span></span>
                            <p>o</p>
                            <span></span>
                        </div>
                        <button type="button" class="btn-google">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px"register php height="20px">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                <path fill="none" d="M0 0h48v48H0z"/>
                            </svg>
                            <span>Registrate con Google</span>
                        </button>
                        <div class="signin">
                            <span>
                                Ya tienes cuena?
                            </span>
                            <a href="login.php">
                                login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        function mostrarPassword(inputId, iconId) {
            const password =
                document.getElementById(inputId);
            const icon =
                document.getElementById(iconId);
            if (password.type === "password") {
                password.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                password.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
        document.querySelector("form").addEventListener("submit", function(event) {
            const password =
                document.getElementById("password").value;
            const confirmPassword =
                document.getElementById("confirm_password").value;
            if (password !== confirmPassword) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseñas no coinciden',
                    text: 'La contraseña y su confirmación deben ser iguales.',
                    confirmButtonColor: '#198754'
                });
            }
        });

        <?php if ($swal): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $swal['icon'] ?>',
                title: '<?= addslashes($swal['title']) ?>',
                text: '<?= addslashes($swal['text']) ?>',
                confirmButtonColor: '#198754'
            }).then(() => {
                <?php if (!empty($swal['redirect'])): ?>
                window.location.href = '<?= $swal['redirect'] ?>';
                <?php endif; ?>
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>