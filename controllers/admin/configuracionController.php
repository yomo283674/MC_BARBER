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
            'nombre_negocio'            => 'MC Barbería',
            'logo_url'                  => '',
            'horario_apertura'          => '08:00:00',
            'horario_cierre'            => '20:00:00',
            'duracion_cita_min'         => 30,
            'tiempo_cancelacion_min'    => 10,
            'tiempo_reprogramacion_min' => 10,
            'tiempo_notificacion_min'   => 2,
        ];
    }

    /** Guarda/actualiza la configuración del sistema */
    public function guardar(array $datos): array {
        $nombre         = trim($datos['nombre_negocio'] ?? '');
        $logo           = trim($datos['logo_url'] ?? $datos['logo_url_actual'] ?? ''); // Conservar actual por defecto o resetear

        // Manejo de la subida de archivo para el logo
        if (isset($_FILES['logo_archivo']) && $_FILES['logo_archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo_archivo']['tmp_name'];
            $fileName = $_FILES['logo_archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                
                $newFileName = 'logo_' . time() . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $logo = 'public/uploads/' . $newFileName;
                }
            }
        }

        $apertura       = $datos['horario_apertura']      ?? '08:00';
        $cierre         = $datos['horario_cierre']        ?? '20:00';
        $duracion_cita  = (int)($datos['duracion_cita_min'] ?? 30);
        $t_cancelacion  = (int)($datos['tiempo_cancelacion_min'] ?? 10);
        $t_reprogram    = (int)($datos['tiempo_reprogramacion_min'] ?? 10);
        $t_notificacion = (int)($datos['tiempo_notificacion_min'] ?? 2);
        
        $admin_id = (int)($_SESSION['usuario_id']   ?? 0);

        if (empty($nombre)) {
            return ['ok' => false, 'msg' => 'El nombre del negocio es requerido.'];
        }
        if ($apertura >= $cierre) {
            return ['ok' => false, 'msg' => 'La hora de apertura debe ser anterior al cierre.'];
        }

        $ok = $this->configModel->guardar($nombre, $logo, $apertura, $cierre, $duracion_cita, $t_cancelacion, $t_reprogram, $t_notificacion, $admin_id);

        if ($ok) {
            registrarAuditoria($this->conn, $admin_id, "ACTUALIZAR_CONFIGURACION", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Configuración guardada correctamente.' : 'Error al guardar configuración.'];
    }

    /** Resetea la configuración a valores por defecto */
    public function resetear(): array {
        $defaults = [
            'nombre_negocio'            => 'MC Barbería',
            'logo_url'                  => '',
            'horario_apertura'          => '08:00',
            'horario_cierre'            => '20:00',
            'duracion_cita_min'         => 30,
            'tiempo_cancelacion_min'    => 10,
            'tiempo_reprogramacion_min' => 10,
            'tiempo_notificacion_min'   => 2,
        ];
        return $this->guardar($defaults);
    }
}
