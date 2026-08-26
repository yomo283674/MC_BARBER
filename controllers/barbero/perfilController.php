<?php
/**
 * controllers/barbero/perfilController.php
 * Actualización de datos personales y contraseña del barbero.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

verificarRol(['BARBERO']);

$id_barbero = (int)$_SESSION['usuario_id'];
$accion     = $_POST['accion'] ?? '';
$back       = '../../views/barbero/perfil.php';

function resp(bool $ok, string $msg, string $url): void {
    $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $msg;
    header("Location: $url");
    exit();
}

switch ($accion) {

    case 'actualizar_datos':
        $nombre      = trim($_POST['nombre']      ?? '');
        $email       = trim($_POST['email']       ?? '');
        $telefono    = trim($_POST['telefono']     ?? '');
        $especialidad= trim($_POST['especialidad'] ?? '');

        if (!$nombre || !$email) {
            resp(false, 'Nombre y email son obligatorios.', $back);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            resp(false, 'El email no tiene un formato válido.', $back);
        }

        // Verificar unicidad del email (excluyendo al propio usuario)
        $chk = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ? LIMIT 1");
        $chk->bind_param('si', $email, $id_barbero);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            resp(false, 'Ese email ya está registrado por otro usuario.', $back);
        }
        $chk->close();

        // Subida de imagen
        $foto_val = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/../../public/uploads/perfiles/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $permitidas)) {
                $nombre_archivo = 'perfil_' . $id_barbero . '_' . time() . '.' . $ext;
                $ruta_destino = $dir . $nombre_archivo;
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
                    $foto_val = $nombre_archivo;
                }
            } else {
                resp(false, 'Formato de imagen no permitido (solo jpg, png, webp).', $back);
            }
        }

        if ($foto_val) {
            $stmt = $conn->prepare(
                "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, especialidad = ?, foto_perfil = ? WHERE id_usuario = ?"
            );
            $stmt->bind_param('sssssi', $nombre, $email, $telefono, $especialidad, $foto_val, $id_barbero);
        } else {
            $stmt = $conn->prepare(
                "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, especialidad = ? WHERE id_usuario = ?"
            );
            $stmt->bind_param('ssssi', $nombre, $email, $telefono, $especialidad, $id_barbero);
        }
        
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $_SESSION['usuario_nombre'] = $nombre;
            if ($foto_val) {
                $_SESSION['usuario_foto'] = $foto_val;
            }
        }

        registrarAuditoria($conn, $id_barbero, 'ACTUALIZAR_PERFIL',
            $ok ? 'EXITOSO' : 'FALLIDO', 'usuarios', "email=$email");
        resp($ok, $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar.', $back);

    case 'cambiar_password':
        $actual     = $_POST['password_actual']    ?? '';
        $nueva      = $_POST['password_nueva']     ?? '';
        $confirmar  = $_POST['password_confirmar'] ?? '';

        if (!$actual || !$nueva || !$confirmar) {
            resp(false, 'Todos los campos de contraseña son obligatorios.', $back);
        }
        if ($nueva !== $confirmar) {
            resp(false, 'Las contraseñas nuevas no coinciden.', $back);
        }
        if (strlen($nueva) < 8) {
            resp(false, 'La contraseña debe tener al menos 8 caracteres.', $back);
        }

        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id_usuario = ? LIMIT 1");
        $stmt->bind_param('i', $id_barbero);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($actual, $row['password'])) {
            registrarAuditoria($conn, $id_barbero, 'CAMBIAR_PASSWORD', 'FALLIDO', 'usuarios', 'Contraseña actual incorrecta');
            resp(false, 'La contraseña actual no es correcta.', $back);
        }

        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $upd  = $conn->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
        $upd->bind_param('si', $hash, $id_barbero);
        $ok = $upd->execute();
        $upd->close();

        registrarAuditoria($conn, $id_barbero, 'CAMBIAR_PASSWORD',
            $ok ? 'EXITOSO' : 'FALLIDO', 'usuarios', '');
        resp($ok, $ok ? 'Contraseña actualizada correctamente.' : 'Error al cambiar la contraseña.', $back);

    default:
        resp(false, 'Acción no reconocida.', $back);
}
