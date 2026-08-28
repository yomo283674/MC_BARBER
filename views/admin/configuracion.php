<?php
/**
 * views/admin/configuracion.php
 * Panel de Configuración Global del Negocio para el Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/configuracionController.php';
global $conn;

$ctrl = new ConfiguracionController($conn);

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'resetear') {
        $flash = $ctrl->resetear();
    } else {
        $flash = $ctrl->guardar($_POST);
    }
}

$config = $ctrl->obtener();

$pagina_activa = 'configuracion';
$titulo_pagina = 'Configuración | MC Barber';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?></title>
    <meta name="description" content="Configuración global del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 900px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .config-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .config-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px -10px rgba(0,0,0,0.12);
        }
        .config-card-header {
            padding: 24px 30px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
        }
        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(212,175,55,0.15) 0%, rgba(212,175,55,0.05) 100%);
            border: 1px solid rgba(212,175,55,0.2);
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(212,175,55,0.1);
            flex-shrink: 0;
        }
        .config-card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #111827;
        }
        .config-card-header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .config-card-body {
            padding: 30px;
            flex: 1;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .helper-text {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon-wrapper i {
            position: absolute;
            left: 1px;
            top: 1px;
            bottom: 1px;
            width: 44px;
            background: #f8fafc;
            border-right: 1px solid #e5e7eb;
            border-radius: 11px 0 0 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .input-icon-wrapper input {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 18px 12px 56px;
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .input-icon-wrapper input:focus {
            background: #ffffff;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212,175,55,0.15);
            outline: none;
        }
        .input-icon-wrapper:has(input:focus) i {
            color: var(--gold);
            border-color: rgba(212,175,55,0.3);
            background: rgba(212,175,55,0.05);
        }
        
        .footer-actions {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            padding: 24px 32px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .btn-reset {
            background: rgba(220, 38, 38, 0.05);
            color: #dc2626;
            border: 1px solid rgba(220, 38, 38, 0.15);
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }
        .btn-reset:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.25);
        }
        .btn-reset:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #d4af37 0%, #b58a4a 100%);
            color: #ffffff;
            border: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 14.5px;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(212, 175, 55, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .btn-save::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: skewX(-20deg);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(212, 175, 55, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            background: linear-gradient(135deg, #dfbc4c 0%, #c19854 100%);
        }
        .btn-save:hover::before {
            left: 150%;
        }
        .btn-save:active {
            transform: translateY(1px);
            box-shadow: 0 5px 15px -5px rgba(212, 175, 55, 0.4);
        }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

<!-- MAIN -->
<div class="main-content">
    <div class="page-content">
        
        <!-- Page Header -->
        <div class="page-header" style="margin-bottom: 30px;">
            <h1>Configuración</h1>
            <p>Ajustes generales, horarios y políticas de negocio de la Barbería</p>
        </div>

        <form method="POST" id="configForm" enctype="multipart/form-data">
            <input type="hidden" name="accion" id="formAccion" value="guardar">
            
            <div class="config-grid">
                
                <!-- Tarjeta 1: Info Negocio -->
                <div class="config-card">
                    <div class="config-card-header">
                        <div class="header-icon"><i class="bi bi-shop"></i></div>
                        <div>
                            <h3>Información General</h3>
                            <p>Datos principales y de contacto de la barbería</p>
                        </div>
                    </div>
                    <div class="config-card-body">
                        <div class="form-group">
                            <label class="form-label">Nombre del Negocio *</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-buildings"></i>
                                <input type="text" name="nombre_negocio" class="form-control" value="<?= htmlspecialchars($config['nombre_negocio']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top:20px;">
                            <label class="form-label">Logotipo (Archivo)</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-image"></i>
                                <input type="file" name="logo_archivo" class="form-control" accept="image/*" style="padding-top: 9px;">
                            </div>
                            <?php if (!empty($config['logo_url'])): ?>
                                <div class="helper-text"><i class="bi bi-check-circle"></i> Logotipo actual guardado. Sube uno nuevo para reemplazarlo.</div>
                            <?php else: ?>
                                <div class="helper-text"><i class="bi bi-info-circle"></i> Sube la imagen del logo del negocio (opcional).</div>
                            <?php endif; ?>
                            <input type="hidden" name="logo_url_actual" value="<?= htmlspecialchars($config['logo_url']) ?>">
                        </div>

                        <h4 style="margin-top: 30px; margin-bottom: 16px; font-size: 14px; font-weight: 800; color: #111827; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px;">Horarios de Atención</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Hora Apertura *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-clock"></i>
                                    <input type="time" name="horario_apertura" class="form-control" value="<?= substr($config['horario_apertura'], 0, 5) ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora Cierre *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-clock-history"></i>
                                    <input type="time" name="horario_cierre" class="form-control" value="<?= substr($config['horario_cierre'], 0, 5) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="helper-text"><i class="bi bi-exclamation-triangle"></i> Define los límites para la reserva de citas en línea.</div>
                    </div>
                </div>

                <!-- Tarjeta 2: Políticas -->
                <div class="config-card">
                    <div class="config-card-header">
                        <div class="header-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h3>Políticas del Negocio</h3>
                            <p>Reglas operativas de citas y notificaciones</p>
                        </div>
                    </div>
                    <div class="config-card-body">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Duración Base Cita (min) *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-hourglass-split"></i>
                                    <input type="number" name="duracion_cita_min" class="form-control" value="<?= $config['duracion_cita_min'] ?>" min="5" step="5" required>
                                </div>
                                <div class="helper-text">Usada si un servicio no especifica duración.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Límite Cancelación (min) *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-x-circle"></i>
                                    <input type="number" name="tiempo_cancelacion_min" class="form-control" value="<?= $config['tiempo_cancelacion_min'] ?>" min="0" required>
                                </div>
                                <div class="helper-text">Minutos antes para poder cancelar.</div>
                            </div>
                        </div>

                        <div class="form-row" style="margin-top: 20px;">
                            <div class="form-group">
                                <label class="form-label">Límite Reprogramación (min) *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-arrow-repeat"></i>
                                    <input type="number" name="tiempo_reprogramacion_min" class="form-control" value="<?= $config['tiempo_reprogramacion_min'] ?>" min="0" required>
                                </div>
                                <div class="helper-text">Límite para respuesta a reprogramaciones.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tiempo Notificación (min) *</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-bell"></i>
                                    <input type="number" name="tiempo_notificacion_min" class="form-control" value="<?= $config['tiempo_notificacion_min'] ?>" min="0" required>
                                </div>
                                <div class="helper-text">Cuándo enviar recordatorio al cliente.</div>
                            </div>
                        </div>

                        <div style="margin-top: 30px; padding: 20px; background: linear-gradient(to right, rgba(212,175,55,0.05), rgba(212,175,55,0.02)); border-radius: 16px; border-left: 4px solid var(--gold); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                            <h5 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 800; color: #111827; display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-info-circle-fill" style="color: var(--gold);"></i> Información Técnica
                            </h5>
                            <p style="margin: 0; font-size: 13px; color: #4b5563; line-height: 1.6;">
                                Estos parámetros afectan directamente las reglas del sistema y cómo se evalúa la disponibilidad y el flujo de los turnos en cola. Los cambios surten efecto inmediatamente para las nuevas solicitudes.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Acciones -->
            <div class="footer-actions">
                <button type="button" class="btn-reset" onclick="confirmarReset()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar Valores por Defecto
                </button>
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle"></i> Guardar Configuración
                </button>
            </div>

        </form>

    </div><!-- /page-content -->
</div><!-- /main-content -->

<script>
function confirmarReset() {
    Swal.fire({
        title: '¿Restaurar valores?',
        text: 'Se cargarán los valores predeterminados del sistema.',
        icon: 'warning',
        showCancelButton: true,
        buttonsStyling: false,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, restaurar',
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: 'swal-btn swal-btn-danger',
            cancelButton: 'swal-btn swal-btn-secondary',
            actions: 'swal-actions-right'
        }
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('formAccion').value = 'resetear';
            document.getElementById('configForm').submit();
        }
    });
}

<?php if ($flash): ?>
Swal.fire({
    icon: '<?= $flash['ok'] ? 'success' : 'error' ?>',
    title: '<?= $flash['ok'] ? '¡Configuración Guardada!' : 'Error' ?>',
    text: '<?= addslashes($flash['msg']) ?>',
    buttonsStyling: false,
    customClass: {
        popup: 'swal-ultra-modern',
        confirmButton: 'swal-btn swal-btn-primary'
    }
});
<?php endif; ?>
</script>
</body>
</html>
