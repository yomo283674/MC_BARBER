<?php
/**
 * views/admin/servicios.php
 * Gestión de servicios â€” Dashboard Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);
require_once PROFUNDIDAD . 'controllers/admin/serviciosAdminController.php';
global $conn;

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl   = new ServiciosAdminController($conn);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $imagenPath = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'srv_' . time() . '.' . $ext;
                $dest = PROFUNDIDAD . 'public/uploads/servicios/' . $filename;
                if (!is_dir(PROFUNDIDAD . 'public/uploads/servicios/')) mkdir(PROFUNDIDAD . 'public/uploads/servicios/', 0777, true);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                    $imagenPath = $filename;
                }
            }
        }

        $flash = $ctrl->crear(
            trim($_POST['nombre']      ?? ''),
            trim($_POST['descripcion'] ?? ''),
            (float)($_POST['precio']   ?? 0),
            (int)($_POST['duracion']   ?? 30),
            $imagenPath
        );
    } elseif ($accion === 'editar') {
        $imagenPath = trim($_POST['imagen_actual'] ?? '');
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'srv_' . time() . '.' . $ext;
                $dest = PROFUNDIDAD . 'public/uploads/servicios/' . $filename;
                if (!is_dir(PROFUNDIDAD . 'public/uploads/servicios/')) mkdir(PROFUNDIDAD . 'public/uploads/servicios/', 0777, true);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                    $imagenPath = $filename;
                }
            }
        }

        $flash = $ctrl->actualizar(
            (int)$_POST['id'],
            trim($_POST['nombre']      ?? ''),
            trim($_POST['descripcion'] ?? ''),
            (float)($_POST['precio']   ?? 0),
            (int)($_POST['duracion']   ?? 30),
            $imagenPath,
            trim($_POST['estado']      ?? 'ACTIVO')
        );
    } elseif ($accion === 'desactivar') {
        $flash = $ctrl->desactivar((int)$_POST['id']);
    }
}

$ctrl     = new ServiciosAdminController($conn);
$servicios= $ctrl->listar();

$pagina_activa = 'servicios';
$titulo_pagina = 'Gestión de Servicios';
$base_path     = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios Administrador | MC Barber</title>
    <meta name="description" content="Gestión de servicios ofrecidos por MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-estado  { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .bg-activo     { background:rgba(22,163,74,.15); color:#16a34a; }
        .bg-inactivo   { background:rgba(220,38,38,.15); color:#dc2626; }
        .service-card {
            background: linear-gradient(145deg, #ffffff, #fafafa);
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .service-card:hover {
            box-shadow: 0 12px 24px rgba(181, 138, 74, 0.12);
            transform: translateY(-4px);
            border-color: rgba(181, 138, 74, 0.2);
        }
        .service-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(181,138,74,0.1), rgba(181,138,74,0.2));
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }
        .service-icon i {
            color: var(--gold);
            font-size: 22px;
        }
        .service-price {
            font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -0.5px;
        }
        .service-dur {
            font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;
        }
        .service-actions {
            display: flex; gap: 8px; margin-top: 20px;
        }
        .btn-sm {
            padding: 8px 12px; border-radius: 10px; border: none; cursor: pointer;
            font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.2s ease; flex: 1;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-edit { background: rgba(181,138,74,0.1); color: var(--gold); }
        .btn-edit:hover { background: var(--gold); color: #fff; box-shadow: 0 4px 12px rgba(181,138,74,0.3); }
        .btn-danger { background: rgba(220,38,38,0.1); color: #dc2626; }
        .btn-danger:hover { background: #dc2626; color: #fff; box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
        .services-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

        <!-- Page Header -->
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="margin:0">Servicios</h1>
                <p style="margin:4px 0 0; color:var(--text-muted)">Catálogo de servicios de la barbería</p>
            </div>
            <button class="btn btn-gold" onclick="document.getElementById('modalCrear').classList.add('open')" id="btnNuevoServicio">
                <i class="bi bi-plus-lg"></i> Nuevo Servicio
            </button>
        </div>

        <!-- Stats rápidas -->
        <?php
        $activos   = count(array_filter($servicios, fn($s) => $s['estado'] === 'ACTIVO'));
        $inactivos = count(array_filter($servicios, fn($s) => $s['estado'] === 'INACTIVO'));
        $precio_min= $activos > 0 ? min(array_column(array_filter($servicios, fn($s) => $s['estado']==='ACTIVO'), 'precio')) : 0;
        $precio_max= $activos > 0 ? max(array_column(array_filter($servicios, fn($s) => $s['estado']==='ACTIVO'), 'precio')) : 0;
        ?>
        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-tag-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= count($servicios) ?></div>
                    <div class="stat-label">Total servicios</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $activos ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-value">$<?= number_format($precio_min,0,',','.') ?></div>
                    <div class="stat-label">Precio mínimo</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-value">$<?= number_format($precio_max,0,',','.') ?></div>
                    <div class="stat-label">Precio máximo</div>
                </div>
            </div>
        </div>

        <!-- Grid de servicios -->
        <?php if (empty($servicios)): ?>
        <div class="content-card">
            <div class="content-card-body" style="text-align:center; padding:50px">
                <i class="bi bi-tag" style="font-size:48px; opacity:.2; color:var(--gold)"></i>
                <p style="margin-top:16px; color:var(--text-muted)">No hay servicios registrados. Crea el primero.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="services-grid">
            <?php foreach ($servicios as $sv): ?>
            <div class="service-card" style="<?= $sv['estado'] === 'INACTIVO' ? 'opacity:.65' : '' ?>">
                <div class="service-icon" style="<?= !empty($sv['imagen']) ? 'background: url(' . $base_path . 'public/uploads/servicios/' . htmlspecialchars($sv['imagen']) . ') center/cover no-repeat;' : '' ?>">
                    <?php if (empty($sv['imagen'])): ?>
                        <i class="bi bi-scissors"></i>
                    <?php endif; ?>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px">
                    <div style="font-weight:700; font-size:15px"><?= htmlspecialchars($sv['nombre']) ?></div>
                    <span class="badge-estado bg-<?= strtolower($sv['estado']) ?>"><?= $sv['estado'] ?></span>
                </div>
                <p style="font-size:12px; color:var(--text-muted); margin:0 0 10px; line-height:1.5">
                    <?= htmlspecialchars($sv['descripcion'] ?: 'Sin descripción') ?>
                </p>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="service-price">$<?= number_format($sv['precio'],0,',','.') ?></span>
                    <div style="text-align:right">
                        <div class="service-dur"><i class="bi bi-clock"></i> <?= $sv['duracion_minutos'] ?? $sv['duracion'] ?? 30 ?> min</div>
                        <div style="font-size:11.5px; font-weight:600; color:#9ca3af; letter-spacing:0.5px;"><i class="bi bi-calendar2-check"></i> <?= $sv['total_citas'] ?> CITAS</div>
                    </div>
                </div>
                <div class="service-actions">
                    <button class="btn-sm btn-edit"
                        onclick="abrirEditar(<?= htmlspecialchars(json_encode($sv)) ?>)">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <?php if ($sv['estado'] === 'ACTIVO'): ?>
                    <button class="btn-sm btn-danger"
                        onclick="desactivar(<?= $sv['id_servicio'] ?>, '<?= htmlspecialchars($sv['nombre']) ?>')">
                        <i class="bi bi-eye-slash"></i> Desactivar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /page-content -->
</div><!-- /main-content -->

<!-- Modal Crear -->
<div class="modal-overlay" id="modalCrear">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-header">
            <h4 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Servicio</h4>
            <button class="modal-close" onclick="cerrarModales()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="crear">
            <div class="content-card-body">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Corte clásico">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción del servicio…"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" name="precio" class="form-control" required min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (min) *</label>
                        <input type="number" name="duracion" class="form-control" required min="5" step="5" value="30" placeholder="30">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end; padding:16px 20px; border-top:1px solid var(--border);">
                <button type="button" class="btn btn-outline" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn btn-gold">Crear Servicio</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-header">
            <h4 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar Servicio</h4>
            <button class="modal-close" onclick="cerrarModales()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="imagen_actual" id="editImagenActual">
            <div class="content-card-body">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="editNombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="editDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Deja vacío para conservar la imagen actual.</small>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" name="precio" id="editPrecio" class="form-control" required min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (min) *</label>
                        <input type="number" name="duracion" id="editDuracion" class="form-control" required min="5" step="5">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="editEstado" class="form-control">
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
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

<!-- Form desactivar -->
<form method="POST" id="formDesactivar" style="display:none">
    <input type="hidden" name="accion" value="desactivar">
    <input type="hidden" name="id" id="desactivarId">
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
function abrirEditar(sv) {
    document.getElementById('editId').value      = sv.id_servicio;
    document.getElementById('editNombre').value  = sv.nombre;
    document.getElementById('editDesc').value    = sv.descripcion || '';
    document.getElementById('editPrecio').value  = sv.precio;
    document.getElementById('editDuracion').value= sv.duracion_minutos;
    document.getElementById('editEstado').value  = sv.estado;
    document.getElementById('editImagenActual').value = sv.imagen || '';
    document.getElementById('modalEditar').classList.add('open');
}
function desactivar(id, nombre) {
    Swal.fire({
        title: '¿Desactivar servicio?',
        text: `"${nombre}" no estará disponible para nuevas citas.`,
        icon: 'warning',
        showCancelButton: true,
        buttonsStyling: false,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, desactivar',
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: 'swal-btn swal-btn-danger',
            cancelButton: 'swal-btn swal-btn-secondary',
            actions: 'swal-actions-right'
        }
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('desactivarId').value = id;
            document.getElementById('formDesactivar').submit();
        }
    });
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) cerrarModales(); });
});
<?php if ($flash): ?>
Swal.fire({
    icon: '<?= $flash['ok'] ? 'success' : 'error' ?>',
    title: '<?= $flash['ok'] ? '¡Listo!' : 'Error' ?>',
    text: '<?= addslashes($flash['msg']) ?>',
    buttonsStyling: false,
    customClass: {
        popup: 'swal-ultra-modern',
        confirmButton: 'swal-btn swal-btn-primary'
    }
});
<?php endif; ?>
const p = new URLSearchParams(window.location.search);
if (p.get('expired') === '1') {
    Swal.fire({ 
        icon:'warning', 
        title:'Sesión expirada', 
        text:'Tu sesión cerró por inactividad.', 
        buttonsStyling: false,
        customClass: {
            popup: 'swal-ultra-modern',
            confirmButton: 'swal-btn swal-btn-primary'
        }
    });
}
</script>
</body>
</html>

