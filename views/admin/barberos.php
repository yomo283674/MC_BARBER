<?php
/**
 * views/admin/barberos.php
 * Gestión de barberos â€” Dashboard Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/barberosController.php';
global $conn;

// Procesar acciones POST
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new BarberosController($conn);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $flash = $ctrl->crear(
            trim($_POST['nombre']   ?? ''),
            trim($_POST['email']    ?? ''),
            trim($_POST['telefono'] ?? ''),
            trim($_POST['password'] ?? '')
        );
    } elseif ($accion === 'editar') {
        $flash = $ctrl->actualizar(
            (int)($_POST['id']       ?? 0),
            trim($_POST['nombre']    ?? ''),
            trim($_POST['telefono']  ?? ''),
            trim($_POST['estado']    ?? 'ACTIVO')
        );
    } elseif ($accion === 'cambiar_estado') {
        $flash = $ctrl->cambiarEstado(
            (int)($_POST['id']     ?? 0),
            trim($_POST['estado']  ?? 'ACTIVO')
        );
    }
}

$ctrl     = new BarberosController($conn);
$barberos = $ctrl->listar();

$pagina_activa = 'barberos';
$titulo_pagina = 'Gestión de Barberos';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barberos â€” Administrador | MC Barber</title>
    <meta name="description" content="Gestión de barberos del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-estado { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .bg-activo    { background: rgba(22,163,74,.15); color: #16a34a; }
        .bg-inactivo  { background: rgba(220,38,38,.15); color: #dc2626; }
        .bg-suspendido{ background: rgba(217,119,6,.15); color: #d97706; }
        .data-table   { width:100%; border-collapse:collapse; }
        .data-table th{ text-align:left; padding:12px 16px; border-bottom:1px solid var(--border);
                        color:var(--text-light); font-size:11px; font-weight:600; text-transform:uppercase; }
        .data-table td{ padding:12px 16px; border-bottom:1px solid var(--border); font-size:13px; }
        .data-table tr:hover{ background:rgba(0,0,0,.02); }
        .btn-sm { padding:4px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px; font-weight:600; transition:.2s; }
        .btn-edit    { background:var(--gold-soft); color:var(--gold); }
        .btn-edit:hover { background:var(--gold); color:#fff; }
        .btn-danger  { background:rgba(220,38,38,.12); color:#dc2626; }
        .btn-danger:hover { background:#dc2626; color:#fff; }
        .btn-success { background:rgba(22,163,74,.12); color:#16a34a; }
        .btn-success:hover { background:#16a34a; color:#fff; }
        .avatar-sm { width:34px; height:34px; border-radius:50%; background:var(--gold-soft);
                     color:var(--gold); font-weight:700; display:flex; align-items:center;
                     justify-content:center; font-size:13px; flex-shrink:0; }
        .stat-mini { 
            display:flex; align-items:center; gap:16px; 
            background:linear-gradient(145deg, #ffffff, #fafafa);
            padding:20px 24px; border-radius:16px; 
            border:1px solid rgba(0,0,0,0.03); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-mini:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }
        .stat-mini-icon { 
            width:48px; height:48px; border-radius:14px; 
            display:flex; align-items:center; justify-content:center; 
        }
        .stat-mini-icon i { font-size:22px; }
        .stat-mini-val  { font-size:28px; font-weight:800; color:#111827; line-height:1; letter-spacing:-0.5px; }
        .stat-mini-lbl  { font-size:11px; color:#6b7280; margin-top:6px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="margin:0">Barberos</h1>
                <p style="margin:4px 0 0; color:var(--text-muted)">Gestión del equipo de barberos</p>
            </div>
            <button class="btn btn-gold" onclick="abrirModalCrear()" id="btnNuevoBarbero">
                <i class="bi bi-plus-lg"></i> Nuevo Barbero
            </button>
        </div>

        <!-- KPI Mini cards -->
        <div class="grid-3" style="margin-bottom:20px;">
            <?php
            $activos    = count(array_filter($barberos, fn($b) => $b['estado'] === 'ACTIVO'));
            $inactivos  = count(array_filter($barberos, fn($b) => $b['estado'] === 'INACTIVO'));
            $suspendidos= count(array_filter($barberos, fn($b) => $b['estado'] === 'SUSPENDIDO'));
            ?>
            <div class="stat-mini" onmouseover="this.style.borderColor='rgba(22,163,74,0.3)'; this.style.boxShadow='0 12px 24px rgba(22,163,74,0.15)'" onmouseout="this.style.borderColor='rgba(0,0,0,0.03)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.03)'">
                <div class="stat-mini-icon" style="background:linear-gradient(135deg, rgba(22,163,74,0.1), rgba(22,163,74,0.2))">
                    <i class="bi bi-scissors" style="color:#16a34a"></i>
                </div>
                <div>
                    <div class="stat-mini-val"><?= $activos ?></div>
                    <div class="stat-mini-lbl">Activos</div>
                </div>
            </div>
            <div class="stat-mini" onmouseover="this.style.borderColor='rgba(220,38,38,0.3)'; this.style.boxShadow='0 12px 24px rgba(220,38,38,0.15)'" onmouseout="this.style.borderColor='rgba(0,0,0,0.03)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.03)'">
                <div class="stat-mini-icon" style="background:linear-gradient(135deg, rgba(220,38,38,0.1), rgba(220,38,38,0.2))">
                    <i class="bi bi-person-dash" style="color:#dc2626"></i>
                </div>
                <div>
                    <div class="stat-mini-val"><?= $inactivos ?></div>
                    <div class="stat-mini-lbl">Inactivos</div>
                </div>
            </div>
            <div class="stat-mini" onmouseover="this.style.borderColor='rgba(217,119,6,0.3)'; this.style.boxShadow='0 12px 24px rgba(217,119,6,0.15)'" onmouseout="this.style.borderColor='rgba(0,0,0,0.03)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.03)'">
                <div class="stat-mini-icon" style="background:linear-gradient(135deg, rgba(217,119,6,0.1), rgba(217,119,6,0.2))">
                    <i class="bi bi-slash-circle" style="color:#d97706"></i>
                </div>
                <div>
                    <div class="stat-mini-val"><?= $suspendidos ?></div>
                    <div class="stat-mini-lbl">Suspendidos</div>
                </div>
            </div>
        </div>

        <!-- Tabla de barberos -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-scissors me-2" style="color:var(--gold)"></i>Equipo de barberos</h3>
                <span style="font-size:12px; color:var(--text-muted)"><?= count($barberos) ?> registros</span>
            </div>
            <div class="content-card-body p-0" style="overflow-x:auto">
                <?php if (empty($barberos)): ?>
                    <div style="padding:50px; text-align:center; color:var(--text-muted)">
                        <i class="bi bi-scissors" style="font-size:40px; opacity:.3"></i>
                        <p style="margin-top:12px">No hay barberos registrados</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Barbero</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Citas totales</th>
                                <th>Completadas</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barberos as $b): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="avatar-sm"><?= strtoupper(mb_substr($b['nombre'],0,1)) ?></div>
                                        <div>
                                            <div style="font-weight:600"><?= htmlspecialchars($b['nombre']) ?></div>
                                            <small style="color:var(--text-muted)"><?= htmlspecialchars($b['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($b['telefono'] ?? 'â€”') ?></td>
                                <td>
                                    <span class="badge-estado bg-<?= strtolower($b['estado']) ?>">
                                        <?= $b['estado'] ?>
                                    </span>
                                </td>
                                <td style="font-weight:600; color:var(--gold)"><?= (int)$b['total_citas'] ?></td>
                                <td><?= (int)$b['citas_completadas'] ?></td>
                                <td style="color:var(--text-muted); font-size:12px"><?= date('d/m/Y', strtotime($b['fecha_registro'])) ?></td>
                                <td>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <button class="btn-sm btn-edit"
                                            onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($b)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($b['estado'] === 'ACTIVO'): ?>
                                        <button class="btn-sm btn-danger"
                                            onclick="cambiarEstado(<?= $b['id_usuario'] ?>, 'SUSPENDIDO')">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn-sm btn-success"
                                            onclick="cambiarEstado(<?= $b['id_usuario'] ?>, 'ACTIVO')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
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

<!-- Modal Crear Barbero -->
<div class="modal-overlay" id="modalCrear">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-header">
            <h4 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Barbero</h4>
            <button class="modal-close" onclick="cerrarModales()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="content-card-body">
                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Carlos Méndez">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@ejemplo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" class="form-control" placeholder="+57 300 000 0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña temporal *</label>
                    <input type="password" name="password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres">
                </div>
            </div>
            <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end; padding:16px 20px; border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn btn-gold">Crear Barbero</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Barbero -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-header">
            <h4 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar Barbero</h4>
            <button class="modal-close" onclick="cerrarModales()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="editId">
            <div class="content-card-body">
                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="nombre" id="editNombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="editTelefono" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="editEstado" class="form-control">
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="SUSPENDIDO">Suspendido</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end; padding:16px 20px; border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn btn-gold">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto cambiar estado -->
<form method="POST" id="formEstado" style="display:none">
    <input type="hidden" name="accion" value="cambiar_estado">
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
function abrirModalCrear() {
    document.getElementById('modalCrear').classList.add('open');
}
function abrirModalEditar(b) {
    document.getElementById('editId').value       = b.id_usuario;
    document.getElementById('editNombre').value   = b.nombre;
    document.getElementById('editTelefono').value = b.telefono || '';
    document.getElementById('editEstado').value   = b.estado;
    document.getElementById('modalEditar').classList.add('open');
}
function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
function cambiarEstado(id, estado) {
    const labels = { ACTIVO: 'activar', SUSPENDIDO: 'suspender', INACTIVO: 'desactivar' };
    Swal.fire({
        title: '¿Confirmar?',
        text: `¿Deseas ${labels[estado]} este barbero?`,
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

// Cerrar modal clickando overlay
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) cerrarModales(); });
});

// Flash SweetAlert
<?php if ($flash): ?>
Swal.fire({
    icon: '<?= $flash['ok'] ? 'success' : 'error' ?>',
    title: '<?= $flash['ok'] ? '¡Listo!' : 'Error' ?>',
    text: '<?= addslashes($flash['msg']) ?>',
    confirmButtonColor: '#b58a4a'
});
<?php endif; ?>

// Session expired
const p = new URLSearchParams(window.location.search);
if (p.get('expired') === '1') {
    Swal.fire({ icon:'warning', title:'Sesión expirada', text:'Tu sesión cerró por inactividad.', confirmButtonColor:'#b58a4a' });
}
</script>
</body>
</html>

