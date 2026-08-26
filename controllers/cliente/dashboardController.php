<?php
/**
 * controllers/cliente/dashboardController.php
 * Datos del dashboard para el cliente autenticado.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cita.php';
require_once __DIR__ . '/../../models/Servicio.php';

class ClienteDashboardController {

    private Cita $citaModel;
    private Servicio $servicioModel;

    public function __construct() {
        $this->citaModel     = new Cita();
        $this->servicioModel = new Servicio();
    }

    /** KPIs del cliente */
    public function getStats(int $id_cliente): array {
        $todas      = $this->citaModel->obtenerPorCliente($id_cliente);
        $proximas   = array_filter($todas, fn($c) =>
            in_array($c['estado'], ['PENDIENTE', 'ACEPTADA']) &&
            $c['fecha'] >= date('Y-m-d')
        );
        $completadas = array_filter($todas, fn($c) => $c['estado'] === 'COMPLETADA');

        return [
            'proximas'    => count($proximas),
            'completadas' => count($completadas),
            'total'       => count($todas),
        ];
    }

    /** Próxima cita del cliente */
    public function getProximaCita(int $id_cliente): ?array {
        return $this->citaModel->proximaCitaCliente($id_cliente);
    }

    /** Listado de citas del cliente */
    public function getMisCitas(int $id_cliente): array {
        return $this->citaModel->obtenerPorCliente($id_cliente);
    }

    /** Catálogo de servicios activos */
    public function getServicios(): array {
        return $this->servicioModel->obtenerActivos();
    }

    /** Turno activo del cliente (si tiene) */
    public function getTurnoActivo(int $id_cliente): ?array {
        global $conn;
        $hoy = date('Y-m-d');
        $stmt = $conn->prepare(
            "SELECT t.*, c.hora, s.nombre AS servicio, u.nombre AS barbero
            FROM turnos t
            INNER JOIN citas c    ON t.id_cita     = c.id_cita
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            INNER JOIN usuarios u  ON c.id_barbero  = u.id_usuario
            WHERE t.id_cliente = ? AND t.fecha = ? AND t.estado = 'EN_ESPERA'
            ORDER BY t.posicion ASC LIMIT 1"
        );
        $stmt->bind_param('is', $id_cliente, $hoy);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}
