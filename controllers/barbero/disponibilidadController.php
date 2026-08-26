<?php
/**
 * controllers/barbero/disponibilidadController.php
 * Gestión de disponibilidad horaria del barbero.
 * Acciones: activar, bloquear, generar_semana.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Disponibilidad.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

verificarRol(['BARBERO']);

$id_barbero  = (int)$_SESSION['usuario_id'];
$accion      = $_POST['accion'] ?? '';
$dispModel   = new Disponibilidad();

function resp(bool $ok, string $msg, string $url = '../../views/barbero/disponibilidad.php'): void {
    $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $msg;
    header("Location: $url");
    exit();
}

switch ($accion) {

    case 'activar':
        $fecha      = trim($_POST['fecha']      ?? '');
        $hora_ini   = trim($_POST['hora_inicio'] ?? '');
        $hora_fin   = trim($_POST['hora_fin']    ?? '');
        if (!$fecha || !$hora_ini || !$hora_fin) {
            resp(false, 'Datos incompletos.');
        }
        $ok = $dispModel->activar($id_barbero, $fecha, $hora_ini, $hora_fin);
        registrarAuditoria($conn, $id_barbero, 'ACTIVAR_DISPONIBILIDAD',
            $ok ? 'EXITOSO' : 'FALLIDO', 'disponibilidad', "$fecha $hora_ini-$hora_fin");
        resp($ok, $ok ? 'Horario activado correctamente.' : 'Error al activar.');

    case 'bloquear':
        $fecha    = trim($_POST['fecha']      ?? '');
        $hora_ini = trim($_POST['hora_inicio'] ?? '');
        if (!$fecha || !$hora_ini) {
            resp(false, 'Datos incompletos.');
        }
        $res = $dispModel->bloquear($id_barbero, $fecha, $hora_ini);
        registrarAuditoria($conn, $id_barbero, 'BLOQUEAR_DISPONIBILIDAD',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'disponibilidad', "$fecha $hora_ini");
        resp($res['ok'], $res['mensaje']);

    case 'generar_dia':
        $fecha        = trim($_POST['fecha']  ?? '');
        $hora_apertura= trim($_POST['hora_apertura'] ?? '09:00:00');
        $hora_cierre  = trim($_POST['hora_cierre']   ?? '20:00:00');
        $duracion     = (int)($_POST['duracion_min'] ?? 30);
        if (!$fecha) {
            resp(false, 'Fecha requerida.');
        }
        $creados = $dispModel->generarDia($id_barbero, $fecha, $hora_apertura, $hora_cierre, $duracion);
        registrarAuditoria($conn, $id_barbero, 'GENERAR_DIA',
            'EXITOSO', 'disponibilidad', "fecha=$fecha creados=$creados");
        resp(true, "Se generaron $creados slots de disponibilidad para el día.");

    case 'eliminar':
        $fecha    = trim($_POST['fecha']      ?? '');
        $hora_ini = trim($_POST['hora_inicio'] ?? '');
        if (!$fecha || !$hora_ini) {
            resp(false, 'Datos incompletos.');
        }
        $res = $dispModel->eliminar($id_barbero, $fecha, $hora_ini);
        registrarAuditoria($conn, $id_barbero, 'ELIMINAR_DISPONIBILIDAD',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'disponibilidad', "$fecha $hora_ini");
        resp($res['ok'], $res['mensaje']);

    case 'eliminar_dia':
        $fecha = trim($_POST['fecha'] ?? '');
        if (!$fecha) {
            resp(false, 'Fecha no especificada.');
        }
        $res = $dispModel->eliminarDia($id_barbero, $fecha);
        registrarAuditoria($conn, $id_barbero, 'ELIMINAR_DIA_DISPONIBILIDAD',
            $res['ok'] ? 'EXITOSO' : 'FALLIDO', 'disponibilidad', "fecha=$fecha");
        resp($res['ok'], $res['mensaje']);

    default:
        resp(false, 'Acción no reconocida.');
}
