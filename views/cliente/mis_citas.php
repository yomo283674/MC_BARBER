<?php
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);

require_once PROFUNDIDAD . 'models/Cita.php';

$id_cliente = usuarioId();
$citaModel  = new Cita();
$todasCitas = $citaModel->obtenerPorCliente($id_cliente);

$proximas   = array_filter($todasCitas, fn($c) => in_array($c['estado'], ['PENDIENTE','ACEPTADA','REPROGRAMADA']) && $c['fecha'] >= date('Y-m-d'));
$anteriores = array_filter($todasCitas, fn($c) => in_array($c['estado'], ['COMPLETADA']) || ($c['fecha'] < date('Y-m-d') && $c['estado'] !== 'CANCELADA'));
$canceladas = array_filter($todasCitas, fn($c) => $c['estado'] === 'CANCELADA');

// Flash message
$flash_tipo = $_SESSION['flash_tipo'] ?? '';
$flash_msg  = $_SESSION['flash_msg']  ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_msg']);

$pagina_activa = 'mis_citas';
$titulo_pagina = 'Mis Citas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas â€” MC Barbería</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_cliente.php'; ?>

<?php if ($flash_msg): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?= htmlspecialchars($flash_tipo) ?>',
        title: '<?= $flash_tipo === 'success' ? '¡Listo!' : ($flash_tipo === 'error' ? 'Error' : 'Aviso') ?>',
        text: '<?= addslashes(htmlspecialchars($flash_msg)) ?>',
        confirmButtonColor: '#b58a4a',
        timer: 4000,
        timerProgressBar: true
    });
});
</script>
<?php endif; ?>

<div class="page-header">
    <h1>Mis Citas</h1>
    <p>Gestiona todas tus citas en la barbería.</p>
</div>

<!-- PROXIMAS CITAS -->
<div class="content-card">
    <div class="content-card-header">
        <h3><i class="bi bi-calendar-check" style="color:var(--gold);margin-right:8px;"></i>Próximas Citas</h3>
        <a href="agendar.php" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nueva cita</a>
    </div>
    <div class="content-card-body">
        <?php if (empty($proximas)): ?>
            <div class="empty-state"><i class="bi bi-calendar-x"></i><h3>No tienes citas próximas</h3><p>Agenda tu primera cita con nosotros.</p></div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($proximas as $cita):
                $secs = $citaModel->segundosRestantes($cita['fecha_creacion']);
                $puedeCanc = $secs > 0 && in_array($cita['estado'], ['PENDIENTE']);
                $repr = null;
                if ($cita['estado'] === 'REPROGRAMADA') {
                    $repr = $citaModel->obtenerReprogramacionPendiente((int)$cita['id_cita']);
                }
            ?>
            <div class="premium-ticket" style="background: linear-gradient(145deg, #111827, #000000); border-radius: 20px; color: #fff; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid rgba(212,175,55,0.2); width: 100%; overflow: hidden; display: flex; flex-direction: column; margin-bottom: 24px;">
                <!-- Gold glow effect -->
                <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(212,175,55,0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
                
                <!-- Top Section: Brand & Service -->
                <div style="padding: 24px 24px 20px; position: relative; z-index: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 4px; color: #d4af37; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-star-fill" style="font-size: 10px;"></i> RESERVA <i class="bi bi-star-fill" style="font-size: 10px;"></i>
                            </div>
                            <div style="font-size: 24px; font-weight: 800; line-height: 1.1; letter-spacing: -0.5px; background: linear-gradient(135deg, #ffffff, #d1d5db); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <?= htmlspecialchars($cita['servicio']) ?>
                            </div>
                        </div>
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #d4af37; flex-shrink: 0; box-shadow: inset 0 0 10px rgba(212,175,55,0.1);">
                            <i class="bi bi-scissors"></i>
                        </div>
                    </div>

                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 14px; margin-bottom: 8px;">
                        <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Profesional a cargo</div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: #d4af37; color: #000; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="bi bi-person-check-fill"></i></div>
                            <div style="font-size: 15px; font-weight: 600; text-transform: capitalize; color: #f9fafb;"><?= htmlspecialchars($cita['barbero']) ?></div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 14px;">
                            <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Fecha</div>
                            <div style="font-size: 14px; font-weight: 600; color: #f9fafb;"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></div>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 14px;">
                            <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Hora</div>
                            <div style="font-size: 16px; font-weight: 700; color: #f9fafb; display: flex; align-items: center; gap: 6px;"><i class="bi bi-clock" style="color: #d4af37; font-size: 14px;"></i> <?= substr($cita['hora'], 0, 5) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Perforation Line -->
                <div style="position: relative; height: 0; width: 100%; border-top: 2px dashed rgba(255,255,255,0.15); margin: 2px 0; z-index: 1;">
                    <div style="position: absolute; left: -14px; top: -14px; width: 28px; height: 28px; background: var(--white); border-radius: 50%; box-shadow: inset -1px 0 0 rgba(212,175,55,0.2);"></div>
                    <div style="position: absolute; right: -14px; top: -14px; width: 28px; height: 28px; background: var(--white); border-radius: 50%; box-shadow: inset 1px 0 0 rgba(212,175,55,0.2);"></div>
                </div>

                <!-- Bottom Section: Price & Status -->
                <div style="padding: 20px 24px 24px; position: relative; z-index: 1; background: rgba(212,175,55,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
                        <div>
                            <span class="badge-estado badge-<?= strtolower($cita['estado']) ?>" style="background:rgba(212,175,55,0.2); color:#d4af37; border:1px solid rgba(212,175,55,0.4); font-weight:800; padding: 6px 14px; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"><i class="bi bi-circle-fill" style="font-size: 8px; margin-right: 6px; vertical-align: middle;"></i><?= ucfirst(strtolower($cita['estado'])) ?></span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2px;"><i class="bi bi-hourglass-split"></i> <?= $cita['duracion_min'] ?? 0 ?> MIN</div>
                            <div style="font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -1px; text-shadow: 0 2px 10px rgba(212,175,55,0.3);">
                                <span style="font-size: 14px; color: #d4af37; font-weight: 700; vertical-align: super; margin-right: 2px;">$</span><?= number_format($cita['precio'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta reprogramacion pendiente -->
                    <?php if ($repr && $citaModel->dentroDeVentana($repr['fecha_solicitud'])): ?>
                        <?php $secsRepr = $citaModel->segundosRestantes($repr['fecha_solicitud']); ?>
                        <div class="reprogramacion-alert" style="margin-top:16px; padding-top:16px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size:13px;font-weight:700;color:#c4b5fd;margin-bottom:10px;">
                                <i class="bi bi-arrow-repeat"></i> Reprogramación pendiente de tu respuesta
                            </div>
                            <div style="font-size:13px;margin-bottom:10px; color:#f9fafb;">
                                Nuevo horario: <strong style="color:#d4af37;"><?= date('d/m/Y', strtotime($repr['nueva_fecha'])) ?></strong> a las <strong style="color:#d4af37;"><?= substr($repr['nueva_hora'],0,5) ?></strong>
                            </div>
                            <div class="countdown-wrapper" id="cdWrap<?= $cita['id_cita'] ?>" style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1); color:#fff; border-radius:12px; padding:12px 16px; margin-bottom:12px;">
                                <i class="bi bi-clock countdown-icon" style="color:#d4af37; font-size: 20px;"></i>
                                <div style="flex:1;">
                                    <div class="countdown-label" style="color:#9ca3af; font-size: 11px; text-transform:uppercase; letter-spacing:1px; margin-bottom: 4px;">Tiempo restante</div>
                                    <div class="countdown-timer" id="cdTimer<?= $cita['id_cita'] ?>" style="color:#f9fafb; font-size:16px; font-weight:700;">03:00</div>
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button class="btn" id="btnConf<?= $cita['id_cita'] ?>" onclick="responder(<?= $cita['id_cita'] ?>, 'CONFIRMAR')" style="flex:1; justify-content:center; background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; font-weight:700; padding:10px; border-radius:12px; box-shadow:0 4px 10px rgba(16, 185, 129, 0.3);">
                                    <i class="bi bi-check-circle"></i> Confirmar
                                </button>
                                <button class="btn" id="btnCanc<?= $cita['id_cita'] ?>" onclick="responder(<?= $cita['id_cita'] ?>, 'CANCELAR')" style="flex:1; justify-content:center; background:rgba(220,38,38,0.15); border:1px solid rgba(220,38,38,0.3); color:#fca5a5; font-weight:700; padding:10px; border-radius:12px;">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            </div>
                            <script>
                                initCd(<?= $secsRepr ?>, 'cdTimer<?= $cita['id_cita'] ?>', 'cdWrap<?= $cita['id_cita'] ?>', ['btnConf<?= $cita['id_cita'] ?>','btnCanc<?= $cita['id_cita'] ?>']);
                            </script>
                        </div>
                    <?php endif; ?>

                    <!-- Ventana cancelacion -->
                    <?php if ($puedeCanc): ?>
                        <div class="countdown-wrapper" id="cdWrapCanc<?= $cita['id_cita'] ?>" style="margin-top:16px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:12px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <i class="bi bi-clock countdown-icon" style="color:#d4af37; font-size:20px;"></i>
                                <div>
                                    <div class="countdown-label" style="color:#9ca3af; font-size:11px; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Tiempo para cancelar</div>
                                    <div class="countdown-timer" id="cdTimerCanc<?= $cita['id_cita'] ?>" style="font-size:16px; font-weight:700; color:#f9fafb;"><?= str_pad(floor($secs/60),2,'0',STR_PAD_LEFT).':'.str_pad($secs%60,2,'0',STR_PAD_LEFT) ?></div>
                                </div>
                            </div>
                            <button class="btn btn-sm" id="btnCancelCita<?= $cita['id_cita'] ?>" onclick="cancelarCita(<?= $cita['id_cita'] ?>)" style="background:rgba(220,38,38,0.15); border:1px solid rgba(220,38,38,0.3); color:#fca5a5; padding:8px 14px; border-radius:8px; font-weight:700; transition:all 0.3s;" onmouseover="this.style.background='rgba(220,38,38,0.3)'" onmouseout="this.style.background='rgba(220,38,38,0.15)'">
                                <i class="bi bi-x-circle"></i> Cancelar cita
                            </button>
                        </div>
                        <script>
                            initCd(<?= $secs ?>, 'cdTimerCanc<?= $cita['id_cita'] ?>', 'cdWrapCanc<?= $cita['id_cita'] ?>', ['btnCancelCita<?= $cita['id_cita'] ?>']);
                        </script>
                    <?php elseif ($cita['estado'] === 'PENDIENTE'): ?>
                        <div style="font-size:12px;color:#9ca3af;margin-top:16px; text-align:center; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.1);">
                            <i class="bi bi-lock" style="color:#d4af37;"></i> El tiempo para cancelar esta cita ha expirado.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CITAS ANTERIORES -->
<div class="content-card">
    <div class="content-card-header">
        <h3><i class="bi bi-check-circle" style="color:var(--success);margin-right:8px;"></i>Citas Anteriores</h3>
    </div>
    <div class="content-card-body">
        <?php if (empty($anteriores)): ?>
            <div class="empty-state"><i class="bi bi-calendar"></i><h3>Sin historial aún</h3></div>
        <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($anteriores as $c): ?>
                <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)'; this.style.borderColor='rgba(212,175,55,0.3)';" onmouseout="this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
                    
                    <div style="display: flex; align-items: center; gap: 16px; flex: 2; min-width: 250px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.2); display: flex; align-items: center; justify-content: center; color: #d4af37; font-size: 20px;">
                            <i class="bi bi-scissors"></i>
                        </div>
                        <div>
                            <div style="font-size: 16px; font-weight: 800; color: #111827; letter-spacing: -0.3px; margin-bottom: 4px;"><?= htmlspecialchars($c['servicio']) ?></div>
                            <div style="font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-person-badge" style="color: #d4af37;"></i> <span style="text-transform: capitalize;"><?= htmlspecialchars($c['barbero']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 32px; flex: 2; min-width: 200px;">
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px;">Fecha y Hora</div>
                            <div style="font-size: 14px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-calendar-check" style="color: #d4af37;"></i> <?= date('d/m/Y', strtotime($c['fecha'])) ?> <span style="color:#d1d5db;">|</span> <?= substr($c['hora'],0,5) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px;">Precio</div>
                            <div style="font-size: 15px; font-weight: 800; color: #111827;">
                                <span style="font-size: 12px; color: #d4af37; vertical-align: super; margin-right: 2px;">$</span><?= number_format($c['precio'],0,',','.') ?>
                            </div>
                        </div>
                    </div>

                    <div style="flex: 1; text-align: right; min-width: 120px;">
                        <span class="badge-estado badge-<?= strtolower($c['estado']) ?>" style="padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <?php if($c['estado'] === 'COMPLETADA'): ?>
                                <i class="bi bi-check2-circle"></i>
                            <?php elseif($c['estado'] === 'CANCELADA'): ?>
                                <i class="bi bi-x-circle"></i>
                            <?php else: ?>
                                <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                            <?php endif; ?>
                            <?= ucfirst(strtolower($c['estado'])) ?>
                        </span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CITAS CANCELADAS -->
<?php if (!empty($canceladas)): ?>
<div class="content-card">
    <div class="content-card-header">
        <h3><i class="bi bi-x-circle" style="color:var(--danger);margin-right:8px;"></i>Citas Canceladas</h3>
    </div>
    <div class="content-card-body">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($canceladas as $c): ?>
                <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)'; this.style.borderColor='#fca5a5';" onmouseout="this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
                    
                    <div style="display: flex; align-items: center; gap: 16px; flex: 2; min-width: 250px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(220,38,38,0.05); border: 1px solid rgba(220,38,38,0.2); display: flex; align-items: center; justify-content: center; color: #dc2626; font-size: 20px;">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div>
                            <div style="font-size: 16px; font-weight: 800; color: #111827; letter-spacing: -0.3px; margin-bottom: 4px; text-decoration: line-through; opacity: 0.7;"><?= htmlspecialchars($c['servicio']) ?></div>
                            <div style="font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-person-badge" style="color: #9ca3af;"></i> <span style="text-transform: capitalize;"><?= htmlspecialchars($c['barbero']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 32px; flex: 2; min-width: 200px;">
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px;">Fecha y Hora</div>
                            <div style="font-size: 14px; font-weight: 600; color: #6b7280; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-calendar-x" style="color: #9ca3af;"></i> <?= date('d/m/Y', strtotime($c['fecha'])) ?> <span style="color:#d1d5db;">|</span> <?= substr($c['hora'],0,5) ?>
                            </div>
                        </div>
                    </div>

                    <div style="flex: 1; text-align: right; min-width: 120px;">
                        <span class="badge-estado badge-cancelada" style="padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: rgba(220,38,38,0.1); color: #dc2626; border: 1px solid rgba(220,38,38,0.2);">
                            <i class="bi bi-x-circle-fill"></i> Cancelada
                        </span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once PROFUNDIDAD . 'includes/nav_footer.php'; ?>

<script>
function initCd(secs, timerId, wrapperId, btnIds) {
    var r = Math.max(0, secs);
    var timer = document.getElementById(timerId);
    var wrap  = document.getElementById(wrapperId);
    if (!timer) return;

    function upd() {
        var m = String(Math.floor(r/60)).padStart(2,'0');
        var s = String(r%60).padStart(2,'0');
        timer.textContent = m+':'+s;
    }
    upd();
    if (r <= 0) { expire(); return; }

    var iv = setInterval(function() {
        r--;
        upd();
        if (r <= 0) { clearInterval(iv); expire(); }
    }, 1000);

    function expire() {
        if (wrap) wrap.classList.add('expired');
        if (timer) timer.textContent = '00:00';
        btnIds.forEach(function(id) {
            var b = document.getElementById(id);
            if (b) { b.disabled = true; b.title='Tiempo expirado'; }
        });
    }
}

function cancelarCita(id) {
    Swal.fire({
        title: '¿Cancelar cita?',
        text: '¿Estás seguro de que deseas cancelar esta cita?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then(function(r) {
        if (r.isConfirmed) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = '../../controllers/cliente/citasController.php';
            f.innerHTML = '<input name="accion" value="cancelar"><input name="id_cita" value="'+id+'">';
            document.body.appendChild(f);
            f.submit();
        }
    });
}

function responder(id, accion) {
    var texto = accion === 'CONFIRMAR' ? '¿Confirmas el nuevo horario?' : '¿Deseas cancelar la cita?';
    Swal.fire({
        title: accion === 'CONFIRMAR' ? 'Confirmar reprogramación' : 'Cancelar cita',
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: accion === 'CONFIRMAR' ? '#16a34a' : '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: accion === 'CONFIRMAR' ? 'Confirmar' : 'Cancelar cita',
        cancelButtonText: 'Volver'
    }).then(function(r) {
        if (r.isConfirmed) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = '../../controllers/cliente/citasController.php';
            f.innerHTML = '<input name="accion" value="responder_reprogramacion"><input name="id_cita" value="'+id+'"><input name="respuesta" value="'+accion+'">';
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>
</body>
</html>

