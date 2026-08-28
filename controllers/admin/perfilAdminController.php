<?php
/**
 * controllers/admin/perfilAdminController.php
 * Gestión del perfil del Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

class PerfilAdminController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Obtiene los datos del admin autenticado */
    public function obtener(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.estado, u.fecha_registro, u.ultimo_acceso, u.foto_perfil
            FROM usuarios u WHERE u.id_usuario = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Actualiza datos básicos */
    public function actualizarDatos(int $id, string $nombre, string $email, string $telefono, ?array $foto_file = null): array {
        if (empty(trim($nombre)) || empty(trim($email))) {
            return ['ok' => false, 'msg' => 'El nombre y el email son requeridos.'];
        }

        // Verificar si el email ya existe para otro usuario
        $stmt_check = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ? LIMIT 1");
        $stmt_check->bind_param('si', $email, $id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            return ['ok' => false, 'msg' => 'El correo electrónico ya está en uso por otra cuenta.'];
        }
        $stmt_check->close();

        // Manejar subida de foto
        $foto_val = null;
        if ($foto_file && $foto_file['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/../../public/uploads/perfiles/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $ext = strtolower(pathinfo($foto_file['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $permitidas)) {
                $nombre_archivo = 'perfil_' . $id . '_' . time() . '.' . $ext;
                $ruta_destino = $dir . $nombre_archivo;
                if (move_uploaded_file($foto_file['tmp_name'], $ruta_destino)) {
                    $foto_val = $nombre_archivo;
                }
            } else {
                return ['ok' => false, 'msg' => 'Formato de imagen no permitido (solo jpg, png, webp).'];
            }
        }

        if ($foto_val) {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, foto_perfil = ? WHERE id_usuario = ?"
            );
            $stmt->bind_param('ssssi', $nombre, $email, $telefono, $foto_val, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nombre = ?, email = ?, telefono = ? WHERE id_usuario = ?"
            );
            $stmt->bind_param('sssi', $nombre, $email, $telefono, $id);
        }

        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $_SESSION['usuario_nombre'] = $nombre;
            if ($foto_val) {
                $_SESSION['usuario_foto'] = $foto_val;
            }
            registrarAuditoria($this->conn, $id, "ADMIN_ACTUALIZAR_PERFIL", "EXITOSO");
        } else {
            registrarAuditoria($this->conn, $id, "ADMIN_ACTUALIZAR_PERFIL", "FALLIDO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar el perfil.'];
    }

    /** Cambia la contraseña del admin */
    public function cambiarPassword(int $id, string $actual, string $nueva, string $confirmar): array {
        if ($nueva !== $confirmar) {
            return ['ok' => false, 'msg' => 'Las contraseñas nuevas no coinciden.'];
        }
        if (strlen($nueva) < 8) {
            return ['ok' => false, 'msg' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        // Verificar contraseña actual
        $stmt = $this->conn->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($actual, $row['password'])) {
            return ['ok' => false, 'msg' => 'La contraseña actual es incorrecta.'];
        }

        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt2 = $this->conn->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
        $stmt2->bind_param('si', $hash, $id);
        $ok = $stmt2->execute();
        $stmt2->close();

        if ($ok) {
            registrarAuditoria($this->conn, $id, "ADMIN_CAMBIO_PASSWORD", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Contraseña actualizada correctamente.' : 'Error al cambiar contraseña.'];
    }
}
