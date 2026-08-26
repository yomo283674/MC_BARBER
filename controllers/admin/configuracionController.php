<?php
/**
 * controllers/admin/configuracionController.php
 * Gestión de configuración del sistema para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Configuracion.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

class ConfiguracionController {

    private $conn;
    private Configuracion $configModel;

    public function __construct($conn) {
        $this->conn        = $conn;
        $this->configModel = new Configuracion();
    }

    /** Obtiene la configuración actual del sistema */
    public function obtener(): array {
        return $this->configModel->obtener() ?? [
            'nombre_negocio'   => 'MC Barbería',
            'logo_url'         => '',
            'horario_apertura' => '08:00:00',
            'horario_cierre'   => '20:00:00',
        ];
    }

    /** Guarda/actualiza la configuración del sistema */
    public function guardar(array $datos): array {
        $nombre   = trim($datos['nombre_negocio'] ?? '');
        $logo     = trim($datos['logo_url']        ?? '');
        $apertura = $datos['horario_apertura']      ?? '08:00';
        $cierre   = $datos['horario_cierre']        ?? '20:00';
        $admin_id = (int)($_SESSION['usuario_id']   ?? 0);

        if (empty($nombre)) {
            return ['ok' => false, 'msg' => 'El nombre del negocio es requerido.'];
        }
        if ($apertura >= $cierre) {
            return ['ok' => false, 'msg' => 'La hora de apertura debe ser anterior al cierre.'];
        }

        $ok = $this->configModel->guardar($nombre, $logo, $apertura, $cierre, $admin_id);

        if ($ok) {
            registrarAuditoria($this->conn, $admin_id, "ACTUALIZAR_CONFIGURACION", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Configuración guardada correctamente.' : 'Error al guardar configuración.'];
    }

    /** Resetea la configuración a valores por defecto */
    public function resetear(): array {
        $defaults = [
            'nombre_negocio'   => 'MC Barbería',
            'logo_url'         => '',
            'horario_apertura' => '08:00',
            'horario_cierre'   => '20:00',
        ];
        return $this->guardar($defaults);
    }
}
