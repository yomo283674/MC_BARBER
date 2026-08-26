<?php
/**
 * controllers/barbero/serviciosController.php
 * CRUD de servicios creados por el barbero, incluyendo imagen.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Servicio.php';
require_once __DIR__ . '/../../includes/auditoria_helper.php';

verificarRol(['BARBERO']);

$id_barbero = (int)$_SESSION['usuario_id'];
$accion     = $_POST['accion'] ?? $_GET['accion'] ?? '';
$m_servicio = new Servicio();

function resp($ok, $msg) {
    $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $msg;
    header("Location: ../../views/barbero/servicios.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($accion) {
        case 'crear':
        case 'actualizar':
            $id          = (int)($_POST['id_servicio'] ?? 0);
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio      = (float)($_POST['precio'] ?? 0);
            $duracion    = (int)($_POST['duracion_min'] ?? 30);
            $imagen      = null;

            if (!$nombre || $precio <= 0 || $duracion <= 0) {
                resp(false, "Datos inválidos o incompletos.");
            }

            // Manejo de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $fileTmp   = $_FILES['imagen']['tmp_name'];
                $fileName  = $_FILES['imagen']['name'];
                $ext       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $uploadDir = __DIR__ . '/../../public/uploads/servicios/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $newName = uniqid('srv_') . '.' . $ext;
                    if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                        $imagen = $newName;
                    }
                } else {
                    resp(false, "Formato de imagen no permitido (solo JPG, PNG, WEBP).");
                }
            }

            if ($accion === 'crear') {
                $ok = $m_servicio->crear($nombre, $descripcion, $precio, $duracion, null, $imagen, $id_barbero);
                registrarAuditoria($conn, $id_barbero, 'CREAR_SERVICIO', $ok ? 'EXITOSO' : 'FALLIDO', 'servicios', $nombre);
                resp($ok, $ok ? "Servicio creado con éxito." : "Error al crear el servicio.");
            } else {
                // Verificar que el servicio pertenece a este barbero
                $srvActual = $m_servicio->obtenerPorId($id);
                if (!$srvActual || $srvActual['id_barbero'] != $id_barbero) {
                    resp(false, "No tienes permiso para editar este servicio.");
                }
                $ok = $m_servicio->actualizar($id, $nombre, $descripcion, $precio, $duracion, $imagen);
                registrarAuditoria($conn, $id_barbero, 'EDITAR_SERVICIO', $ok ? 'EXITOSO' : 'FALLIDO', 'servicios', "ID: $id");
                resp($ok, $ok ? "Servicio actualizado." : "Error al actualizar.");
            }
            break;

        case 'eliminar':
            $id = (int)($_POST['id_servicio'] ?? 0);
            $srvActual = $m_servicio->obtenerPorId($id);
            if (!$srvActual || $srvActual['id_barbero'] != $id_barbero) {
                resp(false, "Permiso denegado.");
            }
            $ok = $m_servicio->eliminar($id);
            registrarAuditoria($conn, $id_barbero, 'ELIMINAR_SERVICIO', $ok ? 'EXITOSO' : 'FALLIDO', 'servicios', "ID: $id");
            resp($ok, $ok ? "Servicio eliminado." : "Error al eliminar. Puede que tenga citas asociadas.");
            break;
    }
}

// Para obtener los servicios en la vista
function getserviciosParaBarbero(): array {
    global $m_servicio, $id_barbero;
    return $m_servicio->obtenerPorBarbero($id_barbero);
}
