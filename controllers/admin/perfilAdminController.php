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
            "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.estado, u.fecha_registro, u.ultimo_acceso
             FROM usuarios u WHERE u.id_usuario = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Actualiza datos básicos */
    public function actualizarDatos(int $id, string $nombre, string $telefono): array {
        if (empty(trim($nombre))) {
            return ['ok' => false, 'msg' => 'El nombre es requerido.'];
        }
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET nombre = ?, telefono = ? WHERE id_usuario = ?"
        );
        $stmt->bind_param('ssi', $nombre, $telefono, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $_SESSION['usuario_nombre'] = $nombre;
            registrarAuditoria($this->conn, $id, "ADMIN_ACTUALIZAR_PERFIL", "EXITOSO");
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
