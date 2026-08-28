<?php
/**
 * views/admin/auditoria.php
 * Visualización del log de auditoría para el Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/auditoriaController.php';
global $conn;

// Filtros GET
$filtro_desde     = $_GET['desde']     ?? '';
$filtro_hasta     = $_GET['hasta']     ?? '';
$filtro_accion    = $_GET['accion']    ?? '';
$filtro_resultado = $_GET['resultado'] ?? '';

$ctrl  = new AuditoriaController($conn);
$logs  = $ctrl->listar($filtro_desde, $filtro_hasta, $filtro_accion, $filtro_resultado);
$stats = $ctrl->getStats();

$pagina_activa = 'auditoria';
$titulo_pagina = 'Auditoría del Sistema';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría | MC Barber</title>
    <meta name="description" content="Registro de auditoría del sistema">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-resultado { padding:6px 14px; border-radius:30px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; display: inline-flex; align-items: center; justify-content: center; gap:6px; border: 1px solid transparent; }
        .bg-exitoso      { background:rgba(22,163,74,.08); color:#16a34a; border-color: rgba(22,163,74,.2); }
        .bg-fallido      { background:rgba(220,38,38,.08); color:#dc2626; border-color: rgba(220,38,38,.2); }
        .bg-default      { background:rgba(107,114,128,.08); color:#4b5563; border-color: rgba(107,114,128,.2); }
        
        .data-table th { text-align:left; padding:18px 24px; border-bottom:2px solid #f3f4f6; color:#6b7280; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; background: #ffffff; white-space: nowrap; }
        .data-table td { padding:18px 24px; border-bottom:1px solid #f3f4f6; font-size:14px; vertical-align: middle; }
        .data-table    { width:100%; border-collapse:collapse; }
        .data-table tr { transition: all 0.2s ease; }
        .data-table tbody tr:hover { background:#f9fafb; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); z-index: 10; position: relative; }
        
        .filters-bar   { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-input  { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 18px; font-size: 13px; color: #111827; outline: none; transition: all 0.3s ease; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); font-weight: 500; }
        .filter-input:focus { border-color: #d4af37; box-shadow: 0 0 0 4px rgba(212,175,55,0.15); background: #ffffff; }
        .filter-select { padding-right: 40px; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 16px center; background-size: 12px 12px; cursor: pointer; }
        
        .btn-filtrar { background: linear-gradient(135deg, #111827 0%, #1f2937 100%); color: #ffffff; border: none; padding: 12px 28px; border-radius: 12px; font-size: 13.5px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
        .btn-filtrar:hover { background: linear-gradient(135deg, #000000 0%, #111827 100%); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
        .btn-limpiar { background: transparent; color: #6b7280; border: none; padding: 12px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-limpiar:hover { background: #f3f4f6; color: #111827; }
        
        .detalle-code { background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 11.5px; color: #475569; display: inline-flex; align-items: center; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .detalle-code i { color: #94a3b8; margin-right: 6px; font-size: 13px; }
        
        .stat-card { background: #fff; border: 1px solid #f3f4f6; border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); border-color: #e5e7eb; }
        
        .content-card { background: #fff; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f3f4f6; overflow: hidden; }
        .content-card-header { padding: 24px 30px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; background: #fff; }
        .content-card-header h3 { margin: 0; font-size: 18px; font-weight: 800; color: #111827; display: flex; align-items: center; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Auditoría</h1>
            <p>Registro de actividades y eventos del sistema</p>
        </div>

        <!-- KPIs -->
        <div class="stats-grid" style="margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-card-list"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total eventos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['exitosos'] ?></div>
                    <div class="stat-label">Eventos exitosos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['fallidos'] ?></div>
                    <div class="stat-label">Eventos fallidos</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="content-card" style="margin-bottom:20px; border:none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div class="content-card-body" style="padding: 16px 20px;">
                <form method="GET" class="filters-bar" style="gap: 12px;">
                    <input type="date" name="desde" class="filter-input" title="Fecha inicio"
                           value="<?= htmlspecialchars($filtro_desde) ?>">
                    <input type="date" name="hasta" class="filter-input" title="Fecha fin"
                           value="<?= htmlspecialchars($filtro_hasta) ?>">
                    
                    <input type="text" name="accion" class="filter-input" style="width: 200px;"
                           value="<?= htmlspecialchars($filtro_accion) ?>" placeholder="Buscar acción...">
                           
                    <select name="resultado" class="filter-input filter-select" style="max-width:180px;">
                        <option value="">Todos los resultados</option>
                        <option value="EXITOSO" <?= $filtro_resultado === 'EXITOSO' ? 'selected' : '' ?>>Exitoso</option>
                        <option value="FALLIDO" <?= $filtro_resultado === 'FALLIDO' ? 'selected' : '' ?>>Fallido</option>
                    </select>
                    <button type="submit" class="btn-filtrar"><i class="bi bi-funnel-fill"></i> Filtrar</button>
                    <a href="auditoria.php" class="btn-limpiar"><i class="bi bi-arrow-counterclockwise"></i> Restaurar</a>
                </form>
            </div>
        </div>

        <!-- Tabla de auditoria -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-shield-check me-2" style="color:var(--gold); font-size: 20px;"></i>Historial de Eventos</h3>
                <span style="font-size:12px; color:var(--text-muted); background: #f3f4f6; padding: 4px 10px; border-radius: 20px; font-weight: 600;"><?= count($logs) ?> resultados (Máx. 500)</span>
            </div>
            <div class="content-card-body p-0" style="overflow-x:auto">
                <?php if (empty($logs)): ?>
                    <div style="padding:50px; text-align:center; color:var(--text-muted)">
                        <i class="bi bi-search" style="font-size:40px; opacity:.3"></i>
                        <p style="margin-top:12px">No se encontraron registros de auditoría</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>Resultado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #111827; letter-spacing: -0.2px;">
                                        <?= date('d/m/Y <\s\p\a\n \s\t\y\l\e="\c\o\l\o\r\:\#\d\1\d\5\d\b\;\f\o\n\t\-\w\e\i\g\h\t\:400\;"\>|\<\/\s\p\a\n\> H:i:s', strtotime($log['fecha_hora'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: #111827;">
                                        <?= htmlspecialchars($log['usuario_nombre'] ?? 'Sistema') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log['rol']): ?>
                                    <span style="background: rgba(107,114,128,0.08); color: #4b5563; padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(107,114,128,0.2); letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($log['rol']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span style="color: #9ca3af; font-weight: 600;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #374151; background: #f9fafb; display: inline-block; padding: 4px 10px; border-radius: 6px; border: 1px solid #f3f4f6;">
                                        <?= htmlspecialchars($log['accion']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: #6b7280; font-size: 12px;">
                                        <?= htmlspecialchars($log['entidad_afectada'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-default';
                                        $iconClass = 'bi-info-circle-fill';
                                        if ($log['resultado'] === 'EXITOSO') {
                                            $badgeClass = 'bg-exitoso';
                                            $iconClass = 'bi-check-circle-fill';
                                        }
                                        if ($log['resultado'] === 'FALLIDO') {
                                            $badgeClass = 'bg-fallido';
                                            $iconClass = 'bi-x-circle-fill';
                                        }
                                    ?>
                                    <span class="badge-resultado <?= $badgeClass ?>">
                                        <i class="bi <?= $iconClass ?>"></i> <?= htmlspecialchars($log['resultado'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($log['detalle'])): ?>
                                        <div class="detalle-code" title="<?= htmlspecialchars($log['detalle']) ?>">
                                            <i class="bi bi-code-square"></i>
                                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?= htmlspecialchars($log['detalle']) ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-weight: 600;">-</span>
                                    <?php endif; ?>
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

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('open');
}
function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.querySelector('.sidebar-overlay').classList.remove('open');
}

const p = new URLSearchParams(window.location.search);
if (p.get('expired') === '1') {
    Swal.fire({ icon:'warning', title:'Sesión expirada', text:'Tu sesión cerró por inactividad.', confirmButtonColor:'#b58a4a' });
}
</script>
</body>
</html>
