<?php
/**
 * views/cliente/perfil.php
 * Vista de perfil del cliente.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['CLIENTE'], $base_path);
require_once $base_path . 'models/Usuario.php';

$id_usuario = (int)$_SESSION['usuario_id'];
$usuarioModel = new Usuario();
$datos_usuario = $usuarioModel->obtenerPorId($id_usuario);

// Flash
$flash_tipo = $_SESSION['flash_tipo'] ?? '';
$flash_msg  = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_msg']);

$pagina_activa = 'perfil';
$titulo_pagina = 'Configuración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'includes/nav_cliente.php'; ?>    <div class="page-content">
        <?php if ($flash_msg): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?= $flash_tipo ?>',
                        title: '<?= $flash_tipo === 'success' ? '¡Actualizado!' : 'Atención' ?>',
                        text: '<?= addslashes($flash_msg) ?>',
                        confirmButtonColor: '#b58a4a',
                        timer: 3500,
                        timerProgressBar: true
                    });
                });
            </script>
        <?php endif; ?>

        <div style="max-width: 700px; margin: 0 auto;">
            <div class="page-header" style="text-align: center; margin-bottom: 40px;">
                <h1 style="font-size:28px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:12px; letter-spacing:-0.02em; color:#111827">
                    Configuración de Cuenta
                </h1>
                <p style="color:var(--text-muted); margin-top:8px; font-size:15px;">Gestiona tu información personal y seguridad.</p>
            </div>

            <div class="content-card" style="box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04);">
                <div class="content-card-body" style="padding: 40px;">
                    <form action="<?= $base_path ?>controllers/cliente/perfilController.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- SECCIÓN FOTO PERFIL -->
                        <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 40px;">
                            <label style="cursor: pointer; position: relative; border-radius: 50%; display: block;" class="avatar-upload">
                                <div style="position: relative; width: 130px; height: 130px; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(181,138,74,0.15); background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                    <?php if (!empty($datos_usuario['foto_perfil'])): ?>
                                        <img src="<?= $base_path ?>public/uploads/perfiles/<?= htmlspecialchars($datos_usuario['foto_perfil']) ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; transition: filter 0.3s;" id="preview-img">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill" style="font-size: 70px; color: #cbd5e1;" id="preview-icon"></i>
                                        <img src="" alt="" style="display:none; width: 100%; height: 100%; object-fit: cover;" id="preview-img">
                                    <?php endif; ?>
                                    
                                    <div class="upload-overlay" style="position: absolute; inset: 0; background: rgba(17, 24, 39, 0.6); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; opacity: 0; transition: opacity 0.3s ease;">
                                        <i class="bi bi-camera" style="font-size: 26px; margin-bottom: 4px;"></i>
                                        <span style="font-size: 13px; font-weight: 600;">Cambiar</span>
                                    </div>
                                </div>
                                <input type="file" name="foto_perfil" accept="image/jpeg, image/png, image/webp" style="display: none;" onchange="previewImage(this)">
                            </label>
                            
                            <style>
                                .avatar-upload:hover .upload-overlay { opacity: 1 !important; }
                                .avatar-upload:hover img { filter: blur(2px); }
                                .avatar-upload:active > div { transform: scale(0.95); }
                            </style>

                            <script>
                                function previewImage(input) {
                                    if (input.files && input.files[0]) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const img = document.getElementById('preview-img');
                                            const icon = document.getElementById('preview-icon');
                                            if (icon) icon.style.display = 'none';
                                            img.src = e.target.result;
                                            img.style.display = 'block';
                                        }
                                        reader.readAsDataURL(input.files[0]);
                                    }
                                }
                            </script>
                        </div>

                        <!-- SECCIÓN DATOS PERSONALES -->
                        <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">
                            <i class="bi bi-person-lines-fill" style="color:var(--gold);"></i> Datos Personales
                        </h3>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label class="form-label">Nombre Completo</label>
                            <div class="input-wrapper">
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos_usuario['nombre']) ?>" required>
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        
                        <div class="form-row" style="margin-bottom: 32px;">
                            <div class="form-group mb-0">
                                <label class="form-label">Correo Electrónico</label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($datos_usuario['email']) ?>" required>
                                    <i class="bi bi-envelope"></i>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Teléfono</label>
                                <div class="input-wrapper">
                                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($datos_usuario['telefono']) ?>" required>
                                    <i class="bi bi-telephone"></i>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN SEGURIDAD -->
                        <div style="border-top: 1px solid var(--border); padding-top: 32px; margin-top: 16px;">
                            <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">
                                <i class="bi bi-shield-lock" style="color:var(--gold);"></i> Seguridad
                            </h3>
                            <div class="form-group">
                                <label class="form-label">Nueva Contraseña</label>
                                <div class="input-wrapper">
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                                    <i class="bi bi-key"></i>
                                </div>
                                <span class="form-hint" style="display:block; margin-top:8px; font-size:12.5px; color:#6b7280;">Dejar en blanco para mantener la contraseña actual.</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                            <button type="submit" class="btn btn-primary btn-lg" style="min-width: 200px; justify-content: center;">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> <!-- Cerrar page-content -->
</div> <!-- Cerrar main-content -->

</body>
</html>
