<?php
/**
 * controllers/barbero/dashboardController.php
 * Datos del dashboard para el barbero autenticado.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cita.php';
require_once __DIR__ . '/../../models/Disponibilidad.php';
require_once __DIR__ . '/../../models/Servicio.php';

class BarberoDashboardController {

    private Cita $citaModel;
    private Disponibilidad $dispModel;
    private Servicio $servicioModel;

    public function __construct() {
        $this->citaModel     = new Cita();
        $this->dispModel     = new Disponibilidad();
        $this->servicioModel = new Servicio();
    }

    /** KPIs del barbero */
    public function getStats(int $id_barbero): array {
        $hoy        = date('Y-m-d');
        $sem_inicio = date('Y-m-d', strtotime('monday this week'));
        $sem_fin    = date('Y-m-d', strtotime('sunday this week'));

        $resumen = $this->citaModel->resumenHoyBarbero($id_barbero);

        return [
            'citas_hoy'        => (int)($resumen['hoy']        ?? 0),
            'pendientes'       => (int)($resumen['pendientes'] ?? 0),
            'aceptadas'        => (int)($resumen['aceptadas']  ?? 0),
            'completadas_hoy'  => (int)($resumen['completadas'] ?? 0),
        ];
    }

    /** Agenda del día del barbero */
    public function getAgendaHoy(int $id_barbero): array {
        $hoy = date('Y-m-d');
        return $this->citaModel->obtenerPorBarbero($id_barbero, $hoy);
    }

    /** Citas pendientes (próximas) */
    public function getCitasPendientes(int $id_barbero): array {
        $todas = $this->citaModel->obtenerPorBarbero($id_barbero);
        return array_filter($todas, fn($c) =>
            in_array($c['estado'], ['PENDIENTE', 'ACEPTADA']) &&
            $c['fecha'] >= date('Y-m-d')
        );
    }

    /** Disponibilidad actual del barbero (próximos 7 días) */
    public function getDisponibilidadProxima(int $id_barbero): array {
        $hoy     = date('Y-m-d');
        $en7dias = date('Y-m-d', strtotime('+7 days'));
        global $conn;
        $stmt = $conn->prepare(
            "SELECT * FROM disponibilidad
            WHERE id_barbero = ? AND fecha BETWEEN ? AND ?
            ORDER BY fecha ASC, hora_inicio ASC"
        );
        $stmt->bind_param('iss', $id_barbero, $hoy, $en7dias);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Servicios activos (para mostrar en panel lateral) */
    public function getServicios(): array {
        return $this->servicioModel->obtenerActivos();
    }
}
