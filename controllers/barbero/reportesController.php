<?php
/**
 * controllers/barbero/reportesController.php
 * Genera estadísticas del barbero autenticado:
 * – Citas completadas por día (últimos 30 días)
 * – Servicios más solicitados
 * – Ingresos estimados (precio × completadas)
 * – Tasa de cancelación
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cita.php';
require_once __DIR__ . '/../../models/Servicio.php';

class BarberoReportesController {

    private Cita $citaModel;
    private Servicio $servicioModel;

    public function __construct() {
        $this->citaModel     = new Cita();
        $this->servicioModel = new Servicio();
    }

    /**
     * Devuelve todos los datos necesarios para la vista de reportes.
     */
    public function getDatos(int $id_barbero, string $desde, string $hasta): array {
        global $conn;

        // 1. Citas por día
        $stmt = $conn->prepare(
            "SELECT DATE(fecha) AS dia, COUNT(*) AS total
            FROM citas
            WHERE id_barbero = ? AND estado = 'COMPLETADA'
            AND fecha BETWEEN ? AND ?
            GROUP BY DATE(fecha)
            ORDER BY dia ASC"
        );
        $stmt->bind_param('iss', $id_barbero, $desde, $hasta);
        $stmt->execute();
        $por_dia = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 2. Servicios más solicitados
        $servicios_top = $this->servicioModel->masSolicitados($id_barbero, $desde, $hasta);

        // 3. Ingresos estimados y totales
        $stmt2 = $conn->prepare(
            "SELECT
                COUNT(*)                                               AS total,
                SUM(s.precio)                                          AS ingresos,
                SUM(c.estado = 'COMPLETADA')                          AS completadas,
                SUM(c.estado = 'CANCELADA')                           AS canceladas,
                SUM(c.estado = 'PENDIENTE')                           AS pendientes,
                SUM(c.estado = 'ACEPTADA')                            AS aceptadas
            FROM citas c
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            WHERE c.id_barbero = ? AND c.fecha BETWEEN ? AND ?"
        );
        $stmt2->bind_param('iss', $id_barbero, $desde, $hasta);
        $stmt2->execute();
        $resumen = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        // 4. Tasa de cancelación
        $total = (int)($resumen['total'] ?? 0);
        $canceladas = (int)($resumen['canceladas'] ?? 0);
        $tasa_cancelacion = $total > 0 ? round(($canceladas / $total) * 100, 1) : 0;

        return [
            'por_dia'          => $por_dia,
            'servicios_top'    => $servicios_top,
            'resumen'          => $resumen,
            'tasa_cancelacion' => $tasa_cancelacion,
        ];
    }
}
