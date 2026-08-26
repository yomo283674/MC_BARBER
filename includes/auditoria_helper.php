<?php
/**
 * auditoria_helper.php
 * Registra acciones del sistema en la tabla auditoria.
 */

/**
 * Registra un evento de auditoría.
 *
 * @param mysqli $conn            Conexión activa a la BD
 * @param int    $id_usuario      ID del usuario que ejecuta la acción
 * @param string $accion          Nombre de la acción (ej: 'LOGIN', 'CANCELAR_CITA')
 * @param string $resultado       'EXITOSO' | 'FALLIDO'
 * @param string $entidad         Tabla/entidad afectada (ej: 'citas', 'usuarios')
 * @param string $detalle         Descripción detallada libre
 * @param string $observacion     Observación adicional (opcional)
 */
function registrarAuditoria(
    $conn,
    int    $id_usuario,
    string $accion,
    string $resultado      = 'EXITOSO',
    string $entidad        = '',
    string $detalle        = '',
    string $observacion    = ''
): void {
    try {
        $stmt = $conn->prepare(
            "INSERT INTO auditoria (id_usuario, accion, resultado, entidad_afectada, detalle, observacion)
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssss', $id_usuario, $accion, $resultado, $entidad, $detalle, $observacion);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Auditoría nunca debe romper el flujo principal
        error_log('Auditoria error: ' . $e->getMessage());
    }
}
