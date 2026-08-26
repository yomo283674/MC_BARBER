<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

// Recoger y sanear los campos del formulario
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// Validar campos obligatorios
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'title' => 'Campos incompletos', 'message' => 'El email y la contraseña son obligatorios.']);
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'title' => 'Email inválido', 'message' => 'Por favor ingresa un email válido.']);
    exit();
}

// Buscar el usuario en la base de datos
$usuarioModel = new Usuario();
$usuario = $usuarioModel->buscarPorEmail($email);

// Verificar si el usuario existe y la contraseña es correcta
if (!$usuario || !password_verify($password, $usuario['password'])) {
    echo json_encode(['success' => false, 'title' => 'Credenciales incorrectas', 'message' => 'El email o la contraseña son incorrectos.']);
    exit();
}

// Verificar que la cuenta esté activa
if ($usuario['estado'] !== 'ACTIVO') {
    $mensajeEstado = match($usuario['estado']) {
        'INACTIVO'   => 'Tu cuenta está inactiva. Contacta al administrador.',
        'SUSPENDIDO' => 'Tu cuenta ha sido suspendida. Contacta al administrador.',
        default      => 'Tu cuenta no está disponible en este momento.',
    };
    echo json_encode(['success' => false, 'title' => 'Cuenta no disponible', 'message' => $mensajeEstado]);
    exit();
}

// Guardar datos de sesion
$_SESSION['usuario_id']     = $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email']  = $usuario['email'];
$_SESSION['usuario_rol']    = $usuario['rol'];
$_SESSION['usuario_id_rol'] = $usuario['id_rol'];
$_SESSION['usuario_foto']   = $usuario['foto_perfil'] ?? null;

// Actualizar ultimo acceso
$usuarioModel->actualizarUltimoAcceso($usuario['id_usuario']);

// Redirigir segun el rol del usuario
$redireccion = match((int)$usuario['id_rol']) {
    1 => '../../views/admin/dashboard.php',
    2 => '../../views/barbero/dashboard.php',
    3 => '../../views/cliente/dashboard.php',
    default => '../../views/auth/login.php',
};

echo json_encode([
    'success' => true,
    'title' => '¡Bienvenido!',
    'message' => 'Hola ' . htmlspecialchars($usuario['nombre']) . ', has iniciado sesión correctamente.',
    'redirect' => $redireccion
]);
exit();
