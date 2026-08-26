<?php
/**
 * controllers/barbero/citasController.php
 * Gestión de citas para el barbero autenticado.
 * Acciones: aceptar, completar, cancelar, reprogramar.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cita.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

verificarRol(['BARBERO']);

$id_barbero = (int)$_SESSION['usuario_id'];
$accion     = $_REQUEST['accion'] ?? '';
$id_cita    = (int)($_REQUEST['id_cita'] ?? 0);
$citaModel  = new Cita();

function responder(bool $ok, string $msg, string $redirect): void {
    $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $msg;
    header("Location: $redirect");
    exit();
}

$back = '../../views/barbero/citas.php';

switch ($accion) {

    case 'aceptar':
        $res = $citaModel->aceptar($id_cita, $id_barbero);
        registrarAuditoria($conn, $id_barbero, 'ACEPTAR_CITA',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'citas', "id_cita=$id_cita");
        responder($res['ok'], $res['mensaje'], $back);

    case 'completar':
        $res = $citaModel->completar($id_cita, $id_barbero);
        $ok  = $res['ok'] ?? false;
        registrarAuditoria($conn, $id_barbero, 'COMPLETAR_CITA',
            $ok ? 'EXITOSO' : 'FALLIDO', 'citas', "id_cita=$id_cita");
        responder($ok, $ok ? 'Cita marcada como completada.' : 'Error al completar.', $back);

    case 'cancelar':
        $motivo = trim($_REQUEST['motivo'] ?? '');
        $res    = $citaModel->cancelarBarbero($id_cita, $id_barbero, $motivo);
        registrarAuditoria($conn, $id_barbero, 'CANCELAR_CITA',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'citas', "id_cita=$id_cita motivo=$motivo");
        responder($res['ok'], $res['mensaje'], $back);

    case 'reprogramar':
        $nueva_fecha = trim($_REQUEST['nueva_fecha'] ?? '');
        $nueva_hora  = trim($_REQUEST['nueva_hora'] ?? '');
        if (!$nueva_fecha || !$nueva_hora) {
            responder(false, 'Debes indicar la nueva fecha y hora.', $back);
        }
        $res = $citaModel->reprogramar($id_cita, $id_barbero, $nueva_fecha, $nueva_hora);
        registrarAuditoria($conn, $id_barbero, 'REPROGRAMAR_CITA',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'citas',
            "id_cita=$id_cita nueva=$nueva_fecha $nueva_hora");
        responder($res['ok'], $res['mensaje'], $back);

    default:
        responder(false, 'Acción no reconocida.', $back);
}
