<?php
/**
 * controllers/admin/dashboardController.php
 * Reúne todos los datos necesarios para el dashboard del administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Cita.php';
require_once __DIR__ . '/../../models/Servicio.php';

class AdminDashboardController {

    private $conn;
    private Cita $citaModel;
    private Servicio $servicioModel;

    public function __construct($conn) {
        $this->conn          = $conn;
        $this->citaModel     = new Cita();
        $this->servicioModel = new Servicio();
    }

    /** KPI: contadores rápidos */
    public function getStats(): array {
        $hoy = date('Y-m-d');

        // Total usuarios por rol
        $stmt = $this->conn->prepare(
            "SELECT id_rol, COUNT(*) AS total FROM usuarios GROUP BY id_rol"
        );
        $stmt->execute();
        $roles = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $roles[(int)$r['id_rol']] = (int)$r['total'];
        }
        $stmt->close();

        // Citas de hoy por estado
        $stmt2 = $this->conn->prepare(
            "SELECT estado, COUNT(*) AS total FROM citas WHERE fecha = ? GROUP BY estado"
        );
        $stmt2->bind_param('s', $hoy);
        $stmt2->execute();
        $citas_hoy = [];
        foreach ($stmt2->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $citas_hoy[$r['estado']] = (int)$r['total'];
        }
        $stmt2->close();

        // Citas del mes
        $mes_inicio = date('Y-m-01');
        $mes_fin    = date('Y-m-t');
        $stmt3 = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM citas WHERE fecha BETWEEN ? AND ?"
        );
        $stmt3->bind_param('ss', $mes_inicio, $mes_fin);
        $stmt3->execute();
        $citas_mes = (int)($stmt3->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt3->close();

        // Servicios activos
        $total_servicios = $this->servicioModel->contarActivos();

        return [
            'clientes'          => $roles[3] ?? 0,
            'barberos'          => $roles[2] ?? 0,
            'citas_hoy'         => array_sum($citas_hoy),
            'citas_hoy_pend'    => $citas_hoy['PENDIENTE']  ?? 0,
            'citas_hoy_acept'   => $citas_hoy['ACEPTADA']   ?? 0,
            'citas_hoy_comp'    => $citas_hoy['COMPLETADA'] ?? 0,
            'citas_mes'         => $citas_mes,
            'servicios_activos' => $total_servicios,
        ];
    }

    /** Últimas citas para la tabla */
    public function getCitasRecientes(int $limit = 10): array {
        $stmt = $this->conn->prepare(
            "SELECT c.id_cita, c.fecha, c.hora, c.estado,
                    uc.nombre AS cliente, ub.nombre AS barbero,
                    s.nombre  AS servicio, s.precio
            FROM citas c
            INNER JOIN usuarios uc ON c.id_cliente  = uc.id_usuario
            INNER JOIN usuarios ub ON c.id_barbero  = ub.id_usuario
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            ORDER BY c.fecha_creacion DESC
            LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lista de usuarios (clientes + barberos) */
    public function getUsuarios(int $limit = 10): array {
        $stmt = $this->conn->prepare(
            "SELECT u.id_usuario, u.nombre, u.email, u.telefono,
                    u.estado, u.fecha_registro, r.nombre AS rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.id_rol IN (2, 3)
            ORDER BY u.fecha_registro DESC
            LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Servicios más solicitados */
    public function getServiciosMasSolicitados(): array {
        return $this->servicioModel->masSolicitados();
    }

    /** Log de auditoría reciente */
    public function getAuditoriaReciente(int $limit = 8): array {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre AS usuario
            FROM auditoria a
            INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
            ORDER BY a.fecha_hora DESC
            LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
