<?php
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);

require_once PROFUNDIDAD . 'models/Cita.php';
require_once PROFUNDIDAD . 'models/Servicio.php';

$id_cliente   = usuarioId();
$citaModel    = new Cita();
$proximaCita  = $citaModel->proximaCitaCliente($id_cliente);
$todasCitas   = $citaModel->obtenerPorCliente($id_cliente);

$hoy        = date('Y-m-d');
$citasHoy   = array_filter($todasCitas, fn($c) => $c['fecha'] === $hoy && in_array($c['estado'], ['PENDIENTE','ACEPTADA']));
$pendientes = array_filter($todasCitas, fn($c) => $c['estado'] === 'PENDIENTE');

// Verificar si hay reprogramacion pendiente de respuesta
$reprogramacionPendiente = null;
foreach ($todasCitas as $cita) {
    if ($cita['estado'] === 'REPROGRAMADA') {
        $repr = $citaModel->obtenerReprogramacionPendiente((int)$cita['id_cita']);
        if ($repr && $citaModel->dentroDeVentana($repr['fecha_solicitud'])) {
            $reprogramacionPendiente = array_merge($cita, ['reprogramacion' => $repr]);
            break;
        }
    }
}

$pagina_activa = 'inicio';
$titulo_pagina = 'Mi Dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($globalConfig['nombre_negocio'] ?? 'MC BARBER') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_cliente.php'; ?>

<!-- ALERTA REPROGRAMACION PENDIENTE -->
<?php if ($reprogramacionPendiente): ?>
    <?php
    $repr   = $reprogramacionPendiente['reprogramacion'];
    $segsR  = $citaModel->segundosRestantes($repr['fecha_solicitud']);
    ?>
    <div class="reprogramacion-alert" id="alertReprogram">
        <div class="reprogramacion-alert-title">
            <i class="bi bi-arrow-repeat"></i>
            Tu cita ha sido reprogramada â€” Debes confirmar o cancelar
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:14px; font-size:13px; color:#555;">
            <span><strong>Servicio:</strong> <?= htmlspecialchars($reprogramacionPendiente['servicio']) ?></span>
            <span><strong>Barbero:</strong> <?= htmlspecialchars($reprogramacionPendiente['barbero']) ?></span>
        </div>
        <div class="reprogramacion-dates">
            <div class="reprogramacion-date-box">
                <div class="reprogramacion-date-label">Fecha anterior</div>
                <div class="reprogramacion-date-value"><?= date('d/m/Y', strtotime($reprogramacionPendiente['fecha'])) ?> Â· <?= substr($reprogramacionPendiente['hora'], 0, 5) ?></div>
            </div>
            <div class="reprogramacion-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="reprogramacion-date-box" style="border-color:var(--gold); background:rgba(181,138,74,0.05)">
                <div class="reprogramacion-date-label">Nueva fecha</div>
                <div class="reprogramacion-date-value"><?= date('d/m/Y', strtotime($repr['nueva_fecha'])) ?> Â· <?= substr($repr['nueva_hora'], 0, 5) ?></div>
            </div>
        </div>

        <div class="countdown-wrapper" id="cdWrapperHome">
            <i class="bi bi-clock countdown-icon"></i>
            <div>
                <div class="countdown-label">Tiempo restante para responder</div>
                <div class="countdown-timer" id="cdTimerHome">03:00</div>
            </div>
        </div>

        <div class="reprogramacion-actions" style="margin-top:14px;">
            <button class="btn btn-success" id="btnConfirmarHome" onclick="responderReprogramacion(<?= $reprogramacionPendiente['id_cita'] ?>, 'CONFIRMAR')">
                <i class="bi bi-check-circle"></i> Confirmar nuevo horario
            </button>
            <button class="btn btn-danger" id="btnCancelarHome" onclick="responderReprogramacion(<?= $reprogramacionPendiente['id_cita'] ?>, 'CANCELAR')">
                <i class="bi bi-x-circle"></i> Cancelar cita
            </button>
        </div>
    </div>
    <script>
        iniciarContador(<?= $segsR ?>, 'cdTimerHome', 'cdWrapperHome', ['btnConfirmarHome','btnCancelarHome']);
    </script>
<?php endif; ?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1>Bienvenido, <?= htmlspecialchars(explode(' ', usuarioNombre())[0]) ?></h1>
    <p>Aquí tienes un resumen de tu actividad en la barbería.</p>
</div>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= count($todasCitas) ?></div>
            <div class="stat-label">Citas totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= count($pendientes) ?></div>
            <div class="stat-label">Citas pendientes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-scissors"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= count(array_filter($todasCitas, fn($c) => $c['estado'] === 'COMPLETADA')) ?></div>
            <div class="stat-label">Cortes realizados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-calendar-day"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= count($citasHoy) ?></div>
            <div class="stat-label">Citas hoy</div>
        </div>
    </div>
</div>

<!-- PROXIMA CITA -->
<div class="grid-2">
    <div class="content-card">
        <div class="content-card-header">
            <h3><i class="bi bi-calendar-event" style="color:var(--gold);margin-right:8px;"></i>Próxima Cita</h3>
            <a href="agendar.php" class="btn btn-primary" style="padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(181,138,74,0.25); transition: transform 0.2s; hover: transform: translateY(-2px);"><i class="bi bi-calendar-plus-fill"></i> Agendar</a>
        </div>
        <div class="content-card-body">
            <?php if ($proximaCita): ?>
                <div class="premium-ticket" style="background: linear-gradient(145deg, #111827, #000000); border-radius: 20px; color: #fff; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid rgba(212,175,55,0.2); width: 100%; overflow: hidden; display: flex; flex-direction: column;">
                    <!-- Gold glow effect -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(212,175,55,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
                    
                    <!-- Top Section: Brand & Service -->
                    <div style="padding: 24px 24px 20px; position: relative; z-index: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 4px; color: #d4af37; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-star-fill" style="font-size: 10px;"></i> PRÓXIMA CITA <i class="bi bi-star-fill" style="font-size: 10px;"></i>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; line-height: 1.1; letter-spacing: -0.5px; background: linear-gradient(135deg, #ffffff, #d1d5db); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                    <?= htmlspecialchars($proximaCita['servicio']) ?>
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
                                <div style="font-size: 15px; font-weight: 600; text-transform: capitalize; color: #f9fafb;"><?= htmlspecialchars($proximaCita['barbero']) ?></div>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 14px;">
                                <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Fecha</div>
                                <div style="font-size: 14px; font-weight: 600; color: #f9fafb;"><?= date('d/m/Y', strtotime($proximaCita['fecha'])) ?></div>
                            </div>
                            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 14px;">
                                <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Hora</div>
                                <div style="font-size: 16px; font-weight: 700; color: #f9fafb; display: flex; align-items: center; gap: 6px;"><i class="bi bi-clock" style="color: #d4af37; font-size: 14px;"></i> <?= substr($proximaCita['hora'], 0, 5) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Perforation Line -->
                    <div style="position: relative; height: 0; width: 100%; border-top: 2px dashed rgba(255,255,255,0.15); margin: 2px 0; z-index: 1;">
                        <!-- Left and right notches with var(--white) to match content-card-body background -->
                        <div style="position: absolute; left: -14px; top: -14px; width: 28px; height: 28px; background: var(--white); border-radius: 50%; box-shadow: inset -1px 0 0 rgba(212,175,55,0.2);"></div>
                        <div style="position: absolute; right: -14px; top: -14px; width: 28px; height: 28px; background: var(--white); border-radius: 50%; box-shadow: inset 1px 0 0 rgba(212,175,55,0.2);"></div>
                    </div>

                    <!-- Bottom Section: Price & Status -->
                    <div style="padding: 20px 24px 24px; position: relative; z-index: 1; background: rgba(212,175,55,0.02);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <span class="badge-estado badge-<?= strtolower($proximaCita['estado']) ?>" style="background:rgba(212,175,55,0.2); color:#d4af37; border:1px solid rgba(212,175,55,0.4); font-weight:800; padding: 6px 14px; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"><i class="bi bi-circle-fill" style="font-size: 8px; margin-right: 6px; vertical-align: middle;"></i><?= ucfirst(strtolower($proximaCita['estado'])) ?></span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2px;"><i class="bi bi-hourglass-split"></i> <?= $proximaCita['duracion_min'] ?? 0 ?> MIN</div>
                                <div style="font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -1px; text-shadow: 0 2px 10px rgba(212,175,55,0.3);">
                                    <span style="font-size: 14px; color: #d4af37; font-weight: 700; vertical-align: super; margin-right: 2px;">$</span><?= number_format($proximaCita['precio'] ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="premium-empty" style="padding: 40px 20px; text-align: center; background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.8) 100%); border-radius: 20px; border: 1px dashed rgba(181, 138, 74, 0.3);">
                    <div class="premium-empty-icon" style="width: 72px; height: 72px; background: radial-gradient(circle, rgba(181,138,74,0.1) 0%, transparent 70%); display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--gold); font-size: 32px; margin: 0 auto 20px; box-shadow: 0 0 0 1px rgba(181, 138, 74, 0.1);">
                        <i class="bi bi-calendar2-x"></i>
                    </div>
                    <h3 style="font-size: 20px; font-weight: 800; color: #111827; margin-bottom: 8px; letter-spacing: -0.02em;">Sin citas próximas</h3>
                    <p style="font-size: 14px; color: #6b7280; max-width: 300px; margin: 0 auto 24px;">¿Listo para tu próximo estilo? Agenda tu corte ahora mismo.</p>
                    <a href="agendar.php" class="btn btn-primary" style="padding: 10px 24px; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(181,138,74,0.25);"><i class="bi bi-calendar2-plus"></i> Agendar ahora</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ACCESOS RAPIDOS -->
    <div class="content-card">
        <div class="content-card-header">
            <h3><i class="bi bi-grid" style="color:var(--gold);margin-right:8px;"></i>Accesos Rápidos</h3>
        </div>
        <div class="content-card-body" style="padding: 24px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <a href="agendar.php" style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:24px;background:linear-gradient(145deg, #ffffff, #fcfcfc);border-radius:16px;text-decoration:none;border:1px solid rgba(229, 231, 235, 0.5);box-shadow:0 4px 12px rgba(0,0,0,0.02);transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);position:relative;overflow:hidden;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px -4px rgba(181,138,74,0.15)';this.style.borderColor='rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.02)';this.style.borderColor='rgba(229, 231, 235, 0.5)'">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg, rgba(181,138,74,0.12), rgba(181,138,74,0.04));display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:24px;box-shadow:inset 0 0 0 1px rgba(181,138,74,0.25);transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'"><i class="bi bi-calendar2-plus"></i></div>
                    <span style="font-size:14px;font-weight:700;color:#111827;letter-spacing:0.02em;">Agendar Cita</span>
                </a>
                <a href="mis_citas.php" style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:24px;background:linear-gradient(145deg, #ffffff, #fcfcfc);border-radius:16px;text-decoration:none;border:1px solid rgba(229, 231, 235, 0.5);box-shadow:0 4px 12px rgba(0,0,0,0.02);transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);position:relative;overflow:hidden;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px -4px rgba(181,138,74,0.15)';this.style.borderColor='rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.02)';this.style.borderColor='rgba(229, 231, 235, 0.5)'">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg, rgba(181,138,74,0.12), rgba(181,138,74,0.04));display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:24px;box-shadow:inset 0 0 0 1px rgba(181,138,74,0.25);transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'"><i class="bi bi-journal-bookmark"></i></div>
                    <span style="font-size:14px;font-weight:700;color:#111827;letter-spacing:0.02em;">Mis Citas</span>
                </a>
                <a href="servicios.php" style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:24px;background:linear-gradient(145deg, #ffffff, #fcfcfc);border-radius:16px;text-decoration:none;border:1px solid rgba(229, 231, 235, 0.5);box-shadow:0 4px 12px rgba(0,0,0,0.02);transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);position:relative;overflow:hidden;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px -4px rgba(181,138,74,0.15)';this.style.borderColor='rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.02)';this.style.borderColor='rgba(229, 231, 235, 0.5)'">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg, rgba(181,138,74,0.12), rgba(181,138,74,0.04));display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:24px;box-shadow:inset 0 0 0 1px rgba(181,138,74,0.25);transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'"><i class="bi bi-gem"></i></div>
                    <span style="font-size:14px;font-weight:700;color:#111827;letter-spacing:0.02em;">Servicios</span>
                </a>
                <a href="turno.php" style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:24px;background:linear-gradient(145deg, #ffffff, #fcfcfc);border-radius:16px;text-decoration:none;border:1px solid rgba(229, 231, 235, 0.5);box-shadow:0 4px 12px rgba(0,0,0,0.02);transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);position:relative;overflow:hidden;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px -4px rgba(181,138,74,0.15)';this.style.borderColor='rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.02)';this.style.borderColor='rgba(229, 231, 235, 0.5)'">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg, rgba(181,138,74,0.12), rgba(181,138,74,0.04));display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:24px;box-shadow:inset 0 0 0 1px rgba(181,138,74,0.25);transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'"><i class="bi bi-person-bounding-box"></i></div>
                    <span style="font-size:14px;font-weight:700;color:#111827;letter-spacing:0.02em;">Mi Turno</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once PROFUNDIDAD . 'includes/nav_footer.php'; ?>

<script>
/**
 * iniciarContador â€” Regla de 3 minutos
 * @param {number}   segundos  Segundos restantes al cargar
 * @param {string}   timerId   ID del elemento que muestra el contador
 * @param {string}   wrapperId ID del wrapper del contador
 * @param {string[]} btnIds    IDs de los botones a deshabilitar al expirar
 */
function iniciarContador(segundos, timerId, wrapperId, btnIds) {
    var restantes = Math.max(0, segundos);
    var timer = document.getElementById(timerId);
    var wrapper = document.getElementById(wrapperId);
    if (!timer) return;

    function actualizar() {
        var min = String(Math.floor(restantes / 60)).padStart(2, '0');
        var seg = String(restantes % 60).padStart(2, '0');
        timer.textContent = min + ':' + seg;
    }

    actualizar();

    if (restantes <= 0) {
        expirar();
        return;
    }

    var intervalo = setInterval(function() {
        restantes--;
        actualizar();
        if (restantes <= 0) {
            clearInterval(intervalo);
            expirar();
        }
    }, 1000);

    function expirar() {
        if (wrapper) wrapper.classList.add('expired');
        if (timer) timer.textContent = '00:00';
        btnIds.forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) { btn.disabled = true; btn.title = 'Tiempo expirado'; }
        });
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Tiempo expirado',
                text: 'El tiempo para responder ha expirado.',
                confirmButtonColor: '#b58a4a'
            }).then(function() { location.reload(); });
        }
    }
}

function responderReprogramacion(id_cita, accion) {
    var texto = accion === 'CONFIRMAR'
        ? '¿Confirmas el nuevo horario de tu cita?'
        : '¿Deseas cancelar la cita reprogramada?';

    Swal.fire({
        title: accion === 'CONFIRMAR' ? 'Confirmar reprogramación' : 'Cancelar cita',
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: accion === 'CONFIRMAR' ? '#16a34a' : '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: accion === 'CONFIRMAR' ? 'Sí, confirmar' : 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../controllers/cliente/citasController.php';
            form.innerHTML = '<input name="accion" value="responder_reprogramacion">' +
                            '<input name="id_cita" value="' + id_cita + '">' +
                            '<input name="respuesta" value="' + accion + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
</body>
</html>

