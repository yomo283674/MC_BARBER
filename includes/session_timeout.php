<?php
/**
 * session_timeout.php
 * Cierra la sesión automáticamente tras 30 minutos de inactividad.
 * Incluir al inicio de cada página protegida (después de auth_guard).
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

define('SESSION_TIMEOUT', 1800); // 30 minutos

if (!empty($_SESSION['usuario_id'])) {
    $ahora = time();

    if (isset($_SESSION['ultimo_actividad'])) {
        $inactivo = $ahora - $_SESSION['ultimo_actividad'];
        if ($inactivo > SESSION_TIMEOUT) {
            // Limpiar sesión
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();

            // Redirigir con parámetro de expiración para mostrar SweetAlert en login
            header('Location: ' . ($GLOBALS['base_path'] ?? '../../') . 'views/auth/login.php?expired=1');
            exit();
        }
    }

    $_SESSION['ultimo_actividad'] = $ahora;
}
