<?php
/**
 * citasController.php (CLIENTE)
 * Maneja: crear cita, cancelar, responder reprogramacion, 
 *         AJAX para barberos disponibles y slots de horarios.
 */
define('PROFUNDIDAD', __DIR__ . '/../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);

require_once PROFUNDIDAD . 'models/Cita.php';
require_once PROFUNDIDAD . 'models/Disponibilidad.php';

$id_cliente = usuarioId();
$accion     = $_GET['accion'] ?? $_POST['accion'] ?? '';

// AJAX: Barberos disponibles para una fecha
if ($accion === 'barberos' && isset($_GET['fecha'])) {
    header('Content-Type: application/json');
    $fecha = $_GET['fecha'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode([]);
        exit();
    }
    $disponibModel = new Disponibilidad();
    echo json_encode($disponibModel->barberosPorFecha($fecha));
    exit();
}

// AJAX: Slots disponibles para barbero + fecha
if ($accion === 'slots' && isset($_GET['barbero'], $_GET['fecha'])) {
    header('Content-Type: application/json');
    $id_barbero = (int)$_GET['barbero'];
    $fecha      = $_GET['fecha'];
    if (!$id_barbero || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode([]);
        exit();
    }
    $disponibModel = new Disponibilidad();
    echo json_encode($disponibModel->obtenerDisponibles($id_barbero, $fecha));
    exit();
}

// POST: Crear cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
    $id_servicio = (int)($_POST['id_servicio'] ?? 0);
    $id_barbero  = (int)($_POST['id_barbero']  ?? 0);
    $fecha       = trim($_POST['fecha'] ?? '');
    $hora        = trim($_POST['hora']  ?? '');

    if (!$id_servicio || !$id_barbero || !$fecha || !$hora) {
        redirect('agendar', 'error', 'Datos incompletos.');
    }

    // Validar fecha no es pasada
    if ($fecha < date('Y-m-d')) {
        redirect('agendar', 'error', 'No puedes agendar en una fecha pasada.');
    }

    $citaModel = new Cita();
    $resultado = $citaModel->crear($id_cliente, $id_barbero, $id_servicio, $fecha, $hora);

    if ($resultado['ok']) {
        redirect('mis_citas', 'success', 'Tu cita ha sido agendada correctamente.');
    } else {
        redirect('agendar', 'error', $resultado['mensaje']);
    }
}

// POST: Cancelar cita (Regla 3 min)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'cancelar') {
    $id_cita = (int)($_POST['id_cita'] ?? 0);
    if (!$id_cita) redirect('mis_citas', 'error', 'Cita no válida.');

    $citaModel = new Cita();
    $resultado = $citaModel->cancelarCliente($id_cita, $id_cliente);

    if ($resultado['ok']) {
        redirect('mis_citas', 'success', 'Cita cancelada correctamente.');
    } else {
        redirect('mis_citas', 'error', $resultado['mensaje']);
    }
}

// POST: Responder reprogramacion (Regla 3 min)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'responder_reprogramacion') {
    $id_cita  = (int)($_POST['id_cita']   ?? 0);
    $respuesta = strtoupper(trim($_POST['respuesta'] ?? ''));

    if (!$id_cita || !in_array($respuesta, ['CONFIRMAR','CANCELAR'])) {
        redirect('dashboard', 'error', 'Datos inválidos.');
    }

    $citaModel = new Cita();
    $resultado = $citaModel->responderReprogramacion($id_cita, $id_cliente, $respuesta);

    redirect('mis_citas', $resultado['ok'] ? 'success' : 'warning', $resultado['mensaje']);
}


// Default: Redirigir a mis citas

redirect('mis_citas', 'warning', 'Acción no reconocida.');

// Helper de redirección con mensaje

function redirect(string $pagina, string $tipo, string $msg): void {
    $_SESSION['flash_tipo'] = $tipo;
    $_SESSION['flash_msg']  = $msg;
    header("Location: ../../views/cliente/{$pagina}.php");
    exit();
}
