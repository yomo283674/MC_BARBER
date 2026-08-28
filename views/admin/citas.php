<?php
/**
 * views/admin/citas.php
 * Gestión global de citas â€” Dashboard Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/citasAdminController.php';
global $conn;

// Procesar POST
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $ctrl  = new CitasAdminController($conn);
    $accion = $_POST['accion'];
    
    if ($accion === 'cambiar_estado') {
        $flash = $ctrl->cambiarEstado((int)$_POST['id_cita'], trim($_POST['nuevo_estado'] ?? ''));
    } elseif ($accion === 'crear') {
        $flash = $ctrl->crearCita(
            (int)$_POST['id_cliente'],
            (int)$_POST['id_barbero'],
            (int)$_POST['id_servicio'],
            trim($_POST['fecha'] ?? ''),
            trim($_POST['hora'] ?? '')
        );
    }
}

// Filtros GET
$f_estado    = $_GET['estado']      ?? '';
$f_desde     = $_GET['fecha_desde'] ?? '';
$f_hasta     = $_GET['fecha_hasta'] ?? '';
$f_barbero   = (int)($_GET['id_barbero'] ?? 0);

$ctrl    = new CitasAdminController($conn);
$citas   = $ctrl->listar($f_estado, $f_desde, $f_hasta, $f_barbero);
$barberos = $ctrl->getBarberos();
$clientes = $ctrl->getClientes();
$servicios = $ctrl->getServicios();
$stats    = $ctrl->getStats();

$pagina_activa = 'citas';
$titulo_pagina = 'Gestión de Citas';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Administrador | MC Barber</title>
    <meta name="description" content="Gestión global de citas del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        /* Estados */
        .badge-estado     { padding:6px 12px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing: 0.5px; display: inline-block; text-align: center; min-width: 90px; }
        .bg-pendiente     { background:rgba(217,119,6,.12); color:#d97706; border: 1px solid rgba(217,119,6,.2); }
        .bg-aceptada      { background:rgba(22,163,74,.12); color:#16a34a; border: 1px solid rgba(22,163,74,.2); }
        .bg-completada    { background:rgba(37,99,235,.12); color:#2563eb; border: 1px solid rgba(37,99,235,.2); }
        .bg-cancelada     { background:rgba(220,38,38,.12); color:#dc2626; border: 1px solid rgba(220,38,38,.2); }
        .bg-reprogramada  { background:rgba(181,138,74,.12); color:var(--gold); border: 1px solid rgba(181,138,74,.2); }
        
        /* Data Table */
        .data-table { width:100%; border-collapse: separate; border-spacing: 0; white-space: nowrap; }
        .data-table th { text-align:left; padding:16px; border-bottom:1px solid rgba(0,0,0,0.05); color:#6b7280; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing: 0.05em; background: #f8fafc; }
        .data-table th:first-child { border-top-left-radius: 8px; }
        .data-table th:last-child { border-top-right-radius: 8px; }
        .data-table td { padding:16px; border-bottom:1px solid rgba(0,0,0,0.03); font-size:13.5px; vertical-align: middle; transition: background 0.2s; }
        .data-table tbody tr:hover td { background:rgba(181,138,74,0.03); }
        
        /* Select de estado en tabla */
        .select-estado { 
            padding: 8px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 600; 
            background-color: #fff; color: #374151; cursor: pointer; transition: all 0.2s; width: 145px;
            appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 8px center; background-size: 16px; padding-right: 32px;
        }
        .select-estado:hover { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(181,138,74,0.1); }
        .select-estado:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(181,138,74,0.2); }

        /* Filters */
        .filters-bar { display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 24px; }
        .filters-bar .form-label { font-size:12px; font-weight: 600; color: #4b5563; margin-bottom: 6px; display: block; }
        .filters-bar .form-control { border-radius: 8px; border: 1px solid #e5e7eb; padding: 8px 12px; font-size: 13px; transition: all 0.2s; background: #f9fafb; }
        .filters-bar .form-control:focus { background: #fff; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(181,138,74,0.1); }
        
        .btn-filter { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-limpiar { color: #6b7280; background: transparent; border: 1px solid transparent; text-decoration: none; }
        .btn-limpiar:hover { color: #111827; background: #f3f4f6; }

        /* KPI Chips Modernos */
        .kpi-row { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px; }
        .kpi-chip { 
            background:#fff; border:1px solid rgba(0,0,0,0.04); border-radius:16px; padding:20px; 
            display:flex; align-items:center; gap:16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default; position: relative; overflow: hidden;
        }
        .kpi-chip:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -8px rgba(0,0,0,0.08); border-color: rgba(181,138,74,0.2); }
        .kpi-icon-box { 
            width: 48px; height: 48px; border-radius: 12px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;
        }
        .kpi-chip-val { font-size:26px; font-weight:800; color: #111827; line-height: 1.2; letter-spacing: -0.5px; }
        .kpi-chip-lbl { font-size:12px; font-weight: 600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; }
        
        .icon-box-total { background: rgba(181,138,74,0.1); color: var(--gold); }
        .icon-box-pendientes { background: rgba(217,119,6,0.1); color: #d97706; }
        .icon-box-aceptadas { background: rgba(22,163,74,0.1); color: #16a34a; }
        .icon-box-completadas { background: rgba(37,99,235,0.1); color: #2563eb; }
        .icon-box-canceladas { background: rgba(220,38,38,0.1); color: #dc2626; }

        .content-card-citas { background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; }
        .card-header-citas { padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-header-citas h3 { margin: 0; font-size: 16px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }
        
        /* Info cliente/barbero en tabla */
        .td-info-title { font-weight: 700; color: #111827; margin-bottom: 2px; }
        .td-info-sub { color: #6b7280; font-size: 12px; display: flex; align-items: center; gap: 4px; }
        
        .avatar-circle {
            width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; color: #fff; flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-transform: uppercase;
        }
        .avatar-client { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
        .avatar-barber { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .user-cell { display: flex; align-items: center; gap: 12px; }
        
        .badge-id { background: #f3f4f6; color: #6b7280; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: monospace; }
        .price-text { font-weight: 800; color: #16a34a; background: rgba(22,163,74,0.1); padding: 4px 8px; border-radius: 8px; display: inline-block; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="margin:0">Citas</h1>
                <p style="margin:4px 0 0; color:var(--text-muted)">Gestión global de todas las citas del sistema</p>
            </div>
            <button class="btn btn-gold" onclick="document.getElementById('modalCrear').classList.add('open')">
                <i class="bi bi-plus-lg"></i> Nueva Cita
            </button>
        </div>

        <!-- KPI chips -->
        <div class="kpi-row">
            <div class="kpi-chip">
                <div class="kpi-icon-box icon-box-total"><i class="bi bi-calendar3" style="font-size:22px;"></i></div>
                <div>
                    <div class="kpi-chip-val"><?= $stats['total'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Total</div>
                </div>
            </div>
            <div class="kpi-chip">
                <div class="kpi-icon-box icon-box-pendientes"><i class="bi bi-clock" style="font-size:22px;"></i></div>
                <div>
                    <div class="kpi-chip-val" style="color:#d97706"><?= $stats['pendiente'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Pendientes</div>
                </div>
            </div>
            <div class="kpi-chip">
                <div class="kpi-icon-box icon-box-aceptadas"><i class="bi bi-check-circle" style="font-size:22px;"></i></div>
                <div>
                    <div class="kpi-chip-val" style="color:#16a34a"><?= $stats['aceptada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Aceptadas</div>
                </div>
            </div>
            <div class="kpi-chip">
                <div class="kpi-icon-box icon-box-completadas"><i class="bi bi-check2-all" style="font-size:22px;"></i></div>
                <div>
                    <div class="kpi-chip-val" style="color:#2563eb"><?= $stats['completada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Completadas</div>
                </div>
            </div>
            <div class="kpi-chip">
                <div class="kpi-icon-box icon-box-canceladas"><i class="bi bi-x-circle" style="font-size:22px;"></i></div>
                <div>
                    <div class="kpi-chip-val" style="color:#dc2626"><?= $stats['cancelada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Canceladas</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <form method="GET" class="filters-bar">
            <div>
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($f_desde) ?>" style="width:140px">
            </div>
            <div>
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($f_hasta) ?>" style="width:140px">
            </div>
            <div>
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control" style="width:150px">
                    <option value="">Todos</option>
                    <?php foreach (['PENDIENTE','ACEPTADA','COMPLETADA','CANCELADA','REPROGRAMADA'] as $e): ?>
                    <option value="<?= $e ?>" <?= $f_estado === $e ? 'selected' : '' ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Barbero</label>
                <select name="id_barbero" class="form-control" style="width:180px">
                    <option value="0">Todos</option>
                    <?php foreach ($barberos as $bar): ?>
                    <option value="<?= $bar['id_usuario'] ?>" <?= $f_barbero === $bar['id_usuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($bar['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self:flex-end; display:flex; gap:8px">
                <button type="submit" class="btn btn-gold btn-filter"><i class="bi bi-search"></i> Filtrar</button>
                <a href="citas.php" class="btn-filter btn-limpiar"><i class="bi bi-x-lg"></i> Limpiar</a>
            </div>
        </form>

        <!-- Tabla de citas -->
        <div class="content-card-citas">
            <div class="card-header-citas">
                <h3><i class="bi bi-calendar-check" style="color:var(--gold)"></i> Citas programadas</h3>
                <span style="font-size:12px; font-weight:600; color:var(--text-muted); background:#f3f4f6; padding:4px 10px; border-radius:20px;"><?= count($citas) ?> resultados</span>
            </div>
            <div style="overflow-x:auto">
                <?php if (empty($citas)): ?>
                    <div style="padding:60px 20px; text-align:center; color:var(--text-muted)">
                        <i class="bi bi-calendar-x" style="font-size:48px; opacity:.3; margin-bottom:16px; display:inline-block"></i>
                        <h4 style="margin:0; font-weight:600; color:#374151">No hay citas</h4>
                        <p style="margin-top:8px; font-size:14px">No se encontraron citas con los filtros actuales.</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Barbero</th>
                                <th>Servicio</th>
                                <th>Fecha y Hora</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th style="text-align:right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $c): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <?php if (!empty($c['foto_cliente'])): ?>
                                            <div style="width: 38px; height: 38px; border-radius: 50%; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 2px solid #fff; flex-shrink: 0;">
                                                <img src="<?= htmlspecialchars(PROFUNDIDAD . ltrim($c['foto_cliente'], '/')) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Foto de <?= htmlspecialchars($c['cliente']) ?>" onerror="this.outerHTML='<div class=\'avatar-circle avatar-client\'><?= substr(htmlspecialchars($c['cliente']), 0, 1) ?></div>'">
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-circle avatar-client"><?= substr($c['cliente'], 0, 1) ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="td-info-title"><?= htmlspecialchars($c['cliente']) ?></div>
                                            <div class="td-info-sub"><i class="bi bi-telephone"></i> <?= htmlspecialchars($c['tel_cliente'] ?? 'N/A') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar-circle avatar-barber"><?= substr($c['barbero'], 0, 1) ?></div>
                                        <div class="td-info-title"><?= htmlspecialchars($c['barbero']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="td-info-title"><?= htmlspecialchars($c['servicio']) ?></div>
                                    <div class="td-info-sub"><i class="bi bi-clock"></i> <?= $c['duracion_minutos'] ?> min</div>
                                </td>
                                <td style="white-space:nowrap">
                                    <div class="td-info-title"><i class="bi bi-calendar-event" style="color:#9ca3af; margin-right:4px"></i> <?= date('d M Y', strtotime($c['fecha'])) ?></div>
                                    <div class="td-info-sub" style="margin-top:2px"><i class="bi bi-clock-history"></i> <?= substr($c['hora'],0,5) ?></div>
                                </td>
                                <td><span class="price-text">$<?= number_format($c['precio'],0,',','.') ?></span></td>
                                <td>
                                    <span class="badge-estado bg-<?= strtolower($c['estado']) ?>">
                                        <?= $c['estado'] ?>
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <select class="select-estado" onchange="cambiarEstado(<?= $c['id_cita'] ?>, this.value, this)">
                                        <?php foreach (['PENDIENTE','ACEPTADA','COMPLETADA','CANCELADA','REPROGRAMADA'] as $e): ?>
                                        <option value="<?= $e ?>" <?= $c['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /page-content -->
</div><!-- /main-content -->

<!-- Modal Crear Cita -->
<div class="modal-overlay" id="modalCrear">
    <div class="modal-box" style="max-width:500px">
        <div class="modal-header">
            <h4 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Nueva Cita</h4>
            <button class="modal-close" onclick="cerrarModales()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="content-card-body">
                <div class="form-group">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" class="form-control" required>
                        <option value="">Seleccione un cliente...</option>
                        <?php foreach ($clientes as $cli): ?>
                        <option value="<?= $cli['id_usuario'] ?>"><?= htmlspecialchars($cli['nombre']) ?> (<?= htmlspecialchars($cli['telefono'] ?? 'Sin tel') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Barbero *</label>
                    <select name="id_barbero" class="form-control" required>
                        <option value="">Seleccione un barbero...</option>
                        <?php foreach ($barberos as $bar): ?>
                        <option value="<?= $bar['id_usuario'] ?>"><?= htmlspecialchars($bar['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Servicio *</label>
                    <select name="id_servicio" class="form-control" required>
                        <option value="">Seleccione un servicio...</option>
                        <?php foreach ($servicios as $srv): ?>
                        <option value="<?= $srv['id_servicio'] ?>"><?= htmlspecialchars($srv['nombre']) ?> ($<?= number_format($srv['precio'],0,',','.') ?> - <?= $srv['duracion_minutos'] ?> min)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora *</label>
                        <input type="time" name="hora" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end; padding:16px 20px; border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn btn-gold">Crear Cita</button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto cambiar estado -->
<form method="POST" id="formEstado" style="display:none">
    <input type="hidden" name="id_cita"      id="citaId">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
    <input type="hidden" name="accion"       value="cambiar_estado">
</form>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('open');
}
function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.querySelector('.sidebar-overlay').classList.remove('open');
}
function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) cerrarModales(); });
});
function cambiarEstado(id, nuevoEstado, selectEl) {
    const anterior = selectEl.dataset.anterior || selectEl.value;
    Swal.fire({
        title: '¿Cambiar estado?',
        text: `Cita #${id} â†’ ${nuevoEstado}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#b58a4a',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, cambiar'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('citaId').value     = id;
            document.getElementById('nuevoEstado').value = nuevoEstado;
            document.getElementById('formEstado').submit();
        } else {
            // Restaurar select al valor anterior
            selectEl.value = anterior;
        }
    });
    selectEl.dataset.anterior = selectEl.value;
}
<?php if ($flash): ?>
Swal.fire({
    icon: '<?= $flash['ok'] ? 'success' : 'error' ?>',
    title: '<?= $flash['ok'] ? '¡Listo!' : 'Error' ?>',
    text: '<?= addslashes($flash['msg']) ?>',
    confirmButtonColor: '#b58a4a'
});
<?php endif; ?>
const p = new URLSearchParams(window.location.search);
if (p.get('expired') === '1') {
    Swal.fire({ icon:'warning', title:'Sesión expirada', text:'Tu sesión cerró por inactividad.', confirmButtonColor:'#b58a4a' });
}
</script>
</body>
</html>

