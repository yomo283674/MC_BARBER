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
    $flash = $ctrl->cambiarEstado((int)$_POST['id_cita'], trim($_POST['nuevo_estado'] ?? ''));
}

// Filtros GET
$f_estado    = $_GET['estado']      ?? '';
$f_desde     = $_GET['fecha_desde'] ?? '';
$f_hasta     = $_GET['fecha_hasta'] ?? '';
$f_barbero   = (int)($_GET['id_barbero'] ?? 0);

$ctrl    = new CitasAdminController($conn);
$citas   = $ctrl->listar($f_estado, $f_desde, $f_hasta, $f_barbero);
$barberos= $ctrl->getBarberos();
$stats   = $ctrl->getStats();

$pagina_activa = 'citas';
$titulo_pagina = 'Gestión de Citas';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas â€” Administrador | MC Barber</title>
    <meta name="description" content="Gestión global de citas del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-estado     { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .bg-pendiente     { background:rgba(217,119,6,.15); color:#d97706; }
        .bg-aceptada      { background:rgba(22,163,74,.15); color:#16a34a; }
        .bg-completada    { background:rgba(37,99,235,.15); color:#2563eb; }
        .bg-cancelada     { background:rgba(220,38,38,.15); color:#dc2626; }
        .bg-reprogramada  { background:rgba(181,138,74,.15); color:var(--gold); }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { text-align:left; padding:12px 16px; border-bottom:1px solid var(--border); color:var(--text-light); font-size:11px; font-weight:600; text-transform:uppercase; }
        .data-table td { padding:12px 16px; border-bottom:1px solid var(--border); font-size:13px; }
        .data-table tr:hover { background:rgba(0,0,0,.02); }
        .filters-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
        .btn-sm { padding:4px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px; font-weight:600; transition:.2s; }
        .btn-action { background:var(--gold-soft); color:var(--gold); }
        .btn-action:hover { background:var(--gold); color:#fff; }
        .kpi-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        .kpi-chip { flex:1; min-width:120px; background:var(--surface); border:1px solid var(--border);
                    border-radius:10px; padding:14px 18px; display:flex; align-items:center; gap:12px; }
        .kpi-chip-val { font-size:22px; font-weight:800; }
        .kpi-chip-lbl { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Citas</h1>
            <p>Gestión global de todas las citas del sistema</p>
        </div>

        <!-- KPI chips -->
        <div class="kpi-row">
            <div class="kpi-chip">
                <i class="bi bi-calendar3" style="font-size:22px; color:var(--gold)"></i>
                <div>
                    <div class="kpi-chip-val"><?= $stats['total'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Total</div>
                </div>
            </div>
            <div class="kpi-chip">
                <i class="bi bi-clock" style="font-size:22px; color:#d97706"></i>
                <div>
                    <div class="kpi-chip-val" style="color:#d97706"><?= $stats['pendiente'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Pendientes</div>
                </div>
            </div>
            <div class="kpi-chip">
                <i class="bi bi-check-circle" style="font-size:22px; color:#16a34a"></i>
                <div>
                    <div class="kpi-chip-val" style="color:#16a34a"><?= $stats['aceptada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Aceptadas</div>
                </div>
            </div>
            <div class="kpi-chip">
                <i class="bi bi-check2-all" style="font-size:22px; color:#2563eb"></i>
                <div>
                    <div class="kpi-chip-val" style="color:#2563eb"><?= $stats['completada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Completadas</div>
                </div>
            </div>
            <div class="kpi-chip">
                <i class="bi bi-x-circle" style="font-size:22px; color:#dc2626"></i>
                <div>
                    <div class="kpi-chip-val" style="color:#dc2626"><?= $stats['cancelada'] ?? 0 ?></div>
                    <div class="kpi-chip-lbl">Canceladas</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="content-card" style="margin-bottom:16px">
            <div class="content-card-body">
                <form method="GET" class="filters-bar">
                    <div>
                        <label class="form-label" style="font-size:11px">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control"
                               value="<?= htmlspecialchars($f_desde) ?>" style="max-width:170px">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:11px">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control"
                               value="<?= htmlspecialchars($f_hasta) ?>" style="max-width:170px">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:11px">Estado</label>
                        <select name="estado" class="form-control" style="max-width:160px">
                            <option value="">Todos</option>
                            <?php foreach (['PENDIENTE','ACEPTADA','COMPLETADA','CANCELADA','REPROGRAMADA'] as $e): ?>
                            <option value="<?= $e ?>" <?= $f_estado === $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:11px">Barbero</label>
                        <select name="id_barbero" class="form-control" style="max-width:200px">
                            <option value="0">Todos</option>
                            <?php foreach ($barberos as $bar): ?>
                            <option value="<?= $bar['id_usuario'] ?>" <?= $f_barbero === $bar['id_usuario'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bar['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="align-self:flex-end; display:flex; gap:8px">
                        <button type="submit" class="btn btn-gold"><i class="bi bi-search"></i> Filtrar</button>
                        <a href="citas.php" class="btn btn-outline"><i class="bi bi-x"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-calendar-check me-2" style="color:var(--gold)"></i>Todas las citas</h3>
                <span style="font-size:12px; color:var(--text-muted)"><?= count($citas) ?> resultados</span>
            </div>
            <div class="content-card-body p-0" style="overflow-x:auto">
                <?php if (empty($citas)): ?>
                    <div style="padding:50px; text-align:center; color:var(--text-muted)">
                        <i class="bi bi-calendar-x" style="font-size:40px; opacity:.3"></i>
                        <p style="margin-top:12px">No se encontraron citas con los filtros aplicados</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Barbero</th>
                                <th>Servicio</th>
                                <th>Fecha / Hora</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Cambiar estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $c): ?>
                            <tr>
                                <td style="color:var(--text-muted); font-size:11px">#<?= $c['id_cita'] ?></td>
                                <td>
                                    <div style="font-weight:600"><?= htmlspecialchars($c['cliente']) ?></div>
                                    <small style="color:var(--text-muted)"><?= htmlspecialchars($c['tel_cliente'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($c['barbero']) ?></td>
                                <td>
                                    <div><?= htmlspecialchars($c['servicio']) ?></div>
                                    <small style="color:var(--text-muted)"><?= $c['duracion_minutos'] ?> min</small>
                                </td>
                                <td style="white-space:nowrap">
                                    <?= date('d/m/Y', strtotime($c['fecha'])) ?><br>
                                    <small style="color:var(--text-muted)"><?= substr($c['hora'],0,5) ?></small>
                                </td>
                                <td style="font-weight:600; color:var(--gold)">$<?= number_format($c['precio'],0,',','.') ?></td>
                                <td>
                                    <span class="badge-estado bg-<?= strtolower($c['estado']) ?>">
                                        <?= $c['estado'] ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-control" style="font-size:12px; max-width:150px;"
                                        onchange="cambiarEstado(<?= $c['id_cita'] ?>, this.value, this)">
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

