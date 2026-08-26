<?php
/**
 * views/barbero/agenda.php
 * Agenda diaria del barbero - timeline de citas por fecha.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'models/Cita.php';

$id_barbero = (int)$_SESSION['usuario_id'];
$citaModel  = new Cita();
$fecha      = $_GET['fecha'] ?? date('Y-m-d');
$citas      = $citaModel->obtenerPorBarbero($id_barbero, $fecha);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'views/layouts/sidebar_barbero.php'; ?>

<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <h1 class="topbar-title">Agenda del Día</h1>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Hola, <strong><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]) ?></strong></span>
        </div>
    </header>

    <div class="page-content">
        <div class="page-header">
            <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
                Mi Agenda <i class="bi bi-calendar-day" style="color:var(--gold);font-size:24px;"></i>
            </h1>
            <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Consulta y gestiona tus citas del día.</p>
        </div>

        <!-- Selector de fecha -->
        <div class="content-card" style="margin-bottom:20px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div class="content-card-body" style="padding: 20px;">
                <form method="GET" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                    <!-- Controles Izquierda -->
                    <div style="display:flex; align-items:center; gap:6px; background:#f8f9fa; padding:6px; border-radius:16px; border:1px solid #f3f4f6;">
                        <a href="?fecha=<?= date('Y-m-d', strtotime($fecha . ' -1 day')) ?>" style="width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #6b7280; background: transparent; transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827';" onmouseout="this.style.background='transparent'; this.style.color='#6b7280';" title="Día anterior">
                            <i class="bi bi-chevron-left"></i>
                        </a>

                        <div style="position: relative; display: flex; align-items: center;">
                            <i class="bi bi-calendar-event" style="position: absolute; left: 14px; color: var(--gold); font-size: 15px; pointer-events: none;"></i>
                            <input type="date" name="fecha" 
                                style="border: 1px solid #e5e7eb; background: #ffffff; padding: 10px 14px 10px 40px; border-radius: 12px; font-size: 14px; font-weight: 700; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer; outline: none; width: 160px; transition: border-color 0.2s;"
                                value="<?= htmlspecialchars($fecha) ?>"
                                min="<?= date('Y-m-d', strtotime('-30 days')) ?>"
                                max="<?= date('Y-m-d', strtotime('+60 days')) ?>"
                                onchange="this.form.submit()"
                                onfocus="this.style.borderColor='var(--gold)'"
                                onblur="this.style.borderColor='#e5e7eb'">
                        </div>

                        <a href="?fecha=<?= date('Y-m-d', strtotime($fecha . ' +1 day')) ?>" style="width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #6b7280; background: transparent; transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827';" onmouseout="this.style.background='transparent'; this.style.color='#6b7280';" title="Día siguiente">
                            <i class="bi bi-chevron-right"></i>
                        </a>

                        <div style="width: 1px; height: 24px; background: #e5e7eb; margin: 0 4px;"></div>

                        <a href="?fecha=<?= date('Y-m-d') ?>" style="padding: 10px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 700; background: #111827; color: #fff; text-decoration: none; transition: all 0.2s; letter-spacing: 0.3px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 14px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.1)';">
                            Hoy
                        </a>
                    </div>

                    <!-- Texto Derecha -->
                    <?php
                        $dias_es = ['Sunday'=>'Domingo', 'Monday'=>'Lunes', 'Tuesday'=>'Martes', 'Wednesday'=>'Miércoles', 'Thursday'=>'Jueves', 'Friday'=>'Viernes', 'Saturday'=>'Sábado'];
                        $meses_es = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
                        
                        $nombre_dia = $dias_es[date('l', strtotime($fecha))];
                        $dia_num = date('d', strtotime($fecha));
                        $mes_nombre = $meses_es[date('m', strtotime($fecha))];
                        $anio = date('Y', strtotime($fecha));
                    ?>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width: 32px; height: 32px; border-radius: 10px; background: var(--gold-soft); display: flex; align-items: center; justify-content: center; color: var(--gold);">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div>
                            <div style="font-size: 16px; font-weight: 800; color: #111827; letter-spacing: -0.3px;">
                                <?= "$nombre_dia, $dia_num" ?>
                            </div>
                            <div style="font-size: 12px; font-weight: 600; color: var(--gold); text-transform: uppercase; letter-spacing: 0.5px;">
                                <?= "de $mes_nombre $anio" ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Timeline de citas -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-calendar3" style="color:var(--gold);margin-right:8px;"></i>
                    Citas del <?= date('d/m/Y', strtotime($fecha)) ?></h3>
                <span style="font-size:13px;color:var(--text-light);"><?= count($citas) ?> cita(s)</span>
            </div>
            <div class="content-card-body">
                <?php if (empty($citas)): ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h3>Sin citas para esta fecha</h3>
                        <p>No hay citas programadas para el <?= date('d/m/Y', strtotime($fecha)) ?>.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($citas as $c): ?>
                        <div class="timeline-item">
                            <div class="timeline-time"><?= substr($c['hora'], 0, 5) ?></div>
                            <div class="timeline-card <?= strtolower($c['estado']) ?>">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;">
                                    <div>
                                        <div style="display:flex; align-items:center; gap: 10px; margin-bottom: 4px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--gold-soft); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; overflow: hidden;">
                                                <?php if (!empty($c['cliente_foto'])): ?>
                                                    <img src="<?= $base_path ?>public/uploads/perfiles/<?= htmlspecialchars($c['cliente_foto']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <?= strtoupper(mb_substr($c['cliente'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-client" style="margin-bottom: 0;"><?= htmlspecialchars($c['cliente']) ?></div>
                                        </div>
                                        <div class="timeline-service">
                                            <?= htmlspecialchars($c['servicio']) ?>
                                            <span style="color:#d1d5db; margin:0 4px;">&bull;</span>
                                            <i class="bi bi-clock"></i> <?= $c['duracion_min'] ?> min
                                            <span style="color:#d1d5db; margin:0 4px;">&bull;</span>
                                            <span style="color:var(--gold); font-weight:800;">$<?= number_format($c['precio'], 0, ',', '.') ?></span>
                                        </div>
                                        <?php if ($c['cliente_telefono']): ?>
                                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($c['cliente_telefono']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                                        <span class="badge-estado badge-<?= strtolower($c['estado']) ?>">
                                            <?= ucfirst(strtolower($c['estado'])) ?>
                                        </span>
                                        <div class="table-actions">
                                            <?php if ($c['estado'] === 'PENDIENTE'): ?>
                                            <button class="btn btn-success btn-sm"
                                                    onclick="accionCita(<?= $c['id_cita'] ?>, 'aceptar')">
                                                <i class="bi bi-check"></i> Aceptar
                                            </button>
                                            <?php endif; ?>
                                            <?php if (in_array($c['estado'], ['PENDIENTE','ACEPTADA'])): ?>
                                            <button class="btn btn-secondary btn-sm"
                                                    onclick="abrirReprogramar(<?= $c['id_cita'] ?>, '<?= $c['fecha'] ?>', '<?= substr($c['hora'],0,5) ?>')">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                    onclick="accionCita(<?= $c['id_cita'] ?>, 'cancelar')">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($c['estado'] === 'ACEPTADA'): ?>
                                            <button class="btn btn-primary btn-sm"
                                                    onclick="accionCita(<?= $c['id_cita'] ?>, 'completar')">
                                                <i class="bi bi-check-all"></i> Completar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- /.page-content -->
</div><!-- /.main-content -->

<!-- Modal Reprogramar -->
<div class="modal-overlay" id="modalReprogramar">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-arrow-repeat" style="color:var(--gold);margin-right:8px;"></i>Reprogramar Cita</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body">
            <div id="infoActual" style="background:var(--background);border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;"></div>
            <form id="formReprogramar" method="POST" action="<?= $base_path ?>controllers/barbero/citasController.php">
                <input type="hidden" name="accion" value="reprogramar">
                <input type="hidden" name="id_cita" id="repCitaId">
                <div class="form-group">
                    <label class="form-label">Nueva fecha</label>
                    <input type="date" name="nueva_fecha" id="repFecha" class="form-control"
                        min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nueva hora</label>
                    <input type="time" name="nueva_hora" id="repHora" class="form-control" required>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                        El cliente tendrá <strong>3 minutos</strong> para confirmar o cancelar.
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="document.getElementById('formReprogramar').submit()">
                <i class="bi bi-arrow-repeat"></i> Reprogramar
            </button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function accionCita(id, accion) {
    var textos = {
        aceptar:   { title:'Aceptar cita', text:'¿Confirmas que aceptas esta cita?', btn:'Sí, aceptar', color:'#16a34a' },
        cancelar:  { title:'Cancelar cita', text:'¿Estás seguro de cancelar esta cita?', btn:'Sí, cancelar', color:'#dc2626' },
        completar: { title:'Completar cita', text:'¿Marcar esta cita como completada?', btn:'Sí, completar', color:'#b58a4a' }
    };
    var t = textos[accion];
    Swal.fire({
        title: t.title, text: t.text, icon: 'question',
        showCancelButton: true,
        confirmButtonColor: t.color, cancelButtonColor: '#6b7280',
        confirmButtonText: t.btn, cancelButtonText: 'Volver'
    }).then(function(r) {
        if (r.isConfirmed) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = '<?= $base_path ?>controllers/barbero/citasController.php';
            f.innerHTML = '<input name="accion" value="'+accion+'"><input name="id_cita" value="'+id+'">';
            document.body.appendChild(f);
            f.submit();
        }
    });
}

function abrirReprogramar(id, fecha, hora) {
    document.getElementById('repCitaId').value = id;
    document.getElementById('infoActual').innerHTML =
        '<strong>Cita actual:</strong> ' + fecha + ' a las ' + hora;
    document.getElementById('modalReprogramar').classList.add('open');
}

function cerrarModal() {
    document.getElementById('modalReprogramar').classList.remove('open');
}
</script>
</body>
</html>

