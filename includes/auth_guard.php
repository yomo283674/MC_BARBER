<?php
/**
 * auth_guard.php
 * Middleware de proteccion de rutas por rol.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function verificarRol(array $rolesPermitidos, string $profundidad = '../../'): void {
    if (empty($_SESSION['usuario_id'])) {
        header("Location: {$profundidad}views/auth/login.php");
        exit();
    }
    $rolActual = $_SESSION['usuario_rol'] ?? '';
    if (in_array($rolActual, $rolesPermitidos, true)) { return; }
    $dashboards = [
        'CLIENTE'       => "{$profundidad}views/cliente/dashboard.php",
        'BARBERO'       => "{$profundidad}views/barbero/dashboard.php",
        'ADMINISTRADOR' => "{$profundidad}views/admin/dashboard.php",
    ];
    header("Location: " . ($dashboards[$rolActual] ?? "{$profundidad}views/auth/login.php"));
    exit();
}

function estaAutenticado(): bool { return !empty($_SESSION['usuario_id']); }
function rolActual(): string { return $_SESSION['usuario_rol'] ?? ''; }
function usuarioId(): ?int { return isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null; }
function usuarioNombre(): string { return $_SESSION['usuario_nombre'] ?? ''; }
