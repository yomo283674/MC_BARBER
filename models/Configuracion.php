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
    public function guardar(string $nombre_negocio, string $logo_url, string $hora_apertura, string $hora_cierre, int $id_admin): bool {
        $config = $this->obtener();

        if ($config) {
            $stmt = $this->conn->prepare(
                "UPDATE configuracion_sistema
                SET nombre_negocio = ?, logo_url = ?, horario_apertura = ?, horario_cierre = ?,
                    id_administrador_actualizacion = ?, fecha_actualizacion = NOW()
                WHERE id_configuracion = ?"
            );
            $stmt->bind_param('ssssis', $nombre_negocio, $logo_url, $hora_apertura, $hora_cierre, $id_admin, $config['id_configuracion']);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO configuracion_sistema (nombre_negocio, logo_url, horario_apertura, horario_cierre, id_administrador_actualizacion, fecha_actualizacion)
                VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param('ssssi', $nombre_negocio, $logo_url, $hora_apertura, $hora_cierre, $id_admin);
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
