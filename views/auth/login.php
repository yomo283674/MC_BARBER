<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../public/js/swal-custom.js"></script>
</head>
<body>
    <main class="login-page">
        <section class="login-container">
            <div class="login-image">
                <img
                    src="../../public/img/barberia.png"
                    alt="Barbería">
                <div class="image-overlay"></div>
                <!-- Partículas doradas flotantes -->
                <div class="login-image-particles" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span>
                </div>
                <div class="brand-content">
                    <div class="brand-name"></div>
                    <h1>
                        Tu estilo.<br>
                        Tu momento.
                    </h1>
                    <h2>
                        NOSOTROS
                        LO HACEMOS
                        POSIBLE
                    </h2>
                    <div class="brand-line"></div>
                </div>
            </div>

            <div class="login-form-container">
                <div class="login-form">
                    <div class="logo-container">
                        <div class="logo">
                            <img src="../../public/img/logo_corona.jpg" alt="MC Barber Logo">
                        </div>
                        <span>BARBERÍA</span>
                        <small>PREMIUM</small>
                    </div>
                    <div class="login-header">
                        <h2>Bienvenido</h2>
                        <p>
                            Inicia sesion para continuar
                        </p>
                    </div>
                    <form id="loginForm" action="../../controllers/auth/authControllers.php" method="POST">
                        <div class="input-group-custom">
                            <label for="email">
                                Email
                            </label>
                            <div class="input-container">
                                <i class="bi bi-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Tu email" autocomplete="email" required>
                            </div>
                        </div>
                        <div class="input-group-custom">
                            <label for="password">
                                Password
                            </label>
                            <div class="input-container">
                                <i class="bi bi-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Tu password" autocomplete="current-password"  required>
                                <button
                                    type="button"
                                    class="password-button"
                                    onclick="mostrarPassword()">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-options">
                            <div class="remember">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    name="remember">
                                <label for="remember">
                                    Recordarme
                                </label>
                            </div>
                            <a
                                href="recuperar.php"
                                class="forgot-password">
                                Olvidaste tu contraseña?
                            </a>
                        </div>
                        <button
                            type="submit"
                            class="btn-login"
                            id="btnSubmit">
                            <span>Iniciar Sesion</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                        <div class="divider">
                            <span></span>
                            <p>o</p>
                            <span></span>
                        </div>
                        <button type="button" class="btn-google">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                <path fill="none" d="M0 0h48v48H0z"/>
                            </svg>
                            <span>Continuar con Google</span>
                        </button>
                        <div class="signup">
                            <span>
                                No tienes una cuenta?
                            </span>
                            <a href="register.php">
                                Registrarse
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function mostrarPassword() {
            const password = document.getElementById("password");
            const icon = document.getElementById("password-icon");
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>Cargando...</span> <i class="bi bi-hourglass-split"></i>';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: data.title,
                        text: data.message,
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: data.title,
                        text: data.message,
                        confirmButtonColor: '#b58a4a'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error del servidor',
                    text: 'Ha ocurrido un error inesperado.',
                    confirmButtonColor: '#b58a4a'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>