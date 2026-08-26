<?php
require_once __DIR__ . '/../config/database.php';

class Auditoria {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function obtenerTodas(int $limit = 200): array {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre AS usuario_nombre, r.nombre AS rol
            FROM auditoria a
            INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
            INNER JOIN roles    r ON u.id_rol     = r.id_rol
            ORDER BY a.fecha_hora DESC
            LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function filtrar(string $desde = null, string $hasta = null, string $accion = null): array {
        $sql = "SELECT a.*, u.nombre AS usuario_nombre, r.nombre AS rol
                FROM auditoria a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN roles    r ON u.id_rol     = r.id_rol
                WHERE 1=1";
        $params = '';
        $values = [];

        if ($desde) { $sql .= " AND DATE(a.fecha_hora) >= ?"; $params .= 's'; $values[] = $desde; }
        if ($hasta) { $sql .= " AND DATE(a.fecha_hora) <= ?"; $params .= 's'; $values[] = $hasta; }
        if ($accion) { $sql .= " AND a.accion LIKE ?"; $params .= 's'; $values[] = "%$accion%"; }

        $sql .= " ORDER BY a.fecha_hora DESC LIMIT 500";

        $stmt = $this->conn->prepare($sql);
        if ($params) { $stmt->bind_param($params, ...$values); }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
