<?php
require_once __DIR__ . '/../config/database.php';

class Turno {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /** Obtiene el turno del cliente para hoy */
    public function obtenerTurnoHoy(int $id_cliente): ?array {
        $hoy = date('Y-m-d');
        $stmt = $this->conn->prepare(
            "SELECT t.*, c.hora, c.estado AS estado_cita,
                    s.nombre AS servicio, u.nombre AS barbero
            FROM turnos t
            INNER JOIN citas c ON t.id_cita = c.id_cita
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            INNER JOIN usuarios u  ON c.id_barbero  = u.id_usuario
            WHERE t.id_cliente = ? AND t.fecha = ?
            AND t.estado != 'FINALIZADO' AND c.estado NOT IN ('COMPLETADA', 'CANCELADA')
            ORDER BY t.posicion ASC
            LIMIT 1"
        );
        $stmt->bind_param('is', $id_cliente, $hoy);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Cuenta cuantas personas estan antes en la cola */
    public function personasAntes(int $posicion, string $fecha): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM turnos
            WHERE fecha = ? AND posicion < ? AND estado IN ('EN_ESPERA','EN_ATENCION')"
        );
        $stmt->bind_param('si', $fecha, $posicion);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return (int)$total;
    }

    /** Obtiene todos los turnos del dia para el barbero */
    public function turnosDia(string $fecha): array {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nombre AS cliente, c.hora, s.nombre AS servicio
            FROM turnos t
            INNER JOIN usuarios u ON t.id_cliente = u.id_usuario
            INNER JOIN citas c    ON t.id_cita    = c.id_cita
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            WHERE t.fecha = ?
            ORDER BY t.posicion ASC"
        );
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function actualizarEstado(int $id_turno, string $estado): bool {
        $stmt = $this->conn->prepare(
            "UPDATE turnos SET estado = ?, fecha_actualizacion = NOW() WHERE id_turno = ?"
        );
        $stmt->bind_param('si', $estado, $id_turno);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
