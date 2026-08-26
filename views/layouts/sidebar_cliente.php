<?php
/**
 * sidebar_cliente.php
 * Sidebar del CLIENTE. Incluir con require_once.
 */
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Cliente';
$iniciales      = mb_strtoupper(mb_substr($nombre_usuario, 0, 2));
$pagina_actual  = basename($_SERVER['PHP_SELF']);
?>


<!-- Overlay móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="<?= $base_path ?>public/img/logo_corona.jpg" alt="MC Barber">
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">MC BARBER</span>
            <span class="sidebar-brand-sub">Mi cuenta</span>
        </div>

    </div>

    <!-- Navegación -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>

        <a href="<?= $base_path ?>views/cliente/dashboard.php"
        class="nav-item <?= $pagina_actual === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-fill"></i>
            <span>Inicio</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Citas</div>

        <a href="<?= $base_path ?>views/cliente/agendar.php"
        class="nav-item <?= $pagina_actual === 'agendar.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-plus-fill"></i>
            <span>Agendar cita</span>
        </a>

        <a href="<?= $base_path ?>views/cliente/mis_citas.php"
        class="nav-item <?= $pagina_actual === 'mis_citas.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i>
            <span>Mis citas</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Explorar</div>

        <a href="<?= $base_path ?>views/cliente/servicios.php"
        class="nav-item <?= $pagina_actual === 'servicios.php' ? 'active' : '' ?>">
            <i class="bi bi-scissors"></i>
            <span>Servicios</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Cuenta</div>

        <a href="<?= $base_path ?>views/cliente/perfil.php"
        class="nav-item <?= $pagina_actual === 'perfil.php' ? 'active' : '' ?>">
            <i class="bi bi-person-fill"></i>
            <span>Mi perfil</span>
        </a>

        <div style="margin-top:16px">
            <a href="<?= $base_path ?>controllers/auth/logoutController.php"
               class="nav-item logout"
               onclick="confirmarCerrarSesion(event, this.href)">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </nav>

    <!-- Footer usuario -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($_SESSION['usuario_foto'])): ?>
                    <?php $path_prefix = isset($base_path) ? $base_path : '../../'; ?>
                    <img src="<?= $path_prefix ?>public/uploads/perfiles/<?= htmlspecialchars($_SESSION['usuario_foto']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= htmlspecialchars($iniciales) ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="sidebar-user-role">Cliente</div>
            </div>
        </div>
    </div>
</aside>

<script>
if (typeof window.confirmarCerrarSesion !== 'function') {
    window.confirmarCerrarSesion = function(e, url) {
        e.preventDefault();
        Swal.fire({
            html: `
                <div style="display: flex; gap: 16px; align-items: flex-start; text-align: left;">
                    <div style="flex-shrink: 0; width: 40px; height: 40px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-arrow-right" style="font-size: 20px; color: #ef4444;"></i>
                    </div>
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #111827;">¿Cerrar sesión?</h3>
                        <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.5;">Saldrás de tu cuenta de forma segura.</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-ultra-modern',
                confirmButton: 'swal-btn swal-btn-danger',
                cancelButton: 'swal-btn swal-btn-secondary',
                actions: 'swal-actions-right'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    };
}

// Lógica para mantener el sidebar abierto temporalmente al cambiar de página
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    if (sessionStorage.getItem('keep_sidebar_open') === 'true') {
        const mainContent = document.querySelector('.main-content');
        
        // 1. Disable transitions to prevent jump
        sidebar.style.transition = 'none';
        if (mainContent) mainContent.style.transition = 'none';

        // 2. Apply class
        sidebar.classList.add('force-open');

        // 3. Force reflow
        void sidebar.offsetWidth;

        // 4. Restore transitions
        sidebar.style.transition = '';
        if (mainContent) mainContent.style.transition = '';

        sessionStorage.removeItem('keep_sidebar_open');
    }

    sidebar.addEventListener('mouseleave', function() {
        sidebar.classList.remove('force-open');
    });

    const navLinks = document.querySelectorAll('.sidebar-nav .nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (!this.classList.contains('logout')) {
                sessionStorage.setItem('keep_sidebar_open', 'true');
            }
        });
    });
});
</script>
