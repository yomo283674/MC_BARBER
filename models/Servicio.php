<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Servicio
 * CRUD de servicios de la barberia.
 */
class Servicio {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function obtenerActivos(): array {
        $stmt = $this->conn->prepare(
            "SELECT s.*, c.nombre AS categoria, u.nombre AS barbero
            FROM servicios s
            LEFT JOIN categorias_servicio c ON s.id_categoria = c.id_categoria
            LEFT JOIN usuarios u ON s.id_barbero = u.id_usuario
            WHERE s.estado = 'ACTIVO'
            ORDER BY s.nombre ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerTodos(): array {
        $stmt = $this->conn->prepare(
            "SELECT s.*, c.nombre AS categoria
            FROM servicios s
            LEFT JOIN categorias_servicio c ON s.id_categoria = c.id_categoria
            WHERE s.estado != 'ELIMINADO'
            ORDER BY s.estado DESC, s.nombre ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM servicios WHERE id_servicio = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerPorBarbero(int $id_barbero): array {
        $stmt = $this->conn->prepare(
            "SELECT s.*, c.nombre AS categoria
            FROM servicios s
            LEFT JOIN categorias_servicio c ON s.id_categoria = c.id_categoria
            WHERE s.id_barbero = ? AND s.estado != 'ELIMINADO'
            ORDER BY s.estado DESC, s.nombre ASC"
        );
        $stmt->bind_param('i', $id_barbero);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crear(string $nombre, string $descripcion, float $precio, int $duracion, ?int $id_categoria = null, ?string $imagen = null, ?int $id_barbero = null): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO servicios (id_categoria, id_barbero, nombre, descripcion, precio, duracion_min, imagen, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVO')"
        );
        $stmt->bind_param('iissdis', $id_categoria, $id_barbero, $nombre, $descripcion, $precio, $duracion, $imagen);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function actualizar(int $id, string $nombre, string $descripcion, float $precio, int $duracion, ?string $imagen = null): bool {
        if ($imagen) {
            $stmt = $this->conn->prepare(
                "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_min = ?, imagen = ? WHERE id_servicio = ?"
            );
            $stmt->bind_param('ssdisi', $nombre, $descripcion, $precio, $duracion, $imagen, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_min = ? WHERE id_servicio = ?"
            );
            $stmt->bind_param('ssdii', $nombre, $descripcion, $precio, $duracion, $id);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function cambiarEstado(int $id, string $estado): bool {
        $stmt = $this->conn->prepare(
            "UPDATE servicios SET estado = ? WHERE id_servicio = ?"
        );
        $stmt->bind_param('si', $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function eliminar(int $id): bool {
        try {
            $stmt = $this->conn->prepare("DELETE FROM servicios WHERE id_servicio = ?");
            $stmt->bind_param('i', $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        } catch (mysqli_sql_exception $e) {
            // 1451: Cannot delete or update a parent row: a foreign key constraint fails
            if ($e->getCode() == 1451) {
                return $this->cambiarEstado($id, 'ELIMINADO');
            }
            throw $e;
        }
    }

    /** Servicios mas solicitados con contador de citas */
    public function masSolicitados(?int $id_barbero = null, ?string $desde = null, ?string $hasta = null): array {
        $sql = "SELECT s.nombre, COUNT(c.id_cita) AS total
                FROM citas c
                INNER JOIN servicios s ON c.id_servicio = s.id_servicio
                WHERE c.estado = 'COMPLETADA'";
        $params = '';
        $values = [];

        if ($id_barbero) {
            $sql .= " AND c.id_barbero = ?";
            $params .= 'i';
            $values[] = $id_barbero;
        }
        if ($desde) {
            $sql .= " AND c.fecha >= ?";
            $params .= 's';
            $values[] = $desde;
        }
        if ($hasta) {
            $sql .= " AND c.fecha <= ?";
            $params .= 's';
            $values[] = $hasta;
        }
        $sql .= " GROUP BY s.id_servicio ORDER BY total DESC LIMIT 10";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($params, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function contarActivos(): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM servicios WHERE estado = 'ACTIVO'");
        $stmt->execute();
        $total = $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return (int)$total;
    }
}
