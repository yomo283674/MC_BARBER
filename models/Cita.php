<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Cita
 * Gestiona el ciclo de vida completo de las citas.
 * Regla critica: ventana de cancelacion/confirmacion = 3 MINUTOS (180 segundos).
 */
class Cita {

    private $conn;
    private $config;

    public function __construct() {
        global $conn, $globalConfig;
        $this->conn = $conn;
        $this->config = $globalConfig ?? [
            'tiempo_cancelacion_min' => 10,
            'tiempo_reprogramacion_min' => 10,
            'duracion_cita_min' => 30
        ];
    }

    // CREAR

    /**
     * Crea una nueva cita verificando disponibilidad previa.
     * Tambien registra el turno del dia si la cita es para hoy.
     */
    public function crear(int $id_cliente, int $id_barbero, int $id_servicio, string $fecha, string $hora): array {
        // Verificar que no exista otra cita en ese horario para el barbero
        if ($this->horarioOcupado($id_barbero, $fecha, $hora)) {
            return ['ok' => false, 'mensaje' => 'El horario seleccionado ya no está disponible.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO citas (id_cliente, id_barbero, id_servicio, fecha, hora, estado)
            VALUES (?, ?, ?, ?, ?, 'PENDIENTE')"
        );
        $stmt->bind_param('iiiss', $id_cliente, $id_barbero, $id_servicio, $fecha, $hora);
        $ok = $stmt->execute();
        $id_cita = $this->conn->insert_id;
        $stmt->close();

        if (!$ok) {
            return ['ok' => false, 'mensaje' => 'Error al crear la cita.'];
        }

        // Registrar turno si la cita es para hoy
        if ($fecha === date('Y-m-d')) {
            $this->registrarTurno($id_cliente, $id_cita, $fecha);
        }

        return ['ok' => true, 'id_cita' => $id_cita];
    }

    private function registrarTurno(int $id_cliente, int $id_cita, string $fecha): void {
        // Obtener posicion (cantidad de turnos del dia + 1)
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM turnos WHERE fecha = ?"
        );
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $posicion = ($row['total'] ?? 0) + 1;

        $stmt = $this->conn->prepare(
            "INSERT INTO turnos (id_cliente, id_cita, fecha, posicion, estado) VALUES (?, ?, ?, ?, 'EN_ESPERA')"
        );
        $stmt->bind_param('iisi', $id_cliente, $id_cita, $fecha, $posicion);
        $stmt->execute();
        $stmt->close();
    }

    // CONSULTAS

    public function obtenerPorCliente(int $id_cliente): array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.nombre AS servicio, s.precio, s.duracion_min,
                    u.nombre AS barbero
            FROM citas c
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            INNER JOIN usuarios u  ON c.id_barbero  = u.id_usuario
            WHERE c.id_cliente = ?
            ORDER BY c.fecha DESC, c.hora DESC"
        );
        $stmt->bind_param('i', $id_cliente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorBarbero(int $id_barbero, ?string $fecha = null): array {
        $sql = "SELECT c.*, s.nombre AS servicio, s.precio, s.duracion_min,
                    u.nombre AS cliente, u.telefono AS cliente_telefono, u.foto_perfil AS cliente_foto
                FROM citas c
                INNER JOIN servicios s ON c.id_servicio = s.id_servicio
                INNER JOIN usuarios u  ON c.id_cliente  = u.id_usuario
                WHERE c.id_barbero = ?";
        if ($fecha) {
            $sql .= " AND c.fecha = ?";
        }
        $sql .= " ORDER BY c.fecha ASC, c.hora ASC";

        $stmt = $this->conn->prepare($sql);
        if ($fecha) {
            $stmt->bind_param('is', $id_barbero, $fecha);
        } else {
            $stmt->bind_param('i', $id_barbero);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id_cita): ?array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.nombre AS servicio, s.precio, s.duracion_min,
                    ub.nombre AS barbero, uc.nombre AS cliente
            FROM citas c
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            INNER JOIN usuarios ub ON c.id_barbero  = ub.id_usuario
            INNER JOIN usuarios uc ON c.id_cliente  = uc.id_usuario
            WHERE c.id_cita = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id_cita);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function proximaCitaCliente(int $id_cliente): ?array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, s.nombre AS servicio, s.precio, s.duracion_min, u.nombre AS barbero
            FROM citas c
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            INNER JOIN usuarios u  ON c.id_barbero  = u.id_usuario
            WHERE c.id_cliente = ?
            AND c.estado IN ('PENDIENTE','ACEPTADA')
            AND (c.fecha > CURDATE() OR (c.fecha = CURDATE() AND c.hora >= CURTIME()))
            ORDER BY c.fecha ASC, c.hora ASC
            LIMIT 1"
        );
        $stmt->bind_param('i', $id_cliente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // ACCIONES CLIENTE
    /**
     * Cancela una cita. Valida ventana de 3 minutos en backend.
     */
    public function cancelarCliente(int $id_cita, int $id_cliente): array {
        $cita = $this->obtenerPorId($id_cita);

        if (!$cita) {
            return ['ok' => false, 'mensaje' => 'Cita no encontrada.'];
        }
        if ((int)$cita['id_cliente'] !== $id_cliente) {
            return ['ok' => false, 'mensaje' => 'No tienes permiso para cancelar esta cita.'];
        }
        if (!in_array($cita['estado'], ['PENDIENTE', 'ACEPTADA'])) {
            return ['ok' => false, 'mensaje' => 'Esta cita no puede ser cancelada.'];
        }

        // Validar tiempo antes de la cita (configuracion global)
        $cita_datetime = new DateTime($cita['fecha'] . ' ' . $cita['hora']);
        $ahora = new DateTime();
        $min_cancelacion = (int)($this->config['tiempo_cancelacion_min'] ?? 10);
        $limite = clone $cita_datetime;
        $limite->modify("-{$min_cancelacion} minutes");

        if ($ahora > $limite) {
            return ['ok' => false, 'mensaje' => "El tiempo para cancelar esta cita ha expirado (mínimo {$min_cancelacion} minutos antes)."];
        }

        $stmt = $this->conn->prepare(
            "UPDATE citas SET estado = 'CANCELADA', fecha_actualizacion = NOW() WHERE id_cita = ?"
        );
        $stmt->bind_param('i', $id_cita);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->liberarDisponibilidad($cita['id_barbero'], $cita['fecha'], $cita['hora']);
        }

        return ['ok' => $ok, 'mensaje' => $ok ? 'Cita cancelada correctamente.' : 'Error al cancelar.'];
    }


    // ACCIONES BARBERO

    public function aceptar(int $id_cita, int $id_barbero): array {
        $cita = $this->obtenerPorId($id_cita);
        if (!$cita || (int)$cita['id_barbero'] !== $id_barbero) {
            return ['ok' => false, 'mensaje' => 'No tienes permiso sobre esta cita.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE citas SET estado = 'ACEPTADA', fecha_actualizacion = NOW() WHERE id_cita = ?"
        );
        $stmt->bind_param('i', $id_cita);
        $ok = $stmt->execute();
        $stmt->close();
        return ['ok' => $ok, 'mensaje' => $ok ? 'Cita aceptada.' : 'Error al aceptar.'];
    }

    public function cancelarBarbero(int $id_cita, int $id_barbero, string $motivo = ''): array {
        $cita = $this->obtenerPorId($id_cita);
        if (!$cita || (int)$cita['id_barbero'] !== $id_barbero) {
            return ['ok' => false, 'mensaje' => 'No tienes permiso sobre esta cita.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE citas SET estado = 'CANCELADA', motivo_cancelacion = ?, fecha_actualizacion = NOW()
            WHERE id_cita = ?"
        );
        $stmt->bind_param('si', $motivo, $id_cita);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->liberarDisponibilidad($cita['id_barbero'], $cita['fecha'], $cita['hora']);
        }

        return ['ok' => $ok, 'mensaje' => $ok ? 'Cita cancelada.' : 'Error al cancelar.'];
    }

    public function reprogramar(int $id_cita, int $id_barbero, string $nueva_fecha, string $nueva_hora): array {
        $cita = $this->obtenerPorId($id_cita);
        if (!$cita || (int)$cita['id_barbero'] !== $id_barbero) {
            return ['ok' => false, 'mensaje' => 'No tienes permiso sobre esta cita.'];
        }
        if ($this->horarioOcupado($id_barbero, $nueva_fecha, $nueva_hora, $id_cita)) {
            return ['ok' => false, 'mensaje' => 'El nuevo horario no está disponible.'];
        }

        // Calcular fecha limite de respuesta del cliente
        $minutos_repr = (int)($this->config['tiempo_reprogramacion_min'] ?? 10);
        $fecha_limite = date('Y-m-d H:i:s', strtotime('+' . $minutos_repr . ' minutes'));

        // Guardar reprogramacion
        $stmt = $this->conn->prepare(
            "INSERT INTO reprogramaciones (id_cita, nueva_fecha, nueva_hora, estado, fecha_solicitud, fecha_limite_respuesta)
            VALUES (?, ?, ?, 'PENDIENTE', NOW(), ?)"
        );
        $stmt->bind_param('isss', $id_cita, $nueva_fecha, $nueva_hora, $fecha_limite);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Marcar cita como reprogramada
            $stmt2 = $this->conn->prepare(
                "UPDATE citas SET estado = 'REPROGRAMADA', fecha_actualizacion = NOW() WHERE id_cita = ?"
            );
            $stmt2->bind_param('i', $id_cita);
            $stmt2->execute();
            $stmt2->close();
        }

        return ['ok' => $ok, 'mensaje' => $ok ? "Cita reprogramada. El cliente tiene {$minutos_repr} minutos para confirmar." : 'Error al reprogramar.'];
    }

    /**
     * El cliente confirma o rechaza la reprogramacion.
     * Valida ventana de 3 minutos en backend.
     */
    public function responderReprogramacion(int $id_cita, int $id_cliente, string $accion): array {
        // Obtener la reprogramacion pendiente
        $stmt = $this->conn->prepare(
            "SELECT * FROM reprogramaciones
            WHERE id_cita = ? AND estado = 'PENDIENTE'
            ORDER BY fecha_solicitud DESC LIMIT 1"
        );
        $stmt->bind_param('i', $id_cita);
        $stmt->execute();
        $repr = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$repr) {
            return ['ok' => false, 'mensaje' => 'No hay reprogramacion pendiente.'];
        }

        // Validar ventana de 3 minutos
        if (!$this->dentroDeVentana($repr['fecha_solicitud'])) {
            // Expirada: la reprogramacion se aplica automaticamente
            $this->aplicarReprogramacion($repr['id_reprogramacion'], $id_cita,
                $repr['nueva_fecha'], $repr['nueva_hora']);
            return ['ok' => false, 'mensaje' => 'El tiempo de respuesta expiró. La reprogramación fue aplicada automáticamente.'];
        }

        if ($accion === 'CONFIRMAR') {
            $this->aplicarReprogramacion($repr['id_reprogramacion'], $id_cita,
                $repr['nueva_fecha'], $repr['nueva_hora']);
            return ['ok' => true, 'mensaje' => 'Reprogramación confirmada.'];
        } else {
            // Rechazar = cancelar cita
            $stmt = $this->conn->prepare(
                "UPDATE reprogramaciones SET estado = 'CANCELADA' WHERE id_reprogramacion = ?"
            );
            $stmt->bind_param('i', $repr['id_reprogramacion']);
            $stmt->execute();
            $stmt->close();

            $stmt2 = $this->conn->prepare(
                "UPDATE citas SET estado = 'CANCELADA', fecha_actualizacion = NOW() WHERE id_cita = ?"
            );
            $stmt2->bind_param('i', $id_cita);
            $stmt2->execute();
            $stmt2->close();

            return ['ok' => true, 'mensaje' => 'Has cancelado la cita reprogramada.'];
        }
    }

    private function aplicarReprogramacion(int $id_repr, int $id_cita, string $nueva_fecha, string $nueva_hora): void {
        $stmt = $this->conn->prepare(
            "UPDATE reprogramaciones SET estado = 'CONFIRMADA' WHERE id_reprogramacion = ?"
        );
        $stmt->bind_param('i', $id_repr);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $this->conn->prepare(
            "UPDATE citas SET fecha = ?, hora = ?, estado = 'ACEPTADA', fecha_actualizacion = NOW() WHERE id_cita = ?"
        );
        $stmt2->bind_param('ssi', $nueva_fecha, $nueva_hora, $id_cita);
        $stmt2->execute();
        $stmt2->close();
    }

    public function completar(int $id_cita, int $id_barbero): array {
        $stmt = $this->conn->prepare(
            "UPDATE citas SET estado = 'COMPLETADA', fecha_actualizacion = NOW()
            WHERE id_cita = ? AND id_barbero = ?"
        );
        $stmt->bind_param('ii', $id_cita, $id_barbero);
        $ok = $stmt->execute();
        $stmt->close();
        return ['ok' => $ok];
    }

    public function resumenHoyBarbero(int $id_barbero): array {
        $hoy = date('Y-m-d');
        $stmt = $this->conn->prepare(
            "SELECT
            SUM(fecha = ?) AS hoy,
            SUM(estado = 'PENDIENTE') AS pendientes,
            SUM(estado = 'ACEPTADA') AS aceptadas,
            SUM(estado = 'COMPLETADA') AS completadas
            FROM citas WHERE id_barbero = ?"
        );
        $stmt->bind_param('si', $hoy, $id_barbero);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?? [];
    }

    public function resumenGlobal(): array {
        $hoy = date('Y-m-d');
        $stmt = $this->conn->prepare(
            "SELECT
            SUM(fecha = ?) AS hoy,
            SUM(estado = 'COMPLETADA') AS completadas,
            COUNT(*) AS total
            FROM citas"
        );
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?? [];
    }

    public function citasPorDia(?int $id_barbero = null, ?string $desde = null, ?string $hasta = null): array {
        $sql = "SELECT DATE(fecha) AS dia, COUNT(*) AS total
                FROM citas WHERE estado = 'COMPLETADA'";
        $params = '';
        $values = [];

        if ($id_barbero) {
            $sql .= " AND id_barbero = ?";
            $params .= 'i';
            $values[] = $id_barbero;
        }
        if ($desde) {
            $sql .= " AND fecha >= ?";
            $params .= 's';
            $values[] = $desde;
        }
        if ($hasta) {
            $sql .= " AND fecha <= ?";
            $params .= 's';
            $values[] = $hasta;
        }
        $sql .= " GROUP BY DATE(fecha) ORDER BY dia ASC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($params, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerReprogramacionPendiente(int $id_cita): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM reprogramaciones
            WHERE id_cita = ? AND estado = 'PENDIENTE'
            ORDER BY fecha_solicitud DESC LIMIT 1"
        );
        $stmt->bind_param('i', $id_cita);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function horarioOcupado(int $id_barbero, string $fecha, string $hora, int $excluir_cita = 0): bool {
        $stmt = $this->conn->prepare(
            "SELECT id_cita FROM citas
            WHERE id_barbero = ? AND fecha = ? AND hora = ?
            AND estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')
            AND id_cita != ?
            LIMIT 1"
        );
        $stmt->bind_param('issi', $id_barbero, $fecha, $hora, $excluir_cita);
        $stmt->execute();
        $stmt->store_result();
        $ocupado = $stmt->num_rows > 0;
        $stmt->close();
        return $ocupado;
    }

    /**
     * Verifica si una fecha_creacion esta dentro de la ventana de reprogramacion.
     * CRITICO: Esta validacion ocurre en BACKEND para evitar bypass del cliente.
     */
    public function dentroDeVentana(string $fecha_creacion): bool {
        $minutos_repr = (int)($this->config['tiempo_reprogramacion_min'] ?? 10);
        $segundos_repr = $minutos_repr * 60;
        $creacion = new DateTime($fecha_creacion);
        $ahora    = new DateTime();
        $segundos = $ahora->getTimestamp() - $creacion->getTimestamp();
        return $segundos <= $segundos_repr;
    }

    /**
     * Retorna los segundos restantes de la ventana (puede ser negativo si expiro).
     */
    public function segundosRestantes(string $fecha_creacion): int {
        $minutos_repr = (int)($this->config['tiempo_reprogramacion_min'] ?? 10);
        $segundos_repr = $minutos_repr * 60;
        $creacion = new DateTime($fecha_creacion);
        $ahora    = new DateTime();
        $transcurrido = $ahora->getTimestamp() - $creacion->getTimestamp();
        return max(0, $segundos_repr - $transcurrido);
    }

    private function liberarDisponibilidad(int $id_barbero, string $fecha, string $hora): void {
        $stmt = $this->conn->prepare(
            "UPDATE disponibilidad SET disponible = 1
            WHERE id_barbero = ? AND fecha = ? AND hora_inicio = ?"
        );
        $stmt->bind_param('iss', $id_barbero, $fecha, $hora);
        $stmt->execute();
        $stmt->close();
    }
}
