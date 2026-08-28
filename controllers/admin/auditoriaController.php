<?php
/**
 * controllers/admin/auditoriaController.php
 * Consulta del log de auditoría para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Auditoria.php';

class AuditoriaController {

    private $conn;
    private Auditoria $auditModel;

    public function __construct($conn) {
        $this->conn       = $conn;
        $this->auditModel = new Auditoria();
    }

    /** Lista registros de auditoría con filtros opcionales */
    public function listar(string $desde = '', string $hasta = '', string $accion = '', string $resultado = ''): array {
        $sql = "SELECT a.*, u.nombre AS usuario_nombre, r.nombre AS rol
                FROM auditoria a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN roles    r ON u.id_rol     = r.id_rol
                WHERE r.nombre = 'BARBERO'";
        $params = '';
        $values = [];

        if ($desde) {
            $sql .= " AND DATE(a.fecha_hora) >= ?";
            $params .= 's'; $values[] = $desde;
        }
        if ($hasta) {
            $sql .= " AND DATE(a.fecha_hora) <= ?";
            $params .= 's'; $values[] = $hasta;
        }
        if ($accion) {
            $sql .= " AND a.accion LIKE ?";
            $params .= 's'; $values[] = "%$accion%";
        }
        if ($resultado) {
            $sql .= " AND a.resultado = ?";
            $params .= 's'; $values[] = $resultado;
        }

        $sql .= " ORDER BY a.fecha_hora DESC LIMIT 500";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($params, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Estadísticas de auditoría */
    public function getStats(): array {
        $stmt = $this->conn->prepare(
            "SELECT a.resultado, COUNT(*) AS total 
             FROM auditoria a
             INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
             INNER JOIN roles    r ON u.id_rol     = r.id_rol
             WHERE r.nombre = 'BARBERO'
             GROUP BY a.resultado"
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stats = ['exitosos' => 0, 'fallidos' => 0, 'total' => 0];
        foreach ($rows as $r) {
            if ($r['resultado'] === 'EXITOSO') $stats['exitosos'] = $r['total'];
            if ($r['resultado'] === 'FALLIDO') $stats['fallidos'] = $r['total'];
            $stats['total'] += $r['total'];
        }
        return $stats;
    }
}
