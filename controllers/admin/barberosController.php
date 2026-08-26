<?php
/**
 * controllers/admin/barberosController.php
 * Gestión CRUD de barberos para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

class BarberosController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Lista todos los barberos con sus estadísticas */
    public function listar(): array {
        $stmt = $this->conn->prepare(
            "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.estado, u.fecha_registro, u.ultimo_acceso,
                    COUNT(c.id_cita) AS total_citas,
                    SUM(CASE WHEN c.estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS citas_completadas
            FROM usuarios u
            LEFT JOIN citas c ON u.id_usuario = c.id_barbero
            WHERE u.id_rol = 2
            GROUP BY u.id_usuario
            ORDER BY u.nombre ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Obtiene un barbero por ID */
    public function obtener(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT u.*, r.nombre AS rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = ? AND u.id_rol = 2 LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Crea un nuevo barbero */
    public function crear(string $nombre, string $email, string $telefono, string $password): array {
        // Verificar email único
        $check = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            return ['ok' => false, 'msg' => 'El email ya está registrado.'];
        }
        $check->close();

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO usuarios (id_rol, nombre, email, telefono, password, estado) VALUES (2, ?, ?, ?, ?, 'ACTIVO')"
        );
        $stmt->bind_param('ssss', $nombre, $email, $telefono, $hash);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "CREAR_BARBERO: $email", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Barbero creado exitosamente.' : 'Error al crear el barbero.'];
    }

    /** Actualiza datos de un barbero */
    public function actualizar(int $id, string $nombre, string $telefono, string $estado): array {
        $estados = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
        if (!in_array($estado, $estados)) {
            return ['ok' => false, 'msg' => 'Estado inválido.'];
        }
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET nombre = ?, telefono = ?, estado = ? WHERE id_usuario = ? AND id_rol = 2"
        );
        $stmt->bind_param('sssi', $nombre, $telefono, $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "EDITAR_BARBERO: ID=$id", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Barbero actualizado.' : 'Error al actualizar.'];
    }

    /** Cambia estado de un barbero (activar/suspender) */
    public function cambiarEstado(int $id, string $nuevo_estado): array {
        return $this->actualizar($id, '', '', $nuevo_estado);
    }
}
