<?php
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);

require_once PROFUNDIDAD . 'models/Cita.php';
require_once PROFUNDIDAD . 'models/Servicio.php';
require_once PROFUNDIDAD . 'models/Disponibilidad.php';
require_once PROFUNDIDAD . 'models/Usuario.php';

$id_cliente       = usuarioId();
$servicioModel    = new Servicio();
$disponibModel    = new Disponibilidad();
$usuarioModel     = new Usuario();
$servicios        = $servicioModel->obtenerActivos();
$barberos         = $usuarioModel->obtenerBarberosActivos();

$pagina_activa = 'agendar';
$titulo_pagina = 'Agendar Cita';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - MC Barbería</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <style>
        /* Premium Service Card */
        .servicio-card-pro {
            background: var(--white);
            border: 2px solid transparent;
            border-radius: 16px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }
        .servicio-card-pro:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: rgba(212,175,55,0.3);
        }
        .servicio-card-pro.selected {
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212,175,55,0.15);
            background: rgba(181,138,74,0.02);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_cliente.php'; ?>

<div class="page-header">
    <h1>Agendar Cita</h1>
    <p>Sigue los pasos para reservar tu cita en la barbería.</p>
</div>

<!-- STEPPER -->
<div class="steps-nav" id="stepsNav">
    <div class="step-item active" id="step-item-1">
        <div class="step-circle">1</div>
        <span class="step-label">Servicio</span>
    </div>
    <div class="step-connector" id="conn-1"></div>
    <div class="step-item" id="step-item-2">
        <div class="step-circle">2</div>
        <span class="step-label">Barbero</span>
    </div>
    <div class="step-connector" id="conn-2"></div>
    <div class="step-item" id="step-item-3">
        <div class="step-circle">3</div>
        <span class="step-label">Fecha</span>
    </div>
    <div class="step-connector" id="conn-3"></div>
    <div class="step-item" id="step-item-4">
        <div class="step-circle">4</div>
        <span class="step-label">Hora</span>
    </div>
    <div class="step-connector" id="conn-4"></div>
    <div class="step-item" id="step-item-5">
        <div class="step-circle">5</div>
        <span class="step-label">Confirmar</span>
    </div>
</div>

<!-- PASO 1: SERVICIO -->
<div class="content-card" id="paso1">
    <div class="content-card-header"><h3>Paso 1 - Selecciona el servicio</h3></div>
    <div class="content-card-body">
        <?php if (empty($servicios)): ?>
            <div class="empty-state"><i class="bi bi-scissors"></i><h3>Sin servicios disponibles</h3></div>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
            <?php foreach ($servicios as $s): ?>
            <div class="servicio-card-pro"
                data-id="<?= $s['id_servicio'] ?>"
                data-nombre="<?= htmlspecialchars($s['nombre']) ?>"
                data-precio="<?= $s['precio'] ?>"
                data-duracion="<?= $s['duracion_min'] ?>"
                onclick="seleccionarServicio(this)">
                
                <?php if (!empty($s['imagen'])): ?>
                    <div style="height:140px; background-image:url('../../public/uploads/servicios/<?= htmlspecialchars($s['imagen']) ?>'); background-size:cover; background-position:center; border-radius:8px; margin-bottom:12px;"></div>
                <?php else: ?>
                    <div style="height:140px; background:var(--background); border-radius:8px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; color:#ccc; font-size:40px;">
                        <i class="bi bi-image"></i>
                    </div>
                <?php endif; ?>

                <div style="font-size:16px;font-weight:700;margin-bottom:8px;"><?= htmlspecialchars($s['nombre']) ?></div>
                <div style="font-size:13px;color:var(--text-light);margin-bottom:12px;height:40px;overflow:hidden;">
                    <?= htmlspecialchars(mb_strimwidth($s['descripcion'] ?? '', 0, 70, '...')) ?>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:18px;font-weight:800;color:var(--gold);">$<?= number_format($s['precio'], 0, ',', '.') ?></span>
                    <span style="font-size:11px;color:var(--text-muted);background:var(--background);padding:4px 8px;border-radius:20px;">
                        <i class="bi bi-clock"></i> <?= $s['duracion_min'] ?> min
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- PASO 2: BARBERO -->
<div class="content-card" id="paso2" style="display:none;">
    <div class="content-card-header">
        <h3>Paso 2 - Selecciona el barbero</h3>
        <button class="btn btn-volver" onclick="irPaso(1)"><i class="bi bi-arrow-left"></i> Volver al Servicio</button>
    </div>
    <div class="content-card-body">
        <?php if (empty($barberos)): ?>
            <div class="empty-state"><i class="bi bi-person-x"></i><h3>Sin barberos disponibles</h3><p>Actualmente no hay barberos en el sistema.</p></div>
        <?php else: ?>
        <style>
            .barbero-card-pro {
                background: var(--white);
                border: 2px solid transparent;
                border-radius: 16px;
                overflow: hidden;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                position: relative;
            }
            .barbero-card-pro:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -5px rgba(0, 0, 0, 0.04);
                border-color: rgba(212,175,55,0.3);
            }
            .barbero-card-pro.selected {
                border-color: var(--gold);
                box-shadow: 0 0 0 4px rgba(212,175,55,0.15);
            }
            .barbero-card-pro .bc-header {
                height: 70px;
                background: linear-gradient(135deg, #1f2937, #111827);
                position: relative;
            }
            .barbero-card-pro .bc-avatar-wrapper {
                width: 74px;
                height: 74px;
                border-radius: 50%;
                background: var(--white);
                padding: 4px;
                position: absolute;
                bottom: -37px;
                left: 50%;
                transform: translateX(-50%);
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            .barbero-card-pro.selected .bc-avatar-wrapper {
                background: var(--gold);
            }
            .barbero-card-pro .bc-avatar {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 26px;
                font-weight: 800;
                color: #d4af37;
                background-size: cover;
                background-position: center;
                border: 2px solid #ffffff;
            }
            .barbero-card-pro .bc-body {
                padding: 50px 20px 20px;
                text-align: center;
            }
            .barbero-card-pro .bc-name {
                font-size: 17px;
                font-weight: 800;
                color: var(--text-color);
                margin-bottom: 6px;
                letter-spacing: -0.3px;
                text-transform: capitalize;
            }
            .barbero-card-pro .bc-spec {
                font-size: 11px;
                color: #b58a4a;
                background: rgba(212,175,55,0.1);
                padding: 4px 12px;
                border-radius: 20px;
                display: inline-block;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .barbero-card-pro .bc-footer {
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px dashed var(--border);
                font-size: 13px;
                color: var(--text-muted);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                font-weight: 500;
            }
            .barbero-card-pro.selected .bc-footer {
                color: var(--gold);
                font-weight: 600;
            }
            .barbero-card-pro .check-icon {
                position: absolute;
                top: 10px;
                right: 10px;
                width: 24px;
                height: 24px;
                background: var(--gold);
                color: #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                opacity: 0;
                transform: scale(0.8);
                transition: all 0.3s ease;
                z-index: 10;
            }
            .barbero-card-pro.selected .check-icon {
                opacity: 1;
                transform: scale(1);
            }
        </style>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;">
            <?php foreach ($barberos as $b): 
                $nombre_corto = mb_strimwidth($b['nombre'], 0, 22, '...');
                $especialidad = !empty($b['especialidad']) ? mb_strimwidth($b['especialidad'], 0, 20, '...') : 'Barbero Profesional';
            ?>
            <div class="barbero-card-pro"
                 onclick="seleccionarBarberoPro(this, <?= $b['id_usuario'] ?>, '<?= htmlspecialchars($b['nombre']) ?>')">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <div class="bc-header">
                    <div class="bc-avatar-wrapper">
                        <?php if (!empty($b['foto_perfil'])): ?>
                            <div class="bc-avatar" style="background-image:url('../../public/uploads/perfiles/<?= htmlspecialchars($b['foto_perfil']) ?>');"></div>
                        <?php else: ?>
                            <div class="bc-avatar">
                                <?= mb_strtoupper(mb_substr($b['nombre'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bc-body">
                    <div class="bc-name"><?= htmlspecialchars($nombre_corto) ?></div>
                    <div class="bc-spec"><?= htmlspecialchars($especialidad) ?></div>
                    <div class="bc-footer">
                        <i class="bi bi-calendar-check"></i> Ver horarios
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- PASO 3: FECHA -->
<div class="content-card" id="paso3" style="display:none;">
    <div class="content-card-header">
        <h3>Paso 3 - Selecciona la fecha</h3>
        <button class="btn btn-volver" onclick="irPaso(2)"><i class="bi bi-arrow-left"></i> Volver a Barberos</button>
    </div>
    <div class="content-card-body">
        <div class="form-group" style="max-width: 320px;">
            <label class="form-label" style="font-weight: 700; color: #111827; margin-bottom: 12px; display: block; font-size: 15px;">¿Qué día te gustaría venir?</label>
            
            <div style="position: relative; display: flex; align-items: center;">
                <div style="position: absolute; left: 16px; color: var(--gold); font-size: 20px; pointer-events: none; z-index: 2;">
                    <i class="bi bi-calendar2-week"></i>
                </div>
                <input type="date" class="form-control premium-date-input" id="fechaCita"
                    min="<?= date('Y-m-d') ?>"
                    max="<?= date('Y-m-d', strtotime('+60 days')) ?>"
                    onchange="seleccionarFecha(this.value)">
            </div>
            <style>
                .premium-date-input {
                    padding: 16px 16px 16px 48px !important;
                    font-size: 16px !important;
                    font-weight: 600 !important;
                    color: #111827 !important;
                    background: #f9fafb !important;
                    border: 2px solid #e5e7eb !important;
                    border-radius: 14px !important;
                    width: 100% !important;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02) !important;
                    cursor: pointer;
                    position: relative;
                }
                .premium-date-input:hover {
                    border-color: #d1d5db !important;
                    background: #ffffff !important;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important;
                }
                .premium-date-input:focus {
                    border-color: var(--gold) !important;
                    background: #ffffff !important;
                    box-shadow: 0 0 0 4px rgba(212,175,55,0.15), 0 4px 6px -1px rgba(0,0,0,0.05) !important;
                    outline: none !important;
                }
                /* Expand clickable area of native calendar popup */
                .premium-date-input::-webkit-calendar-picker-indicator {
                    background: transparent;
                    bottom: 0;
                    color: transparent;
                    cursor: pointer;
                    height: auto;
                    left: 0;
                    position: absolute;
                    right: 0;
                    top: 0;
                    width: auto;
                }
            </style>
        </div>
    </div>
</div>

<!-- PASO 4: HORA -->
<div class="content-card" id="paso4" style="display:none;">
    <div class="content-card-header">
        <h3>Paso 4 - Selecciona la hora</h3>
        <button class="btn btn-volver" onclick="irPaso(3)"><i class="bi bi-arrow-left"></i> Volver</button>
    </div>
    <div class="content-card-body">
        <div id="loadingSlots" style="display:none;text-align:center;padding:24px;color:var(--text-muted);">
            <i class="bi bi-hourglass-split" style="font-size:28px;"></i><br>Cargando horarios...
        </div>
        <div class="slots-grid" id="slotsGrid"></div>
        <div id="sinSlots" style="display:none;">
            <div class="empty-state"><i class="bi bi-clock-history"></i><h3>Sin horarios disponibles</h3><p>Elige otra fecha o barbero.</p></div>
        </div>
    </div>
</div>

<!-- PASO 5: CONFIRMAR -->
<div class="content-card" id="paso5" style="display:none;">
    <div class="content-card-header">
        <h3>Paso 5 - Confirma tu cita</h3>
        <button class="btn btn-volver" onclick="irPaso(4)"><i class="bi bi-arrow-left"></i> Volver</button>
    </div>
    <div class="content-card-body">
        <div class="cita-resumen" id="resumenCita"></div>
        <div style="margin-top: 32px; text-align: center;">
            <button class="btn btn-primary btn-lg premium-confirm-btn" onclick="confirmarCita()" style="width:100%; max-width: 460px; padding: 16px; border-radius: 16px; font-size: 16px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); margin: 0 auto; box-shadow: 0 10px 20px -5px rgba(212,175,55,0.4);">
                <i class="bi bi-check2-circle" style="font-size: 22px;"></i> Confirmar Reserva
            </button>
            <style>
                .premium-confirm-btn:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 15px 25px -5px rgba(212,175,55,0.6) !important;
                }
                .premium-confirm-btn:active {
                    transform: translateY(1px);
                    box-shadow: 0 5px 10px -5px rgba(212,175,55,0.4) !important;
                }
            </style>
        </div>
    </div>
</div>

<?php require_once PROFUNDIDAD . 'includes/nav_footer.php'; ?>

<script>
var selServicioId   = null;
var selServicioNom  = '';
var selPrecio       = 0;
var selDuracion     = 0;
var selBarberoId    = null;
var selBarberoNom   = '';
var selFecha        = '';
var selHora         = '';

function irPaso(n) {
    [1,2,3,4,5].forEach(function(i) {
        document.getElementById('paso'+i).style.display = i === n ? '' : 'none';
        var item = document.getElementById('step-item-'+i);
        item.classList.remove('active','done');
        if (i === n) item.classList.add('active');
        else if (i < n) item.classList.add('done');
        if (i < 5) {
            var conn = document.getElementById('conn-'+i);
            conn.classList.toggle('done', i < n);
        }
    });
    if (n === 4 && selBarberoId && selFecha) cargarSlots();
    if (n === 5) mostrarResumen();
}

function seleccionarServicio(el) {
    document.querySelectorAll('.servicio-card-pro').forEach(function(c) {
        c.classList.remove('selected');
    });
    el.classList.add('selected');

    selServicioId  = el.dataset.id;
    selServicioNom = el.dataset.nombre;
    selPrecio      = parseFloat(el.dataset.precio);
    selDuracion    = parseInt(el.dataset.duracion);

    setTimeout(function() { 
        irPaso(2); 
    }, 300);
}

function seleccionarBarberoPro(el, id, nombre) {
    document.querySelectorAll('.barbero-card-pro').forEach(function(c) {
        c.classList.remove('selected');
    });
    el.classList.add('selected');

    selBarberoId  = id;
    selBarberoNom = nombre;

    setTimeout(function() { 
        irPaso(3); 
    }, 400);
}

function seleccionarFecha(fecha) {
    if (!fecha) return;
    selFecha = fecha;
    setTimeout(function() { irPaso(4); }, 300);
}

function cargarSlots() {
    var grid    = document.getElementById('slotsGrid');
    var loading = document.getElementById('loadingSlots');
    var sinSlots = document.getElementById('sinSlots');
    grid.innerHTML = '';
    loading.style.display = 'block';
    sinSlots.style.display = 'none';

    fetch('../../controllers/cliente/citasController.php?accion=slots&barbero=' + selBarberoId + '&fecha=' + selFecha)
        .then(function(r) { return r.json(); })
        .then(function(slots) {
            loading.style.display = 'none';
            if (!slots.length) { sinSlots.style.display = 'block'; return; }
            slots.forEach(function(s) {
                var btn = document.createElement('button');
                btn.className = 'slot-btn';
                btn.textContent = s.hora_inicio.substring(0,5);
                btn.onclick = function() {
                    document.querySelectorAll('.slot-btn').forEach(function(x) { x.classList.remove('selected'); });
                    btn.classList.add('selected');
                    selHora = s.hora_inicio;
                    setTimeout(function() { irPaso(5); }, 250);
                };
                grid.appendChild(btn);
            });
        });
}

function mostrarResumen() {
    var fechaObj = selFecha ? new Date(selFecha + 'T00:00:00') : new Date();
    var fechaStr = fechaObj.toLocaleDateString('es-CO', {weekday:'long',day:'numeric',month:'long',year:'numeric'});
    fechaStr = fechaStr.charAt(0).toUpperCase() + fechaStr.slice(1);

    document.getElementById('resumenCita').innerHTML = `
        <div class="premium-ticket" style="background: linear-gradient(145deg, #111827, #000000); border-radius: 20px; color: #fff; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid rgba(212,175,55,0.2); max-width: 460px; margin: 0 auto; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Gold glow effect -->
            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(212,175,55,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
            
            <!-- Top Section: Brand & Service -->
            <div style="padding: 32px 32px 24px; position: relative; z-index: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
                    <div>
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 4px; color: #d4af37; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-star-fill" style="font-size: 10px;"></i> TICKET DE RESERVA <i class="bi bi-star-fill" style="font-size: 10px;"></i>
                        </div>
                        <div style="font-size: 28px; font-weight: 800; line-height: 1.1; letter-spacing: -0.5px; background: linear-gradient(135deg, #ffffff, #d1d5db); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            ${selServicioNom}
                        </div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.3); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #d4af37; flex-shrink: 0; box-shadow: inset 0 0 10px rgba(212,175,55,0.1);">
                        <i class="bi bi-scissors"></i>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 16px; margin-bottom: 8px;">
                    <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Profesional a cargo</div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #d4af37; color: #000; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="bi bi-person-check-fill"></i></div>
                        <div style="font-size: 15px; font-weight: 600; text-transform: capitalize; color: #f9fafb;">${selBarberoNom}</div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 16px;">
                        <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Fecha</div>
                        <div style="font-size: 14px; font-weight: 600; color: #f9fafb;">${fechaStr}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 16px;">
                        <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px;">Hora</div>
                        <div style="font-size: 18px; font-weight: 700; color: #f9fafb; display: flex; align-items: center; gap: 6px;"><i class="bi bi-clock" style="color: #d4af37; font-size: 14px;"></i> ${selHora.substring(0,5)}</div>
                    </div>
                </div>
            </div>

            <!-- Perforation Line -->
            <div style="position: relative; height: 0; width: 100%; border-top: 2px dashed rgba(255,255,255,0.15); margin: 4px 0; z-index: 1;">
                <!-- Left Notch -->
                <div style="position: absolute; left: -14px; top: -14px; width: 28px; height: 28px; background: #ffffff; border-radius: 50%; box-shadow: inset -1px 0 0 rgba(212,175,55,0.2);"></div>
                <!-- Right Notch -->
                <div style="position: absolute; right: -14px; top: -14px; width: 28px; height: 28px; background: #ffffff; border-radius: 50%; box-shadow: inset 1px 0 0 rgba(212,175,55,0.2);"></div>
            </div>

            <!-- Bottom Section: Price & Barcode -->
            <div style="padding: 24px 32px 32px; position: relative; z-index: 1; background: rgba(212,175,55,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
                    <div>
                        <div style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;">Total a pagar</div>
                        <div style="font-size: 13px; color: #000; font-weight: 800; background: linear-gradient(135deg, #d4af37, #fdf0a6); padding: 4px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(212,175,55,0.2);">
                            <i class="bi bi-hourglass-split"></i> ${selDuracion} MIN
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 14px; color: #d4af37; font-weight: 700; margin-bottom: -4px;">COP</div>
                        <div style="font-size: 38px; font-weight: 800; color: #ffffff; letter-spacing: -1px; text-shadow: 0 2px 10px rgba(212,175,55,0.3);">
                            $${Number(selPrecio).toLocaleString('es-CO')}
                        </div>
                    </div>
                </div>

                <!-- Fake Barcode -->
                <div style="display: flex; align-items: stretch; justify-content: space-between; height: 32px; opacity: 0.5;">
                    <div style="width: 3px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 1px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 5px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 2px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 6px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 1px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 3px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 2px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 6px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 1px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 4px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 2px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 3px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 7px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 1px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 3px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 5px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 2px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 1px; background: #fff; border-radius:1px;"></div>
                    <div style="width: 4px; background: #fff; border-radius:1px;"></div>
                </div>
                <div style="text-align: center; font-family: monospace; font-size: 11px; color: #9ca3af; letter-spacing: 5px; margin-top: 8px;">
                    MC-BARBER-${selFecha.replace(/-/g, '')}
                </div>
            </div>
        </div>
    `;
}

function confirmarCita() {
    if (!selServicioId || !selBarberoId || !selFecha || !selHora) {
        Swal.fire({ icon:'warning', title:'Datos incompletos', text:'Completa todos los pasos antes de confirmar.', confirmButtonColor:'#b58a4a' });
        return;
    }

    var fechaObj = new Date(selFecha + 'T00:00:00');
    var fechaCorta = fechaObj.toLocaleDateString('es-CO', {day:'numeric',month:'long'});

    Swal.fire({
        title: '',
        html: `
            <style>
                .premium-swal-confirm {
                    box-shadow: 0 4px 12px rgba(181, 138, 74, 0.3) !important;
                    border-radius: 12px !important;
                    font-weight: 700 !important;
                    padding: 12px 24px !important;
                    transition: all 0.3s ease !important;
                }
                .premium-swal-confirm:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 16px rgba(181, 138, 74, 0.4) !important;
                }
                .premium-swal-cancel {
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    font-weight: 700 !important;
                    padding: 12px 24px !important;
                    transition: all 0.3s ease !important;
                }
                .premium-swal-cancel:hover {
                    background: #e5e7eb !important;
                }
                .premium-swal-popup {
                    border-radius: 24px !important;
                }
            </style>
            <div style="text-align: center; padding: 10px 0 0;">
                <div style="width: 90px; height: 90px; background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, rgba(212,175,55,0.05) 70%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: inset 0 0 0 1px rgba(212,175,55,0.2);">
                    <i class="bi bi-calendar-check" style="font-size: 42px; color: #b58a4a; filter: drop-shadow(0 2px 4px rgba(212,175,55,0.3));"></i>
                </div>
                <h2 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 8px; letter-spacing: -0.5px;">¡Casi listo!</h2>
                <p style="font-size: 15px; color: #6b7280; margin-bottom: 32px; max-width: 320px; margin-left: auto; margin-right: auto;">Estás a un solo paso de confirmar tu reserva en MC Barber.</p>
                
                <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; text-align: left; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed #e5e7eb;">
                        <span style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 14px; font-weight: 600;"><i class="bi bi-scissors" style="color: #b58a4a; font-size: 16px;"></i> Servicio</span>
                        <span style="font-weight: 700; color: #111827; font-size: 15px;">${selServicioNom}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed #e5e7eb;">
                        <span style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 14px; font-weight: 600;"><i class="bi bi-person-badge" style="color: #b58a4a; font-size: 16px;"></i> Barbero</span>
                        <span style="font-weight: 700; color: #111827; font-size: 15px; text-transform: capitalize;">${selBarberoNom}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 14px; font-weight: 600;"><i class="bi bi-clock-history" style="color: #b58a4a; font-size: 16px;"></i> Cuándo</span>
                        <span style="font-weight: 800; color: #b58a4a; font-size: 14px; background: rgba(212,175,55,0.1); padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(212,175,55,0.2);">${fechaCorta} - ${selHora.substring(0,5)}</span>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#b58a4a',
        cancelButtonColor: '#f3f4f6',
        confirmButtonText: '<i class="bi bi-check2-circle" style="margin-right:8px;"></i>Confirmar cita',
        cancelButtonText: '<span style="color:#4b5563; font-weight:700;">Revisar detalles</span>',
        width: '480px',
        padding: '32px 24px',
        customClass: {
            popup: 'premium-swal-popup',
            confirmButton: 'premium-swal-confirm',
            cancelButton: 'premium-swal-cancel'
        }
    }).then(function(r) {
        if (r.isConfirmed) {
            Swal.fire({
                title: 'Procesando...',
                text: 'Guardando tu reserva',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../controllers/cliente/citasController.php';
            form.innerHTML =
                '<input type="hidden" name="accion" value="crear">' +
                '<input type="hidden" name="id_servicio" value="' + selServicioId + '">' +
                '<input type="hidden" name="id_barbero" value="' + selBarberoId + '">' +
                '<input type="hidden" name="fecha" value="' + selFecha + '">' +
                '<input type="hidden" name="hora" value="' + selHora + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
</body>
</html>

