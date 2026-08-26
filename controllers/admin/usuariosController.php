<?php
/**
 * controllers/admin/usuariosController.php
 * Gestión de usuarios (clientes + barberos) para el Administrador.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

class UsuariosController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Lista todos los usuarios (clientes y barberos) con filtros opcionales */
    public function listar(string $rol = '', string $estado = '', string $buscar = ''): array {
        $sql = "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.estado,
                       u.fecha_registro, u.ultimo_acceso, r.nombre AS rol, r.id_rol
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_rol IN (2, 3)";
        $params = '';
        $values = [];

        if ($rol) {
            $sql .= " AND r.nombre = ?";
            $params .= 's';
            $values[] = $rol;
        }
        if ($estado) {
            $sql .= " AND u.estado = ?";
            $params .= 's';
            $values[] = $estado;
        }
        if ($buscar) {
            $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
            $params .= 'ss';
            $values[] = "%$buscar%";
            $values[] = "%$buscar%";
        }

        $sql .= " ORDER BY u.fecha_registro DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($params, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Obtiene un usuario por ID */
    public function obtener(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT u.*, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id_rol
             WHERE u.id_usuario = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Cambia el estado de un usuario */
    public function cambiarEstado(int $id, string $estado): array {
        $estados = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
        if (!in_array($estado, $estados)) {
            return ['ok' => false, 'msg' => 'Estado inválido.'];
        }
        $stmt = $this->conn->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
        $stmt->bind_param('si', $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            registrarAuditoria($this->conn, $_SESSION['usuario_id'], "CAMBIO_ESTADO_USUARIO: ID=$id → $estado", "EXITOSO");
        }
        return ['ok' => $ok, 'msg' => $ok ? "Estado cambiado a $estado." : 'Error al cambiar estado.'];
    }

    /** Estadísticas resumidas */
    public function getStats(): array {
        $stmt = $this->conn->prepare(
            "SELECT id_rol, estado, COUNT(*) AS total FROM usuarios GROUP BY id_rol, estado"
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stats = ['clientes_activos' => 0, 'barberos_activos' => 0, 'suspendidos' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $stats['total'] += $r['total'];
            if ($r['id_rol'] == 3 && $r['estado'] === 'ACTIVO') $stats['clientes_activos'] += $r['total'];
            if ($r['id_rol'] == 2 && $r['estado'] === 'ACTIVO') $stats['barberos_activos'] += $r['total'];
            if ($r['estado'] === 'SUSPENDIDO') $stats['suspendidos'] += $r['total'];
        }
        return $stats;
    }
}
