<?php
require_once __DIR__ . '/../config/database.php';

class Configuracion {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /** Obtiene la configuracion global (registro singleton) */
    public function obtener(): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM configuracion_sistema LIMIT 1");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Actualiza o crea la configuracion global */
    public function guardar(string $nombre_negocio, string $logo_url, string $hora_apertura, string $hora_cierre, int $duracion_cita_min, int $tiempo_cancelacion_min, int $tiempo_reprogramacion_min, int $tiempo_notificacion_min, int $id_admin): bool {
        $config = $this->obtener();

        if ($config) {
            $stmt = $this->conn->prepare(
                "UPDATE configuracion_sistema
                SET nombre_negocio = ?, logo_url = ?, horario_apertura = ?, horario_cierre = ?,
                    duracion_cita_min = ?, tiempo_cancelacion_min = ?, tiempo_reprogramacion_min = ?, tiempo_notificacion_min = ?,
                    id_administrador_actualizacion = ?, fecha_actualizacion = NOW()
                WHERE id_configuracion = ?"
            );
            $stmt->bind_param('ssssiiiiii', $nombre_negocio, $logo_url, $hora_apertura, $hora_cierre, $duracion_cita_min, $tiempo_cancelacion_min, $tiempo_reprogramacion_min, $tiempo_notificacion_min, $id_admin, $config['id_configuracion']);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO configuracion_sistema (nombre_negocio, logo_url, horario_apertura, horario_cierre, duracion_cita_min, tiempo_cancelacion_min, tiempo_reprogramacion_min, tiempo_notificacion_min, id_administrador_actualizacion, fecha_actualizacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param('ssssiiiii', $nombre_negocio, $logo_url, $hora_apertura, $hora_cierre, $duracion_cita_min, $tiempo_cancelacion_min, $tiempo_reprogramacion_min, $tiempo_notificacion_min, $id_admin);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Valor por defecto si no hay configuracion */
    public function nombreNegocio(): string {
        $config = $this->obtener();
        return $config['nombre_negocio'] ?? 'MC Barbería';
    }
}
