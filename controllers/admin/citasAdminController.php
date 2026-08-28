<?php
/**
 * controllers/admin/citasAdminController.php
 * Gestión de citas para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';
require_once __DIR__ . '/../../models/Cita.php';

class CitasAdminController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Lista citas con filtros opcionales */
    public function listar(string $estado = '', string $fecha_desde = '', string $fecha_hasta = '', int $id_barbero = 0): array {
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.estado,
                    uc.nombre AS cliente, uc.email AS email_cliente, uc.telefono AS tel_cliente, uc.foto_perfil AS foto_cliente,
                    ub.nombre AS barbero,
                    s.nombre  AS servicio, s.precio, s.duracion_min AS duracion_minutos
                FROM citas c
                INNER JOIN usuarios uc ON c.id_cliente  = uc.id_usuario
                INNER JOIN usuarios ub ON c.id_barbero  = ub.id_usuario
                INNER JOIN servicios s  ON c.id_servicio = s.id_servicio
                WHERE 1=1";
        $params = '';
        $values = [];

        if ($estado) {
            $sql .= " AND c.estado = ?";
            $params .= 's'; $values[] = $estado;
        }
        if ($fecha_desde) {
            $sql .= " AND c.fecha >= ?";
            $params .= 's'; $values[] = $fecha_desde;
        }
        if ($fecha_hasta) {
            $sql .= " AND c.fecha <= ?";
            $params .= 's'; $values[] = $fecha_hasta;
        }
        if ($id_barbero > 0) {
            $sql .= " AND c.id_barbero = ?";
            $params .= 'i'; $values[] = $id_barbero;
        }

        $sql .= " ORDER BY c.fecha DESC, c.hora DESC LIMIT 300";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($params, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Cambia el estado de una cita */
    public function cambiarEstado(int $id_cita, string $nuevo_estado): array {
        $estados = ['PENDIENTE', 'ACEPTADA', 'COMPLETADA', 'CANCELADA', 'REPROGRAMADA'];
        if (!in_array($nuevo_estado, $estados)) {
            return ['ok' => false, 'msg' => 'Estado inválido.'];
        }
        $stmt = $this->conn->prepare("UPDATE citas SET estado = ? WHERE id_cita = ?");
        $stmt->bind_param('si', $nuevo_estado, $id_cita);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "ADMIN_CAMBIO_ESTADO_CITA: ID=$id_cita → $nuevo_estado", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? "Cita actualizada a $nuevo_estado." : 'Error al actualizar cita.'];
    }

    /** Lista todos los barberos (para filtro select) */
    public function getBarberos(): array {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre FROM usuarios WHERE id_rol = 2 AND estado = 'ACTIVO' ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Estadísticas generales de citas */
    public function getStats(): array {
        $stmt = $this->conn->prepare(
            "SELECT estado, COUNT(*) AS total FROM citas GROUP BY estado"
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stats = [];
        foreach ($rows as $r) {
            $stats[strtolower($r['estado'])] = (int)$r['total'];
        }
        $stats['total'] = array_sum($stats);
        return $stats;
    }

    /** Lista todos los clientes activos (para filtro select) */
    public function getClientes(): array {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre, telefono FROM usuarios WHERE id_rol = 3 AND estado = 'ACTIVO' ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lista todos los servicios activos (para filtro select) */
    public function getServicios(): array {
        $stmt = $this->conn->prepare(
            "SELECT id_servicio, nombre, precio, duracion_min AS duracion_minutos FROM servicios WHERE estado = 'ACTIVO' ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Crea una cita usando el modelo de negocio */
    public function crearCita(int $id_cliente, int $id_barbero, int $id_servicio, string $fecha, string $hora): array {
        $citaModel = new Cita();
        $resultado = $citaModel->crear($id_cliente, $id_barbero, $id_servicio, $fecha, $hora);
        
        if ($resultado['ok']) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "ADMIN_CREO_CITA: Cliente=$id_cliente, Barbero=$id_barbero, Fecha=$fecha $hora", "EXITOSO");
            return ['ok' => true, 'msg' => 'Cita creada exitosamente.'];
        } else {
            return ['ok' => false, 'msg' => $resultado['mensaje'] ?? 'Error al crear la cita.'];
        }
    }
}
