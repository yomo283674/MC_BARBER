<?php
/**
 * controllers/admin/estadisticasController.php
 * Reportes y estadísticas para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';

class EstadisticasController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Ingresos totales por mes (últimos 6 meses) */
    public function ingresosPorMes(): array {
        $stmt = $this->conn->prepare(
            "SELECT DATE_FORMAT(c.fecha, '%Y-%m') AS mes,
                    COUNT(c.id_cita) AS total_citas,
                    SUM(s.precio) AS ingresos
             FROM citas c
             INNER JOIN servicios s ON c.id_servicio = s.id_servicio
             WHERE c.estado = 'COMPLETADA'
               AND c.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Citas por estado (para gráfico de torta) */
    public function citasPorEstado(): array {
        $stmt = $this->conn->prepare(
            "SELECT estado, COUNT(*) AS total FROM citas GROUP BY estado ORDER BY total DESC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Rendimiento por barbero */
    public function rendimientoPorBarbero(): array {
        $stmt = $this->conn->prepare(
            "SELECT ub.nombre AS barbero,
                    COUNT(c.id_cita) AS total_citas,
                    SUM(CASE WHEN c.estado='COMPLETADA' THEN 1 ELSE 0 END) AS completadas,
                    SUM(CASE WHEN c.estado='CANCELADA'  THEN 1 ELSE 0 END) AS canceladas,
                    SUM(CASE WHEN c.estado='COMPLETADA' THEN s.precio ELSE 0 END) AS ingresos
             FROM citas c
             INNER JOIN usuarios ub ON c.id_barbero = ub.id_usuario
             INNER JOIN servicios s ON c.id_servicio = s.id_servicio
             WHERE c.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY ub.id_usuario
             ORDER BY completadas DESC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Servicios más populares */
    public function serviciosPopulares(int $limit = 8): array {
        $stmt = $this->conn->prepare(
            "SELECT s.nombre, s.precio, COUNT(c.id_cita) AS total_citas,
                    SUM(CASE WHEN c.estado='COMPLETADA' THEN s.precio ELSE 0 END) AS ingresos
             FROM servicios s
             LEFT JOIN citas c ON s.id_servicio = c.id_servicio
             GROUP BY s.id_servicio
             ORDER BY total_citas DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** KPIs generales */
    public function kpis(): array {
        $hoy       = date('Y-m-d');
        $mes_ini   = date('Y-m-01');
        $mes_fin   = date('Y-m-t');

        // Ingresos del mes
        $s1 = $this->conn->prepare(
            "SELECT SUM(s.precio) AS total FROM citas c
             INNER JOIN servicios s ON c.id_servicio = s.id_servicio
             WHERE c.estado = 'COMPLETADA' AND c.fecha BETWEEN ? AND ?"
        );
        $s1->bind_param('ss', $mes_ini, $mes_fin);
        $s1->execute();
        $ingresos_mes = (float)($s1->get_result()->fetch_assoc()['total'] ?? 0);

        // Citas del mes
        $s2 = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM citas WHERE fecha BETWEEN ? AND ?"
        );
        $s2->bind_param('ss', $mes_ini, $mes_fin);
        $s2->execute();
        $citas_mes = (int)($s2->get_result()->fetch_assoc()['total'] ?? 0);

        // Clientes nuevos del mes
        $s3 = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 3 AND DATE(fecha_registro) BETWEEN ? AND ?"
        );
        $s3->bind_param('ss', $mes_ini, $mes_fin);
        $s3->execute();
        $clientes_nuevos = (int)($s3->get_result()->fetch_assoc()['total'] ?? 0);

        // Tasa de cancelación
        $s4 = $this->conn->prepare(
            "SELECT estado, COUNT(*) AS total FROM citas WHERE fecha BETWEEN ? AND ? GROUP BY estado"
        );
        $s4->bind_param('ss', $mes_ini, $mes_fin);
        $s4->execute();
        $por_estado = [];
        foreach ($s4->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $por_estado[$r['estado']] = $r['total'];
        }
        $total_mes = array_sum($por_estado);
        $canceladas = $por_estado['CANCELADA'] ?? 0;
        $tasa_cancelacion = $total_mes > 0 ? round(($canceladas / $total_mes) * 100, 1) : 0;

        return [
            'ingresos_mes'      => $ingresos_mes,
            'citas_mes'         => $citas_mes,
            'clientes_nuevos'   => $clientes_nuevos,
            'tasa_cancelacion'  => $tasa_cancelacion,
        ];
    }
}
