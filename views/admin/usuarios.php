<?php
/**
 * views/admin/usuarios.php
 * Gestión de todos los usuarios â€” Dashboard Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/usuariosController.php';
global $conn;

// Procesar POST cambio de estado
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $ctrl  = new UsuariosController($conn);
    $flash = $ctrl->cambiarEstado((int)$_POST['id'], trim($_POST['estado'] ?? ''));
}

// Filtros GET
$filtro_rol    = $_GET['rol']    ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_buscar = $_GET['buscar'] ?? '';

$ctrl    = new UsuariosController($conn);
$usuarios= $ctrl->listar($filtro_rol, $filtro_estado, $filtro_buscar);
$stats   = $ctrl->getStats();

$pagina_activa = 'usuarios';
$titulo_pagina = 'Gestión de Usuarios';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios â€” Administrador | MC Barber</title>
    <meta name="description" content="Gestión de usuarios del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-estado  { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .bg-activo     { background:rgba(22,163,74,.15); color:#16a34a; }
        .bg-inactivo   { background:rgba(220,38,38,.15); color:#dc2626; }
        .bg-suspendido { background:rgba(217,119,6,.15); color:#d97706; }
        .badge-rol-barbero { background:rgba(37,99,235,.12); color:#2563eb; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-rol-cliente { background:rgba(181,138,74,.12); color:var(--gold); padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600; }
        .data-table th { text-align:left; padding:16px 24px; border-bottom:1px solid #e5e7eb; color:#9ca3af; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; background: #f9fafb; white-space: nowrap; }
        .data-table td { padding:16px 24px; border-bottom:1px solid #f3f4f6; font-size:13px; vertical-align: middle; white-space: nowrap; }
        .data-table    { width:100%; border-collapse:collapse; }
        .data-table tr { transition: background 0.2s ease; }
        .data-table tbody tr:hover { background:#f8fafc; }
        .filters-bar   { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .btn-sm { padding:4px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px; font-weight:600; transition:.2s; }
        .btn-danger  { background:rgba(220,38,38,.12); color:#dc2626; }
        .btn-danger:hover { background:#dc2626; color:#fff; }
        .btn-success { background:rgba(22,163,74,.12); color:#16a34a; }
        .btn-success:hover { background:#16a34a; color:#fff; }
        .avatar-sm { width:34px; height:34px; border-radius:50%; background:var(--gold-soft); color:var(--gold); font-weight:700; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .filter-input { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 16px; font-size: 13px; color: #111827; outline: none; transition: all 0.3s ease; width: 100%; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); }
        .filter-input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,0.15); background: #ffffff; }
        .filter-select { padding-right: 36px; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 14px center; background-size: 12px 12px; cursor: pointer; }
        .btn-filtrar { background: #111827; color: #ffffff; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-filtrar:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: #fff; }
        .btn-limpiar { background: transparent; color: #6b7280; border: none; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-limpiar:hover { background: #f3f4f6; color: #111827; }
        .input-icon-wrapper { position: relative; width: 100%; max-width: 240px; }
        .input-icon-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }
        .input-icon-wrapper input { padding-left: 38px; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Usuarios</h1>
            <p>Clientes y barberos del sistema</p>
        </div>

        <!-- KPIs -->
        <div class="stats-grid" style="margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total usuarios</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-person-check-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['clientes_activos'] ?></div>
                    <div class="stat-label">Clientes activos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-scissors"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['barberos_activos'] ?></div>
                    <div class="stat-label">Barberos activos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-slash-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['suspendidos'] ?></div>
                    <div class="stat-label">Suspendidos</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="content-card" style="margin-bottom:20px; border:none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div class="content-card-body" style="padding: 16px 20px;">
                <form method="GET" class="filters-bar" style="gap: 12px;">
                    <div class="input-icon-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" name="buscar" class="filter-input"
                               value="<?= htmlspecialchars($filtro_buscar) ?>" placeholder="Buscar nombre o email...">
                    </div>
                    <select name="rol" class="filter-input filter-select" style="max-width:180px;">
                        <option value="">Todos los roles</option>
                        <option value="BARBERO"  <?= $filtro_rol === 'BARBERO'  ? 'selected' : '' ?>>Barbero</option>
                        <option value="CLIENTE"  <?= $filtro_rol === 'CLIENTE'  ? 'selected' : '' ?>>Cliente</option>
                    </select>
                    <select name="estado" class="filter-input filter-select" style="max-width:180px;">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVO"     <?= $filtro_estado === 'ACTIVO'     ? 'selected' : '' ?>>Activo</option>
                        <option value="INACTIVO"   <?= $filtro_estado === 'INACTIVO'   ? 'selected' : '' ?>>Inactivo</option>
                        <option value="SUSPENDIDO" <?= $filtro_estado === 'SUSPENDIDO' ? 'selected' : '' ?>>Suspendido</option>
                    </select>
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                    <a href="usuarios.php" class="btn-limpiar"><i class="bi bi-arrow-counterclockwise"></i> Limpiar</a>
                </form>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-people me-2" style="color:var(--gold)"></i>Lista de usuarios</h3>
                <span style="font-size:12px; color:var(--text-muted)"><?= count($usuarios) ?> resultados</span>
            </div>
            <div class="content-card-body p-0" style="overflow-x:auto">
                <?php if (empty($usuarios)): ?>
                    <div style="padding:50px; text-align:center; color:var(--text-muted)">
                        <i class="bi bi-person-x" style="font-size:40px; opacity:.3"></i>
                        <p style="margin-top:12px">No se encontraron usuarios con los filtros aplicados</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Último acceso</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:16px;">
                                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border: 1px solid #d1d5db; display: flex; align-items: center; justify-content: center; color: #4b5563; font-size: 16px; font-weight: 800; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                            <?= strtoupper(mb_substr($u['nombre'],0,1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; font-size: 14.5px; color: #111827; letter-spacing: -0.2px; margin-bottom: 4px;"><?= htmlspecialchars($u['nombre']) ?></div>
                                            <div style="color: #6b7280; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="bi bi-envelope" style="color: #9ca3af;"></i> <?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="background: <?= $u['rol'] === 'BARBERO' ? 'rgba(37,99,235,0.08)' : 'rgba(212,175,55,0.08)' ?>; color: <?= $u['rol'] === 'BARBERO' ? '#2563eb' : '#b58a4a' ?>; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid <?= $u['rol'] === 'BARBERO' ? 'rgba(37,99,235,0.2)' : 'rgba(212,175,55,0.2)' ?>;">
                                        <?= $u['rol'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #374151;"><?= htmlspecialchars($u['telefono'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <span class="badge-estado bg-<?= strtolower($u['estado']) ?>" style="padding: 8px 16px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; justify-content: center;">
                                        <?= $u['estado'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="color: #4b5563; font-weight: 600;">
                                        <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: #4b5563; font-weight: 600;">
                                        <?= $u['ultimo_acceso'] ? date('d/m/Y <\s\p\a\n \s\t\y\l\e="\c\o\l\o\r\:\#\d\1\d\5\d\b\;\f\o\n\t\-\w\e\i\g\h\t\:400\;"\>|\<\/\s\p\a\n\> H:i', strtotime($u['ultimo_acceso'])) : '<span style="color:#9ca3af;">-</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u['estado'] === 'ACTIVO'): ?>
                                    <button style="background: rgba(220,38,38,0.08); color: #dc2626; border: 1px solid rgba(220,38,38,0.2); padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;"
                                        onmouseover="this.style.background='#dc2626'; this.style.color='#fff';" onmouseout="this.style.background='rgba(220,38,38,0.08)'; this.style.color='#dc2626';"
                                        onclick="cambiarEstado(<?= $u['id_usuario'] ?>, 'SUSPENDIDO', '<?= htmlspecialchars($u['nombre']) ?>')">
                                        <i class="bi bi-slash-circle"></i> Suspender
                                    </button>
                                    <?php else: ?>
                                    <button style="background: rgba(22,163,74,0.08); color: #16a34a; border: 1px solid rgba(22,163,74,0.2); padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;"
                                        onmouseover="this.style.background='#16a34a'; this.style.color='#fff';" onmouseout="this.style.background='rgba(22,163,74,0.08)'; this.style.color='#16a34a';"
                                        onclick="cambiarEstado(<?= $u['id_usuario'] ?>, 'ACTIVO', '<?= htmlspecialchars($u['nombre']) ?>')">
                                        <i class="bi bi-check-circle"></i> Activar
                                    </button>
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

<!-- Form oculto cambiar estado -->
<form method="POST" id="formEstado" style="display:none">
    <input type="hidden" name="id"     id="estadoId">
    <input type="hidden" name="estado" id="estadoValor">
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
function cambiarEstado(id, estado, nombre) {
    Swal.fire({
        title: '¿Confirmar acción?',
        text: `¿Deseas ${estado === 'SUSPENDIDO' ? 'suspender' : 'activar'} a "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b58a4a',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, confirmar'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('estadoId').value    = id;
            document.getElementById('estadoValor').value = estado;
            document.getElementById('formEstado').submit();
        }
    });
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

