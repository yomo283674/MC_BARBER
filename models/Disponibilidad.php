<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Disponibilidad
 * Gestiona los horarios disponibles de cada barbero.
 */
class Disponibilidad {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Obtiene los slots disponibles para un barbero en una fecha.
     * Excluye los horarios que ya tienen cita activa.
     */
    public function obtenerDisponibles(int $id_barbero, string $fecha): array {
        $stmt = $this->conn->prepare(
            "SELECT d.*
            FROM disponibilidad d
            WHERE d.id_barbero = ?
            AND d.fecha = ?
            AND d.disponible = 1
            AND NOT EXISTS (
                SELECT 1 FROM citas c
                WHERE c.id_barbero = d.id_barbero
                    AND c.fecha = d.fecha
                    AND c.hora = d.hora_inicio
                    AND c.estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')
            )
            ORDER BY d.hora_inicio ASC"
        );
        $stmt->bind_param('is', $id_barbero, $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene toda la disponibilidad de un barbero para una fecha (incluye bloqueados).
     */
    public function obtenerPorBarberoFecha(int $id_barbero, string $fecha): array {
        $stmt = $this->conn->prepare(
            "SELECT d.*, 
                    (SELECT COUNT(*) FROM citas c 
                    WHERE c.id_barbero = d.id_barbero 
                    AND c.fecha = d.fecha 
                    AND c.hora = d.hora_inicio
                    AND c.estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')) AS tiene_cita
            FROM disponibilidad d
            WHERE d.id_barbero = ? AND d.fecha = ?
            ORDER BY d.hora_inicio ASC"
        );
        $stmt->bind_param('is', $id_barbero, $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Activa o crea un slot de disponibilidad.
     */
    public function activar(int $id_barbero, string $fecha, string $hora_inicio, string $hora_fin): bool {
        // Verificar si ya existe
        $stmt = $this->conn->prepare(
            "SELECT id_disponibilidad FROM disponibilidad
            WHERE id_barbero = ? AND fecha = ? AND hora_inicio = ? LIMIT 1"
        );
        $stmt->bind_param('iss', $id_barbero, $fecha, $hora_inicio);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existe) {
            $stmt2 = $this->conn->prepare(
                "UPDATE disponibilidad SET disponible = 1, hora_fin = ? WHERE id_disponibilidad = ?"
            );
            $stmt2->bind_param('si', $hora_fin, $existe['id_disponibilidad']);
            $ok = $stmt2->execute();
            $stmt2->close();
            return $ok;
        }

        $stmt3 = $this->conn->prepare(
            "INSERT INTO disponibilidad (id_barbero, fecha, hora_inicio, hora_fin, disponible)
            VALUES (?, ?, ?, ?, 1)"
        );
        $stmt3->bind_param('isss', $id_barbero, $fecha, $hora_inicio, $hora_fin);
        $ok = $stmt3->execute();
        $stmt3->close();
        return $ok;
    }

    /**
     * Bloquea un slot. Verifica que no haya cita confirmada primero.
     */
    public function bloquear(int $id_barbero, string $fecha, string $hora_inicio): array {
        // Verificar si tiene cita activa en ese horario
        $stmt = $this->conn->prepare(
            "SELECT id_cita FROM citas
            WHERE id_barbero = ? AND fecha = ? AND hora = ?
            AND estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')
            LIMIT 1"
        );
        $stmt->bind_param('iss', $id_barbero, $fecha, $hora_inicio);
        $stmt->execute();
        $cita = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cita) {
            return [
                'ok' => false,
                'mensaje' => 'Existe una cita confirmada en este horario. Debes reprogramarla primero.'
            ];
        }

        $stmt2 = $this->conn->prepare(
            "UPDATE disponibilidad SET disponible = 0
            WHERE id_barbero = ? AND fecha = ? AND hora_inicio = ?"
        );
        $stmt2->bind_param('iss', $id_barbero, $fecha, $hora_inicio);
        $ok = $stmt2->execute();
        $stmt2->close();

        return ['ok' => $ok, 'mensaje' => $ok ? 'Horario bloqueado.' : 'Error al bloquear.'];
    }

    /**
     * Retorna barberos que tienen disponibilidad para una fecha dada.
     */
    public function barberosPorFecha(string $fecha): array {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT u.id_usuario, u.nombre, u.especialidad
            FROM disponibilidad d
            INNER JOIN usuarios u ON d.id_barbero = u.id_usuario
            WHERE d.fecha = ? AND d.disponible = 1 AND u.estado = 'ACTIVO'
            ORDER BY u.nombre ASC"
        );
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Genera slots automaticos para un día específico */
    public function generarDia(int $id_barbero, string $fecha, string $hora_apertura, string $hora_cierre, int $duracion_min): int {
        $creados = 0;
        
        $ts_actual = strtotime($hora_apertura);
        $ts_cierre = strtotime($hora_cierre);
        
        if (!$ts_actual || !$ts_cierre) {
            return 0;
        }

        // Si la hora de cierre es menor o igual a la apertura, intentamos corregirlo inteligentemente.
        if ($ts_cierre <= $ts_actual) {
            // Posible error de usuario (ingresó 05:00 AM en lugar de 05:00 PM / 17:00).
            $ts_cierre_pm = strtotime('+12 hours', $ts_cierre);
            if ($ts_cierre_pm > $ts_actual) {
                $ts_cierre = $ts_cierre_pm; // Lo convertimos a PM
            } else {
                // Si aún sumándole 12 horas sigue siendo menor (ej. abre a las 20:00 y cierra a las 02:00), 
                // entonces es de madrugada del día siguiente.
                $ts_cierre = strtotime('+1 day', $ts_cierre);
            }
        }

        while ($ts_actual < $ts_cierre) {
            $ts_fin = strtotime("+{$duracion_min} minutes", $ts_actual);
            
            if ($ts_fin > $ts_cierre) break;
            
            $hora_str = date('H:i:s', $ts_actual);
            $hora_fin_str = date('H:i:s', $ts_fin);
            
            $this->activar($id_barbero, $fecha, $hora_str, $hora_fin_str);
            
            $ts_actual = $ts_fin;
            $creados++;
        }
        return $creados;
    }

    /**
     * Elimina un slot de disponibilidad. Verifica que no haya cita confirmada.
     */
    public function eliminar(int $id_barbero, string $fecha, string $hora_inicio): array {
        // Verificar si tiene cita activa en ese horario
        $stmt = $this->conn->prepare(
            "SELECT id_cita FROM citas
            WHERE id_barbero = ? AND fecha = ? AND hora = ?
            AND estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')
            LIMIT 1"
        );
        $stmt->bind_param('iss', $id_barbero, $fecha, $hora_inicio);
        $stmt->execute();
        $cita = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cita) {
            return [
                'ok' => false,
                'mensaje' => 'Existe una cita confirmada en este horario. No se puede eliminar.'
            ];
        }

        $stmt2 = $this->conn->prepare(
            "DELETE FROM disponibilidad
            WHERE id_barbero = ? AND fecha = ? AND hora_inicio = ?"
        );
        $stmt2->bind_param('iss', $id_barbero, $fecha, $hora_inicio);
        $ok = $stmt2->execute();
        $stmt2->close();

        return ['ok' => $ok, 'mensaje' => $ok ? 'Horario eliminado correctamente.' : 'Error al eliminar el horario.'];
    }

    /**
     * Elimina todos los horarios de un día que no tengan citas confirmadas.
     */
    public function eliminarDia(int $id_barbero, string $fecha): array {
        // Obtenemos los slots que SÍ tienen cita
        $stmt = $this->conn->prepare(
            "SELECT d.id_disponibilidad
            FROM disponibilidad d
            INNER JOIN citas c ON c.id_barbero = d.id_barbero AND c.fecha = d.fecha AND c.hora = d.hora_inicio
            WHERE d.id_barbero = ? AND d.fecha = ? AND c.estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')"
        );
        $stmt->bind_param('is', $id_barbero, $fecha);
        $stmt->execute();
        $con_cita = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $ids_no_borrar = array_column($con_cita, 'id_disponibilidad');

        if (!empty($ids_no_borrar)) {
            $ids_str = implode(',', array_map('intval', $ids_no_borrar));
            $sql = "DELETE FROM disponibilidad WHERE id_barbero = ? AND fecha = ? AND id_disponibilidad NOT IN ($ids_str)";
        } else {
            $sql = "DELETE FROM disponibilidad WHERE id_barbero = ? AND fecha = ?";
        }

        $stmt2 = $this->conn->prepare($sql);
        $stmt2->bind_param('is', $id_barbero, $fecha);
        $ok = $stmt2->execute();
        $eliminados = $stmt2->affected_rows;
        $stmt2->close();

        if (count($ids_no_borrar) > 0) {
            $mensaje = "Se eliminaron $eliminados horarios libres. No se pudieron eliminar " . count($ids_no_borrar) . " por tener citas.";
        } else {
            $mensaje = "Todos los horarios del día fueron eliminados.";
        }

        return ['ok' => $ok, 'mensaje' => $mensaje];
    }
}
