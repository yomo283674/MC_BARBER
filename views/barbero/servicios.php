<?php
/**
 * views/barbero/servicios.php
 * Gestión de servicios propios del barbero.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'controllers/barbero/serviciosController.php';

$servicios = getserviciosParaBarbero();

$pagina_activa = 'servicios';
$titulo_pagina = 'Mis Servicios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Servicios - Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .service-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(229, 231, 235, 0.6);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.02);
        }
        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -5px rgba(0,0,0,0.08), 0 10px 15px -6px rgba(0,0,0,0.04);
            border-color: rgba(229, 231, 235, 0.9);
        }
        .service-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            transition: transform 0.5s ease;
        }
        .service-card:hover .service-img {
            transform: scale(1.05);
        }
        .service-img-wrapper {
            overflow: hidden;
            width: 100%;
            height: 200px;
        }
        .service-content { padding: 24px; flex: 1; display: flex; flex-direction: column; position: relative; z-index: 1; background: #ffffff; }
        .service-actions { display: flex; gap: 12px; margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(229, 231, 235, 0.6); }
        
        .btn-card-edit, .btn-card-del {
            flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px; font-size: 13.5px; font-weight: 700; border-radius: 12px;
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-card-edit { background: #f3f4f6; color: #374151; }
        .btn-card-edit:hover { background: #e5e7eb; color: #111827; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        .btn-card-del { background: #fef2f2; color: #dc2626; }
        .btn-card-del:hover { background: #fee2e2; color: #b91c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,38,38,0.1); }

        /* Modal estilos básicos */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(17, 24, 39, 0.6); align-items: center; justify-content: center; backdrop-filter: blur(8px); opacity: 0; transition: opacity 0.3s ease; }
        .modal.show { display: flex; opacity: 1; }
        .modal-content { background: var(--white); padding: 36px; border-radius: 24px; width: 100%; max-width: 520px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); transform: translateY(20px); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .modal.show .modal-content { transform: translateY(0); }
        .close-btn { position: absolute; right: 24px; top: 24px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f3f4f6; font-size: 20px; cursor: pointer; color: #6b7280; transition: all 0.2s; }
        .close-btn:hover { background: #e5e7eb; color: #111827; transform: rotate(90deg); }
        
        /* Premium Empty State */
        .premium-empty {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 80px 20px; text-align: center; background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.8) 100%);
            border-radius: 24px; border: 1px dashed rgba(181, 138, 74, 0.3); margin-top: 20px;
        }
        .premium-empty-icon {
            width: 80px; height: 80px; background: radial-gradient(circle, rgba(181,138,74,0.15) 0%, transparent 70%);
            display: flex; align-items: center; justify-content: center; border-radius: 50%;
            color: var(--gold); font-size: 36px; margin-bottom: 24px;
            box-shadow: 0 0 0 1px rgba(181, 138, 74, 0.1);
        }
        .premium-empty h3 { font-size: 22px; font-weight: 800; color: #111827; margin-bottom: 8px; letter-spacing: -0.02em; }
        .premium-empty p { font-size: 15px; color: #6b7280; max-width: 400px; line-height: 1.6; margin-bottom: 32px; }

        /* Premium Modal Forms */
        .modal-content .form-label {
            font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;
        }
        .modal-content .form-control {
            background: #f9fafb !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 12px !important;
            padding: 14px 16px !important;
            font-size: 14.5px !important;
            color: #111827 !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        .modal-content .form-control:focus {
            background: #ffffff !important;
            border-color: var(--gold) !important;
            box-shadow: 0 0 0 4px rgba(181, 138, 74, 0.15) !important;
        }
        /* Override browser autofill blue background */
        .modal-content .form-control:-webkit-autofill,
        .modal-content .form-control:-webkit-autofill:hover, 
        .modal-content .form-control:-webkit-autofill:focus, 
        .modal-content .form-control:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #f9fafb inset !important;
            -webkit-text-fill-color: #111827 !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Mobile adjustments for Modal */
        @media (max-width: 640px) {
            .modal-content { padding: 24px 16px; width: calc(100% - 32px); }
            .close-btn { right: 12px; top: 12px; }
        }
    </style>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'views/layouts/sidebar_barbero.php'; ?>

<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <h1 class="topbar-title">Mis Servicios</h1>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Hola<strong><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]) ?></strong></span>
        </div>
    </header>

    <div class="page-content">
        <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:32px">
            <div>
                <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
                    Mis Servicios <i class="bi bi-scissors" style="color:var(--gold);font-size:24px;transform:rotate(-45deg)"></i>
                </h1>
                <p style="font-size:15px;color:var(--text-light);margin-top:6px">Crea y administra los servicios que ofreces a los clientes.</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModal()" style="height:44px;padding:0 24px;border-radius:12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;box-shadow:0 6px 16px rgba(181,138,74,0.25);transition:all 0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(181,138,74,0.35)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 6px 16px rgba(181,138,74,0.25)'">
                <i class="bi bi-plus-lg" style="font-size:16px"></i> Nuevo Servicio
            </button>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin-bottom:20px;padding:12px;background:#dcfce7;color:#166534;border-radius:8px;">
                <i class="bi bi-check-circle"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error" style="margin-bottom:20px;padding:12px;background:#fee2e2;color:#991b1b;border-radius:8px;">
                <i class="bi bi-x-circle"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($servicios)): ?>
            <div class="premium-empty">
                <div class="premium-empty-icon">
                    <i class="bi bi-scissors" style="transform:rotate(-45deg)"></i>
                </div>
                <h3>Aún no tienes servicios</h3>
                <p>Parece que tu catálogo está vacío. Crea tu primer servicio para que los clientes puedan empezar a agendar citas contigo.</p>
                <button class="btn btn-primary" onclick="abrirModal()" style="height:48px;padding:0 28px;border-radius:24px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;box-shadow:0 8px 24px rgba(181,138,74,0.3);transition:all 0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(181,138,74,0.3)'">
                    <i class="bi bi-plus-lg"></i> Crear mi primer servicio
                </button>
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px">
                <?php foreach ($servicios as $s): ?>
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <?php if ($s['imagen']): ?>
                            <img src="<?= $base_path ?>public/uploads/servicios/<?= htmlspecialchars($s['imagen']) ?>" class="service-img" alt="Imagen servicio">
                        <?php else: ?>
                            <div class="service-img" style="display:flex;align-items:center;justify-content:center;font-size:48px;color:#d1d5db">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="service-content">
                        <div style="font-size:18px;font-weight:800;color:#111827;letter-spacing:-0.01em;margin-bottom:8px;line-height:1.2"><?= htmlspecialchars($s['nombre']) ?></div>
                        <div style="font-size:14px;color:#6b7280;line-height:1.6;margin-bottom:20px;height:44px;overflow:hidden;position:relative">
                            <?= htmlspecialchars(mb_strimwidth($s['descripcion'] ?? '', 0, 90, '...')) ?>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                            <div style="font-size:24px;font-weight:800;background:linear-gradient(135deg, var(--gold-hover), var(--gold-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent">
                                $<?= number_format($s['precio'], 0, ',', '.') ?>
                            </div>
                            <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(181,138,74,0.1);color:var(--gold-hover);font-size:12.5px;font-weight:800;padding:6px 14px;border-radius:20px">
                                <i class="bi bi-clock-fill" style="font-size:11px"></i> <?= $s['duracion_min'] ?> min
                            </div>
                        </div>
                        <div class="service-actions">
                            <button class="btn-card-edit" onclick='editarServicio(<?= json_encode($s) ?>)'>
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                            <button class="btn-card-del" onclick="eliminarServicio(<?= $s['id_servicio'] ?>)">
                                <i class="bi bi-trash3-fill"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Modal Crear/Editar -->
        <div class="modal" id="modalServicio">
            <div class="modal-content">
                <i class="bi bi-x close-btn" onclick="cerrarModal()"></i>
                <h2 id="modalTitle" style="margin-bottom:24px;font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">Crear Servicio</h2>
                <form action="<?= $base_path ?>controllers/barbero/serviciosController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" id="accionServicio" value="crear">
                    <input type="hidden" name="id_servicio" id="id_servicio" value="">
                    
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Nombre del Servicio *</label>
                        <input type="text" name="nombre" id="nombreServicio" class="form-control" required placeholder="Ej: Corte Clásico">
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="descServicio" class="form-control" rows="3" placeholder="Detalles del servicio..."></textarea>
                    </div>

                    <div style="display:flex;gap:16px;margin-bottom:16px">
                        <div class="form-group" style="flex:1">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" name="precio" id="precioServicio" class="form-control" required min="1000" step="100">
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="form-label">Duración (min) *</label>
                            <input type="number" name="duracion_min" id="duracionServicio" class="form-control" required min="10" step="5" value="30">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:24px">
                        <label class="form-label">Imagen <span id="imgOpcional">(Opcional)</span></label>
                        
                        <div id="imagePreviewContainer" style="display:none; margin-bottom:12px; position:relative;">
                            <img id="imagePreview" src="" style="width:100%; height:160px; object-fit:cover; border-radius:8px; border:1px solid var(--border);" alt="Vista previa">
                            <button type="button" onclick="limpiarImagen()" style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.6); color:white; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Quitar imagen"><i class="bi bi-x"></i></button>
                        </div>

                        <input type="file" name="imagen" id="inputImagen" class="form-control" accept="image/jpeg, image/png, image/webp" onchange="previewImage(this)">
                        <small style="color:var(--text-muted);font-size:11px" id="imagenHint">JPG, PNG o WEBP. Recomendado: 800x600px.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; height:52px; border-radius:14px; font-weight:800; font-size:15.5px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 8px 24px rgba(181,138,74,0.3); transition:all 0.3s; margin-top:12px; letter-spacing:0.02em;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(181,138,74,0.3)'">
                        <i class="bi bi-check2-circle" style="font-size:20px;"></i> Guardar Servicio
                    </button>
                </form>
            </div>
        </div>

        <!-- Form oculto para eliminar -->
        <form id="formEliminar" action="<?= $base_path ?>controllers/barbero/serviciosController.php" method="POST" style="display:none">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id_servicio" id="idEliminar">
        </form>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function abrirModal() {
    document.getElementById('modalTitle').innerText = 'Crear Servicio';
    document.getElementById('accionServicio').value = 'crear';
    document.getElementById('id_servicio').value = '';
    document.getElementById('nombreServicio').value = '';
    document.getElementById('descServicio').value = '';
    document.getElementById('precioServicio').value = '';
    document.getElementById('duracionServicio').value = '30';
    document.getElementById('imgOpcional').innerText = '(Opcional)';
    limpiarImagen();
    document.getElementById('modalServicio').classList.add('show');
}

function editarServicio(s) {
    document.getElementById('modalTitle').innerText = 'Editar Servicio';
    document.getElementById('accionServicio').value = 'actualizar';
    document.getElementById('id_servicio').value = s.id_servicio;
    document.getElementById('nombreServicio').value = s.nombre;
    document.getElementById('descServicio').value = s.descripcion;
    document.getElementById('precioServicio').value = s.precio;
    document.getElementById('duracionServicio').value = s.duracion_min;
    document.getElementById('imgOpcional').innerText = '(Opcional, dejar vacío para no cambiar)';
    limpiarImagen();
    document.getElementById('modalServicio').classList.add('show');
}

function cerrarModal() {
    document.getElementById('modalServicio').classList.remove('show');
}

function eliminarServicio(id) {
    Swal.fire({
        title: '¿Eliminar servicio?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('idEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    });
}

function previewImage(input) {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const hint = document.getElementById('imagenHint');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
            input.style.display = 'none';
            hint.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function limpiarImagen() {
    const input = document.getElementById('inputImagen');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const hint = document.getElementById('imagenHint');
    
    input.value = '';
    previewImg.src = '';
    previewContainer.style.display = 'none';
    input.style.display = 'block';
    hint.style.display = 'block';
}
</script>
</body>
</html>

