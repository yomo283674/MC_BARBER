<?php
/**
 * views/barbero/disponibilidad.php
 * Gestión de horarios disponibles del barbero.
 * Permite agregar slots manualmente, bloquear y generar semana completa.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'models/Disponibilidad.php';

$id_barbero = (int)$_SESSION['usuario_id'];
$dispModel  = new Disponibilidad();

// Semana a mostrar (por defecto, semana actual)
$semana_ini = $_GET['semana'] ?? date('Y-m-d', strtotime('monday this week'));
$semana_ini = date('Y-m-d', strtotime($semana_ini)); // normalizar a lunes
$dias = [];
for ($i = 0; $i < 7; $i++) {
    $dias[] = date('Y-m-d', strtotime("+{$i} days", strtotime($semana_ini)));
}
$semana_fin = end($dias);

// Cargar disponibilidad de la semana completa
global $conn;
$stmt = $conn->prepare(
    "SELECT d.*,
            (SELECT COUNT(*) FROM citas c
            WHERE c.id_barbero = d.id_barbero AND c.fecha = d.fecha AND c.hora = d.hora_inicio
            AND c.estado IN ('PENDIENTE','ACEPTADA','REPROGRAMADA')) AS tiene_cita
    FROM disponibilidad d
    WHERE d.id_barbero = ? AND d.fecha BETWEEN ? AND ?
    ORDER BY d.fecha ASC, d.hora_inicio ASC"
);
$stmt->bind_param('iss', $id_barbero, $semana_ini, $semana_fin);
$stmt->execute();
$todos_slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Agrupar por fecha
$por_fecha = [];
foreach ($todos_slots as $s) { $por_fecha[$s['fecha']][] = $s; }

$flash_ok  = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flash_err = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

$sem_ant = date('Y-m-d', strtotime('-7 days', strtotime($semana_ini)));
$sem_sig = date('Y-m-d', strtotime('+7 days', strtotime($semana_ini)));

$pagina_activa = 'disponibilidad';
$titulo_pagina = 'Mi Disponibilidad';
$diasNombres   = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Disponibilidad - Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .slots-container {
            max-height: 420px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .slots-container::-webkit-scrollbar {
            width: 4px;
        }
        .slots-container::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.02);
            border-radius: 4px;
        }
        .slots-container::-webkit-scrollbar-thumb {
            background: rgba(181, 138, 74, 0.3);
            border-radius: 4px;
        }
        .slots-container::-webkit-scrollbar-thumb:hover {
            background: rgba(181, 138, 74, 0.6);
        }
    </style>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'includes/nav_barbero.php'; ?>

<?php if ($flash_ok): ?>
    <div class="alert-flash alert-flash-ok"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($flash_ok) ?></div>
<?php endif; ?>
<?php if ($flash_err): ?>
    <div class="alert-flash alert-flash-err"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($flash_err) ?></div>
<?php endif; ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
            Mi Disponibilidad <i class="bi bi-calendar-week" style="color:var(--gold);font-size:24px;"></i>
        </h1>
        <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Configura tus horarios de atención semana a semana de forma rápida y sencilla.</p>
    </div>
    <div style="display:flex;gap:10px">
        <button class="btn btn-primary" onclick="abrirAgregarSlot()" style="height:44px;padding:0 24px;border-radius:12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;box-shadow:0 6px 16px rgba(181,138,74,0.25);transition:all 0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(181,138,74,0.35)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 6px 16px rgba(181,138,74,0.25)'">
            <i class="bi bi-plus-lg" style="font-size:16px"></i> Agregar slot
        </button>
    </div>
</div>

<!-- Navegación semanal -->
<div class="content-card" style="margin-bottom:24px; border:none; box-shadow:var(--shadow); border-radius:12px; overflow:hidden;">
    <div class="content-card-body" style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px; background:linear-gradient(to right, #fff, #fdfbf7, #fff);">
        <a href="?semana=<?= $sem_ant ?>" class="btn btn-sm" style="background:var(--white); color:var(--text); font-weight:600; padding:8px 16px; border-radius:8px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:all 0.2s;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text)'">
            <i class="bi bi-chevron-left me-1"></i> Sem. anterior
        </a>
        <div style="text-align:center;">
            <span style="font-weight:800; font-size:16px; color:var(--black); display:flex; align-items:center; gap:8px;">
                <i class="bi bi-calendar-range" style="color:var(--gold); font-size:18px;"></i>
                <?= date('d/m', strtotime($semana_ini)) ?> - <?= date('d/m/Y', strtotime($semana_fin)) ?>
            </span>
            <?php
                $lunes_actual = date('Y-m-d', strtotime('monday this week'));
                if ($semana_ini === $lunes_actual) {
                    $etiqueta_semana = 'Semana Actual';
                } elseif ($semana_ini === date('Y-m-d', strtotime('+1 week', strtotime($lunes_actual)))) {
                    $etiqueta_semana = 'Próxima Semana';
                } elseif ($semana_ini === date('Y-m-d', strtotime('-1 week', strtotime($lunes_actual)))) {
                    $etiqueta_semana = 'Semana Pasada';
                } else {
                    $etiqueta_semana = 'Semana seleccionada';
                }
            ?>
            <div style="font-size:12px; color:var(--text-muted); font-weight:600; margin-top:2px;"><?= $etiqueta_semana ?></div>
        </div>
        <a href="?semana=<?= $sem_sig ?>" class="btn btn-sm" style="background:var(--white); color:var(--text); font-weight:600; padding:8px 16px; border-radius:8px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:all 0.2s;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text)'">
            Sem. siguiente <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>
</div>

<!-- Grilla semanal -->
<div style="display:grid; grid-template-columns:repeat(7, minmax(180px, 1fr)); gap:12px; margin-bottom:24px; overflow-x:auto; padding-bottom:10px;">
    <?php foreach ($dias as $i => $dia): ?>
    <?php
        $es_hoy  = $dia === date('Y-m-d');
        $slots_d = $por_fecha[$dia] ?? [];
        $libre   = count(array_filter($slots_d, fn($s) => $s['disponible'] && !$s['tiene_cita']));
        $ocupado = count(array_filter($slots_d, fn($s) => $s['tiene_cita']));
        $bloq    = count(array_filter($slots_d, fn($s) => !$s['disponible']));
    ?>
    <div style="background:var(--white); border-radius:12px; border:2px solid <?= $es_hoy ? 'var(--gold)' : 'transparent' ?>; min-height:220px; box-shadow:var(--shadow-sm); display:flex; flex-direction:column; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow)'" onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
        
        <!-- Header del día -->
        <div style="position:relative; text-align:center; padding:12px; border-bottom:1px solid var(--border); background:<?= $es_hoy ? 'var(--gold-soft)' : 'var(--background)' ?>; border-radius:10px 10px 0 0;">
            <div style="position:absolute; top:8px; right:8px; display:flex; gap:4px;">
                <button onclick="abrirGenerarDia('<?= $dia ?>')" style="border:none; background:transparent; color:var(--gold); font-size:14px; cursor:pointer; opacity:0.6; transition:all 0.2s;" onmouseover="this.style.opacity='1'; this.style.transform='scale(1.1)'" onmouseout="this.style.opacity='0.6'; this.style.transform='scale(1)'" title="Generar horarios rápidos para el día">
                    <i class="bi bi-lightning-fill"></i>
                </button>
                <?php if (!empty($slots_d) && ($libre + $bloq) > 0): ?>
                <button onclick="eliminarDia('<?= $dia ?>')" style="border:none; background:transparent; color:var(--danger); font-size:14px; cursor:pointer; opacity:0.5; transition:all 0.2s;" onmouseover="this.style.opacity='1'; this.style.transform='scale(1.1)'" onmouseout="this.style.opacity='0.5'; this.style.transform='scale(1)'" title="Eliminar horarios libres del día">
                    <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
            </div>
            <div style="font-size:12px; color:<?= $es_hoy ? 'var(--gold)' : 'var(--text-muted)' ?>; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:2px;"><?= $diasNombres[$i] ?></div>
            <div style="font-size:22px; font-weight:800; color:<?= $es_hoy ? 'var(--gold)' : 'var(--black)' ?>;">
                <?= date('d', strtotime($dia)) ?>
            </div>
        </div>

        <!-- Cuerpo de slots -->
        <div class="slots-container" style="padding:12px; flex-grow:1; display:flex; flex-direction:column; gap:8px;">
            <?php if (empty($slots_d)): ?>
                <div style="text-align:center; font-size:12px; color:var(--text-light); margin:auto; padding:20px 0; font-style:italic;">Sin horarios</div>
            <?php else: ?>
                <?php foreach ($slots_d as $s): ?>
                <?php
                    if ($s['tiene_cita'])  { $bg='rgba(37,99,235,0.08)';  $col='var(--info)';  $icon='bi-person-check-fill'; }
                    elseif (!$s['disponible']) { $bg='rgba(220,38,38,0.08)'; $col='var(--danger)'; $icon='bi-slash-circle'; }
                    else                   { $bg='rgba(22,163,74,0.08)';   $col='var(--success)'; $icon='bi-check2-circle'; }
                ?>
                <div style="background:<?= $bg ?>; border:1px solid <?= $col ?>; color:<?= $col ?>; border-radius:10px; padding:8px 12px; font-size:12px; font-weight:700; display:flex; justify-content:space-between; align-items:center; transition:all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'">
                    <span style="letter-spacing:0.5px; display:flex; align-items:center; gap:4px;"><i class="bi bi-clock" style="opacity:0.7; font-size:13px;"></i><?= date('h:i A', strtotime($s['hora_inicio'])) ?></span>
                    <?php if (!$s['tiene_cita']): ?>
                    <div style="display:flex; gap:6px;">
                        <button onclick="toggleSlot(<?= $s['id_disponibilidad'] ?>, '<?= $dia ?>', '<?= $s['hora_inicio'] ?>', <?= $s['disponible'] ? 1 : 0 ?>)"
                                style="border:none; background:<?= $s['disponible'] ? 'rgba(22,163,74,0.1)' : 'rgba(220,38,38,0.1)' ?>; cursor:pointer; font-size:14px; color:<?= $col ?>; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:8px; transition:all 0.2s;"
                                onmouseover="this.style.background='<?= $col ?>'; this.style.color='#fff'; this.style.transform='scale(1.15)'" onmouseout="this.style.background='<?= $s['disponible'] ? 'rgba(22,163,74,0.1)' : 'rgba(220,38,38,0.1)' ?>'; this.style.color='<?= $col ?>'; this.style.transform='scale(1)'"
                                title="<?= $s['disponible'] ? 'Bloquear horario' : 'Activar horario' ?>">
                            <i class="bi <?= $icon ?>"></i>
                        </button>
                        <button onclick="eliminarSlot('<?= $dia ?>', '<?= $s['hora_inicio'] ?>')"
                                style="border:none; background:rgba(220,38,38,0.1); cursor:pointer; font-size:14px; color:var(--danger); width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:8px; transition:all 0.2s;"
                                onmouseover="this.style.background='var(--danger)'; this.style.color='#fff'; this.style.transform='scale(1.15)'" onmouseout="this.style.background='rgba(220,38,38,0.1)'; this.style.color='var(--danger)'; this.style.transform='scale(1)'"
                                title="Eliminar horario">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <div style="background:var(--info); color:#fff; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:8px; box-shadow:0 2px 4px rgba(37,99,235,0.2);" title="Horario reservado">
                        <i class="bi <?= $icon ?>" style="font-size:14px;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Botón agregar al final del día -->
        <div style="padding:10px;">
            <button onclick="abrirAgregarSlotFecha('<?= $dia ?>')"
                    style="width:100%; border:1px dashed var(--border); background:var(--white); border-radius:8px; padding:8px; font-size:12px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all 0.2s;"
                    onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)'; this.style.background='var(--gold-soft)'"
                    onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'; this.style.background='var(--white)'">
                <i class="bi bi-plus-lg"></i> Agregar Slot
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Leyenda -->
<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;background:var(--white);padding:16px 24px;border-radius:12px;border:none;box-shadow:var(--shadow-sm);align-items:center;">
    <span style="font-weight:700; color:var(--text); margin-right:8px; font-size:14px;"><i class="bi bi-info-circle me-1" style="color:var(--gold)"></i> Leyenda:</span>
    <span style="display:flex;align-items:center;gap:6px; background:rgba(22,163,74,0.1); color:var(--success); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; border:1px solid rgba(22,163,74,0.2);"><i class="bi bi-check2-circle" style="font-size:16px;"></i> Disponible</span>
    <span style="display:flex;align-items:center;gap:6px; background:rgba(37,99,235,0.1); color:var(--info); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; border:1px solid rgba(37,99,235,0.2);"><i class="bi bi-person-check-fill" style="font-size:16px;"></i> Con cita</span>
    <span style="display:flex;align-items:center;gap:6px; background:rgba(220,38,38,0.1); color:var(--danger); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; border:1px solid rgba(220,38,38,0.2);"><i class="bi bi-slash-circle" style="font-size:16px;"></i> Bloqueado</span>
</div>

<!-- Modal: Agregar Slot -->
<div class="modal-overlay" id="modalSlot">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-clock" style="color:var(--gold);margin-right:8px"></i>Agregar Horario</h3>
            <button class="modal-close" onclick="document.getElementById('modalSlot').classList.remove('open')"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body">
            <form id="formSlot" method="POST" action="<?= $base_path ?>controllers/barbero/disponibilidadController.php">
                <input type="hidden" name="accion" value="activar">
                <div class="form-group">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="slotFecha" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Hora inicio</label>
                        <div class="input-icon">
                            <i class="bi bi-clock"></i>
                            <input type="text" name="hora_inicio" class="form-control with-icon time-picker" placeholder="09:00 AM" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora fin</label>
                        <div class="input-icon">
                            <i class="bi bi-clock"></i>
                            <input type="text" name="hora_fin" class="form-control with-icon time-picker" placeholder="10:00 AM" required>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="document.getElementById('modalSlot').classList.remove('open')">Cancelar</button>
            <button class="btn btn-primary" onclick="document.getElementById('formSlot').submit()">
                <i class="bi bi-plus"></i> Agregar
            </button>
        </div>
    </div>
</div>

<!-- Modal: Generar Día -->
<div class="modal-overlay" id="modalDia">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-lightning" style="color:var(--gold);margin-right:8px"></i>Generar Horarios del Día</h3>
            <button class="modal-close" onclick="document.getElementById('modalDia').classList.remove('open')"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
                Se crearán slots automáticamente para el día seleccionado.
            </p>
            <form id="formDia" method="POST" action="<?= $base_path ?>controllers/barbero/disponibilidadController.php">
                <input type="hidden" name="accion" value="generar_dia">
                <input type="hidden" name="fecha" id="diaFecha">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Hora apertura</label>
                        <div class="input-icon">
                            <i class="bi bi-clock"></i>
                            <input type="text" name="hora_apertura" class="form-control with-icon time-picker" value="09:00" placeholder="09:00 AM" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora cierre</label>
                        <div class="input-icon">
                            <i class="bi bi-clock"></i>
                            <input type="text" name="hora_cierre" class="form-control with-icon time-picker" value="20:00" placeholder="08:00 PM" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Duración por turno (minutos)</label>
                    <select name="duracion_min" class="form-control">
                        <option value="30">30 min</option>
                        <option value="45">45 min</option>
                        <option value="60" selected>60 min</option>
                        <option value="90">90 min</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="document.getElementById('modalDia').classList.remove('open')">Cancelar</button>
            <button class="btn btn-primary" onclick="document.getElementById('formDia').submit()">
                <i class="bi bi-lightning"></i> Generar
            </button>
        </div>
    </div>
</div>

<!-- Form oculto para bloquear/activar -->
<form id="formToggle" method="POST" action="<?= $base_path ?>controllers/barbero/disponibilidadController.php" style="display:none">
    <input type="hidden" name="accion" id="toggleAccion">
    <input type="hidden" name="fecha" id="toggleFecha">
    <input type="hidden" name="hora_inicio" id="toggleHora">
</form>

<?php require_once $base_path . 'includes/nav_footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    flatpickr(".time-picker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: false,
        altInput: true,
        altFormat: "h:i K"
    });
});

function abrirAgregarSlot() {
    document.getElementById('slotFecha').value = '';
    document.getElementById('modalSlot').classList.add('open');
}
function abrirAgregarSlotFecha(fecha) {
    document.getElementById('slotFecha').value = fecha;
    document.getElementById('modalSlot').classList.add('open');
}

function abrirGenerarDia(fecha) {
    document.getElementById('diaFecha').value = fecha;
    document.getElementById('modalDia').classList.add('open');
}

function toggleSlot(id, fecha, hora, activo) {
    const accion = activo ? 'bloquear' : 'activar';
    const titulo = activo ? 'Bloquear horario' : 'Activar horario';
    const desc   = activo ? 'El horario ya no estará disponible para nuevas reservas en tu agenda.' : 'El horario volverá a estar disponible para que tus clientes lo reserven.';
    
    const iconColor = activo ? '#f59e0b' : '#10b981';
    const iconBg    = activo ? 'rgba(245,158,11,0.12)' : 'rgba(16,185,129,0.12)';
    const iconClass = activo ? 'bi-slash-circle' : 'bi-check2-circle';
    const btnCls    = activo ? 'swal-btn swal-btn-danger' : 'swal-btn swal-btn-success'; // Using danger visually for block action
    const btnTxt    = activo ? 'Bloquear' : 'Activar';

    const timeFormatted = new Date(`2000-01-01T${hora}`).toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});

    Swal.fire({ 
        html: `
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: ${iconBg}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="bi ${iconClass}" style="font-size: 20px; color: ${iconColor};"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #111827;">${titulo}</h3>
                    <p style="margin: 0 0 16px 0; font-size: 14px; color: #4b5563; line-height: 1.5;">
                        ${desc}
                    </p>
                    <div style="display: inline-flex; align-items: center; gap: 8px; background: #f3f4f6; padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; font-size: 14px;">
                        <i class="bi bi-clock" style="color: #6b7280;"></i> ${timeFormatted}
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: btnTxt,
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: btnCls,
            cancelButton: 'swal-btn swal-btn-secondary',
            actions: 'swal-actions-right'
        }
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('toggleAccion').value = accion;
            document.getElementById('toggleFecha').value  = fecha;
            document.getElementById('toggleHora').value   = hora;
            document.getElementById('formToggle').submit();
        }
    });
}

function eliminarSlot(fecha, hora) {
    const timeFormatted = new Date(`2000-01-01T${hora}`).toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
    
    Swal.fire({ 
        html: `
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-trash" style="font-size: 20px; color: #ef4444;"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #111827;">Eliminar horario</h3>
                    <p style="margin: 0 0 16px 0; font-size: 14px; color: #4b5563; line-height: 1.5;">
                        Se removerá el slot de las <strong style="color: #111827;">${timeFormatted}</strong>.
                    </p>
                    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 10px 12px; border-radius: 4px;">
                        <span style="color: #991b1b; font-size: 13px; font-weight: 500;">
                            Esta acción es irreversible y eliminará permanentemente este espacio de tu agenda.
                        </span>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: 'swal-btn swal-btn-danger',
            cancelButton: 'swal-btn swal-btn-secondary',
            actions: 'swal-actions-right'
        }
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('toggleAccion').value = 'eliminar';
            document.getElementById('toggleFecha').value  = fecha;
            document.getElementById('toggleHora').value   = hora;
            document.getElementById('formToggle').submit();
        }
    });
}

function eliminarDia(fecha) {
    const [y, m, d] = fecha.split('-');
    const dateFormatted = `${d}/${m}/${y}`;
    
    Swal.fire({ 
        html: `
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-exclamation-triangle" style="font-size: 20px; color: #ef4444;"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #111827;">Eliminar todos los horarios</h3>
                    <p style="margin: 0 0 16px 0; font-size: 14px; color: #4b5563; line-height: 1.5;">
                        Estás a punto de borrar todos los horarios <strong>libres</strong> del día <strong style="color: #111827;">${dateFormatted}</strong>.
                    </p>
                    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 10px 12px; border-radius: 4px;">
                        <span style="color: #991b1b; font-size: 13px; font-weight: 500;">
                            Se eliminarán de forma permanente todos los slots que no tengan citas programadas.
                        </span>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Eliminar todo',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: 'swal-btn swal-btn-danger',
            cancelButton: 'swal-btn swal-btn-secondary',
            actions: 'swal-actions-right'
        }
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('toggleAccion').value = 'eliminar_dia';
            document.getElementById('toggleFecha').value  = fecha;
            document.getElementById('formToggle').submit();
        }
    });
}
</script>
</body>
</html>

