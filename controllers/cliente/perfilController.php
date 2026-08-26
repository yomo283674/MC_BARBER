<?php
/**
 * controllers/cliente/perfilController.php
 * Controlador para actualizar el perfil del cliente.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
verificarRol(['CLIENTE'], $base_path);
require_once $base_path . 'models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = (int)$_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nombre) || empty($email) || empty($telefono)) {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_msg'] = 'Todos los campos excepto la contraseña son obligatorios.';
        header("Location: {$base_path}views/cliente/perfil.php");
        exit;
    }

    $usuarioModel = new Usuario();

    // Check if the email exists but belongs to someone else
    $usuarioExistente = $usuarioModel->buscarPorEmail($email);
    if ($usuarioExistente && (int)$usuarioExistente['id_usuario'] !== $id_usuario) {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_msg'] = 'El correo electrónico ya está en uso por otra cuenta.';
        header("Location: {$base_path}views/cliente/perfil.php");
        exit;
    }

    // Manejo de la foto de perfil
    $foto_perfil_nombre = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_perfil']['tmp_name'];
        $file_name = $_FILES['foto_perfil']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $extensiones_permitidas)) {
            $upload_dir = $base_path . 'public/uploads/perfiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generar nombre unico
            $nuevo_nombre = 'perfil_' . $id_usuario . '_' . time() . '.' . $file_ext;
            
            if (move_uploaded_file($file_tmp, $upload_dir . $nuevo_nombre)) {
                $foto_perfil_nombre = $nuevo_nombre;
                
                // Eliminar foto anterior si existe
                $datos_antiguos = $usuarioModel->obtenerPorId($id_usuario);
                if (!empty($datos_antiguos['foto_perfil']) && file_exists($upload_dir . $datos_antiguos['foto_perfil'])) {
                    unlink($upload_dir . $datos_antiguos['foto_perfil']);
                }
            } else {
                $_SESSION['flash_tipo'] = 'error';
                $_SESSION['flash_msg'] = 'Hubo un error al subir la imagen.';
                header("Location: {$base_path}views/cliente/perfil.php");
                exit;
            }
        } else {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_msg'] = 'Formato de imagen no válido. Usa JPG, PNG o WEBP.';
            header("Location: {$base_path}views/cliente/perfil.php");
            exit;
        }
    }

    // Actualizar perfil
    $pwd = empty($password) ? null : $password;
    $exito = $usuarioModel->actualizarPerfil($id_usuario, $nombre, $email, $telefono, $pwd, $foto_perfil_nombre);

    if ($exito) {
        // Update session name if changed
        $_SESSION['usuario_nombre'] = $nombre;
        if ($foto_perfil_nombre) {
            $_SESSION['usuario_foto'] = $foto_perfil_nombre;
        }
        
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_msg'] = 'Tu perfil se ha actualizado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_msg'] = 'Hubo un error al actualizar el perfil. Inténtalo de nuevo.';
    }

    header("Location: {$base_path}views/cliente/perfil.php");
    exit;
} else {
    header("Location: {$base_path}views/cliente/perfil.php");
    exit;
}
