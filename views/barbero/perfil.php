<?php
/**
 * views/barbero/perfil.php
 * Perfil editable del barbero: nombre, email, teléfono, especialidad y contraseña.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'config/database.php';

$id_barbero = (int)$_SESSION['usuario_id'];

// Cargar datos actuales
$stmt = $conn->prepare("SELECT nombre, email, telefono, especialidad, foto_perfil FROM usuarios WHERE id_usuario = ? LIMIT 1");
$stmt->bind_param('i', $id_barbero);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Flash
$flash_ok  = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flash_err = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

$pagina_activa = 'perfil';
$titulo_pagina = 'Mi Perfil';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        /* Premium Forms overrides */
        .form-label {
            font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;
        }
        .form-control {
            background: #f9fafb !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 12px !important;
            padding: 14px 16px !important;
            font-size: 14.5px !important;
            color: #111827 !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        .input-icon > i { left: 16px; font-size: 18px; color: #9ca3af; }
        .form-control.with-icon { padding-left: 44px !important; }
        
        .form-control:focus {
            background: #ffffff !important;
            border-color: var(--gold) !important;
            box-shadow: 0 0 0 4px rgba(181, 138, 74, 0.15) !important;
        }
        .form-control:-webkit-autofill,
        .form-control:-webkit-autofill:hover, 
        .form-control:-webkit-autofill:focus, 
        .form-control:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #f9fafb inset !important;
            -webkit-text-fill-color: #111827 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        /* Custom File Input */
        input[type="file"].form-control {
            padding: 10px 16px !important;
        }
        input[type="file"]::file-selector-button {
            border: 1px solid var(--border);
            background: var(--white);
            padding: 8px 16px;
            border-radius: 8px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 13px;
            margin-right: 16px;
        }
        input[type="file"]::file-selector-button:hover {
            background: var(--background);
            border-color: var(--gold);
            color: var(--gold);
        }
    </style>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'includes/nav_barbero.php'; ?>

<?php if ($flash_ok): ?>
    <div class="alert-flash alert-flash-ok"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($flash_ok) ?></div>
<?php endif; ?>
<?php if ($flash_err): ?>
    <div class="alert-flash alert-flash-err"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($flash_err) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
        Mi Perfil <i class="bi bi-person-badge" style="color:var(--gold);font-size:24px;"></i>
    </h1>
    <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Actualiza tus datos personales y contraseña.</p>
</div>

<div class="grid-2">
    <!-- Datos personales -->
    <div class="content-card" style="border-top: 4px solid var(--gold); background: linear-gradient(to bottom, #ffffff, #fcfcfc);">
        <div class="content-card-header" style="border-bottom: none; padding-bottom: 0;">
            <h3 style="font-size: 18px; display: flex; align-items: center;"><i class="bi bi-person-bounding-box" style="color:var(--gold);margin-right:10px; font-size: 22px;"></i>Datos personales</h3>
        </div>
        <div class="content-card-body">
            <!-- Avatar -->
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:32px;gap:8px;padding:32px 24px;background:linear-gradient(180deg, rgba(181,138,74,0.03) 0%, rgba(255,255,255,0) 100%);border-radius:20px;border:1px dashed rgba(181,138,74,0.3);">
                <?php if (!empty($usuario['foto_perfil'])): ?>
                    <div style="width:110px;height:110px;border-radius:50%;
                                background-image:url('<?= $base_path ?>public/uploads/perfiles/<?= htmlspecialchars($usuario['foto_perfil']) ?>');
                                background-size:cover;background-position:center;
                                box-shadow:0 0 0 4px #ffffff, 0 0 0 8px var(--gold-soft);
                                transition:all 0.3s; margin-bottom:12px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                <?php else: ?>
                    <div style="width:110px;height:110px;border-radius:50%;
                                background:linear-gradient(135deg, rgba(181,138,74,0.15) 0%, rgba(181,138,74,0.05) 100%);
                                display:flex;align-items:center;justify-content:center;
                                font-size:42px;font-weight:800;color:var(--gold);
                                box-shadow:0 0 0 4px #ffffff, 0 0 0 8px var(--gold-soft);
                                transition:all 0.3s; margin-bottom:12px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <?= strtoupper(mb_substr($usuario['nombre'],0,1)) ?>
                    </div>
                <?php endif; ?>
                <div style="font-size:11px;color:var(--gold-light);font-weight:800;text-transform:uppercase;letter-spacing:0.1em;background:rgba(181,138,74,0.15);padding:4px 12px;border-radius:8px;">BARBERO</div>
            </div>

            <form method="POST" action="<?= $base_path ?>controllers/barbero/perfilController.php" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="actualizar_datos">
                
                <div class="form-group">
                    <label class="form-label">Foto de perfil</label>
                    <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" name="nombre" class="form-control with-icon"
                            value="<?= htmlspecialchars($usuario['nombre']) ?>" required maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" class="form-control with-icon"
                               value="<?= htmlspecialchars($usuario['email']) ?>" required maxlength="150">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <div class="input-icon">
                        <i class="bi bi-telephone"></i>
                        <input type="tel" name="telefono" class="form-control with-icon"
                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" maxlength="20">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Especialidad</label>
                    <div class="input-icon">
                        <i class="bi bi-scissors"></i>
                        <input type="text" name="especialidad" class="form-control with-icon"
                               value="<?= htmlspecialchars($usuario['especialidad'] ?? '') ?>"
                               placeholder="Ej: Fades, Cortes clásicosâ€¦" maxlength="100">
                    </div>
                </div>
                <div style="margin-top: 32px;">
                    <button type="submit" class="btn btn-primary" style="width:100%; height:52px; border-radius:14px; font-weight:800; font-size:15.5px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 8px 24px rgba(181,138,74,0.3); transition:all 0.3s; letter-spacing:0.02em;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(181,138,74,0.3)'">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cambiar contraseña -->
    <div class="content-card" style="border-top: 4px solid var(--gold); background: linear-gradient(to bottom, #ffffff, #fcfcfc);">
        <div class="content-card-header" style="border-bottom: none; padding-bottom: 0;">
            <h3 style="font-size: 18px; display: flex; align-items: center;"><i class="bi bi-shield-lock" style="color:var(--gold);margin-right:10px; font-size: 22px;"></i>Seguridad y Contraseña</h3>
        </div>
        <div class="content-card-body">
            <div style="background:var(--gold-soft);border: 1px solid rgba(181,138,74,0.3);border-radius:10px;padding:16px;margin-bottom:24px;font-size:13px;color:var(--text);display:flex;gap:12px;align-items:center;">
                <i class="bi bi-info-circle-fill" style="color:var(--gold);font-size:20px;"></i>
                <span>Tu contraseña debe tener al menos <strong>8 caracteres</strong>. Recomendamos usar letras, números y símbolos.</span>
            </div>
            <form method="POST" action="<?= $base_path ?>controllers/barbero/perfilController.php" id="formPassword">
                <input type="hidden" name="accion" value="cambiar_password">
                <div class="form-group">
                    <label class="form-label">Contraseña actual</label>
                    <div class="input-icon">
                        <i class="bi bi-unlock"></i>
                        <input type="password" name="password_actual" class="form-control with-icon" required placeholder="Ingresa tu contraseña actual">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nueva contraseña</label>
                    <div class="input-icon">
                        <i class="bi bi-key"></i>
                        <input type="password" name="password_nueva" id="pwdNueva" class="form-control with-icon" required minlength="8" placeholder="Ingresa tu nueva contraseña">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <div class="input-icon">
                        <i class="bi bi-check2-all"></i>
                        <input type="password" name="password_confirmar" id="pwdConfirm" class="form-control with-icon" required minlength="8" placeholder="Repite tu nueva contraseña">
                    </div>
                    <small class="form-hint" id="pwdMatch" style="display:none;color:var(--danger);font-weight:600;margin-top:8px;">
                        <i class="bi bi-x-circle"></i> Las contraseñas no coinciden.
                    </small>
                </div>
                <div style="margin-top: 32px;">
                    <button type="button" onclick="submitPassword()" class="btn btn-primary" style="width:100%; height:52px; border-radius:14px; font-weight:800; font-size:15.5px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 8px 24px rgba(181,138,74,0.3); transition:all 0.3s; letter-spacing:0.02em;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(181,138,74,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(181,138,74,0.3)'">
                        Actualizar contraseña
                    </button>
                </div>
            </form>
        </div>


    </div>
</div>

<?php require_once $base_path . 'includes/nav_footer.php'; ?>

<script>
const pwdN = document.getElementById('pwdNueva');
const pwdC = document.getElementById('pwdConfirm');
const pwdM = document.getElementById('pwdMatch');

[pwdN, pwdC].forEach(el => el.addEventListener('input', () => {
    if (pwdN.value && pwdC.value) {
        const match = pwdN.value === pwdC.value;
        pwdM.style.display = match ? 'none' : 'block';
    } else {
        pwdM.style.display = 'none';
    }
}));

function submitPassword() {
    if (pwdN.value !== pwdC.value) {
        Swal.fire({ icon:'error', title:'Error', text:'Las contraseñas no coinciden.', confirmButtonColor:'#b58a4a' });
        return;
    }
    document.getElementById('formPassword').submit();
}
</script>
</body>
</html>

