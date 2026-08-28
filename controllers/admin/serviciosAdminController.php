<?php
/**
 * controllers/admin/serviciosAdminController.php
 * Gestión CRUD de servicios para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

class ServiciosAdminController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Lista todos los servicios */
    public function listar(): array {
        $stmt = $this->conn->prepare(
            "SELECT s.*, COUNT(c.id_cita) AS total_citas
             FROM servicios s
             LEFT JOIN citas c ON s.id_servicio = c.id_servicio
             GROUP BY s.id_servicio
             ORDER BY s.nombre ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Obtiene un servicio por ID */
    public function obtener(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM servicios WHERE id_servicio = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Crea un nuevo servicio */
    public function crear(string $nombre, string $descripcion, float $precio, int $duracion, ?string $imagen = null, string $estado = 'ACTIVO'): array {
        $stmt = $this->conn->prepare(
            "INSERT INTO servicios (nombre, descripcion, precio, duracion_min, imagen, estado) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssdiss', $nombre, $descripcion, $precio, $duracion, $imagen, $estado);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "CREAR_SERVICIO: $nombre", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Servicio creado exitosamente.' : 'Error al crear el servicio.'];
    }

    /** Actualiza un servicio */
    public function actualizar(int $id, string $nombre, string $descripcion, float $precio, int $duracion, ?string $imagen = null, string $estado = 'ACTIVO'): array {
        $stmt = $this->conn->prepare(
            "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_min = ?, imagen = ?, estado = ? WHERE id_servicio = ?"
        );
        $stmt->bind_param('ssdissi', $nombre, $descripcion, $precio, $duracion, $imagen, $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "EDITAR_SERVICIO: ID=$id $nombre", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Servicio actualizado.' : 'Error al actualizar.'];
    }

    /** Elimina (soft-delete: cambia estado a INACTIVO) un servicio */
    public function desactivar(int $id): array {
        $stmt = $this->conn->prepare("UPDATE servicios SET estado = 'INACTIVO' WHERE id_servicio = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "DESACTIVAR_SERVICIO: ID=$id", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Servicio desactivado.' : 'Error.'];
    }
}
