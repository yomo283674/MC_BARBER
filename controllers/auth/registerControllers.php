<?php
session_start();
require_once __DIR__ . '/../../models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/auth/register.php');
    exit();
}

// Recoger y sanear los campos del formulario
$nombre   = trim($_POST['nombre']   ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$terminos = isset($_POST['terms']);

// Validar campos obligatorios
if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Campos incompletos',
        'text'  => 'Todos los campos son obligatorios.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}

// Validar que las contraseñas coincidan
if ($password !== $confirm) {
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Contraseñas no coinciden',
        'text'  => 'La contraseña y su confirmación deben ser iguales.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}

// Validar que el usuario aceptó los términos
if (!$terminos) {
    $_SESSION['swal'] = [
        'icon'  => 'warning',
        'title' => 'Términos y condiciones',
        'text'  => 'Debes aceptar los términos y condiciones para registrarte.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Email inválido',
        'text'  => 'Por favor ingresa un email válido.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}

// Instanciar el modelo de usuario
$usuarioModel = new Usuario();

// Verificar si el email ya está registrado
if ($usuarioModel->emailExiste($email)) {
    $_SESSION['swal'] = [
        'icon'  => 'warning',
        'title' => 'Email ya registrado',
        'text'  => 'El email ya está registrado en el sistema.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}

// Registrar el usuario (id_rol = 3 = Cliente)
$registrado = $usuarioModel->registrar($nombre, $email, $password, $telefono, 3);

if ($registrado) {
    $_SESSION['swal'] = [
        'icon'     => 'success',
        'title'    => '¡Registro Exitoso!',
        'text'     => 'Tu cuenta ha sido creada correctamente. Ahora puedes iniciar sesión.',
        'redirect' => 'login.php'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
} else {
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Error interno',
        'text'  => 'Hubo un error al crear la cuenta. Por favor, intenta de nuevo.'
    ];
    header('Location: ../../views/auth/register.php');
    exit();
}
?>
