<?php
/**
 * sidebar_barbero.php
 * Sidebar del BARBERO. Incluir con require_once.
 */
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Barbero';
$iniciales      = mb_strtoupper(mb_substr($nombre_usuario, 0, 2));
$pagina_actual  = basename($_SERVER['PHP_SELF']);
?>


<!-- Overlay móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= $base_path ?>public/img/logo_corona.jpg" alt="MC Barber">
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">MC BARBER</span>
            <span class="sidebar-brand-sub">Barbero</span>
        </div>

    </div>

    <!-- Navegación -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>

        <a href="<?= $base_path ?>views/barbero/dashboard.php"
        class="nav-item <?= $pagina_actual === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Mis Citas</div>

        <a href="<?= $base_path ?>views/barbero/agenda.php"
        class="nav-item <?= $pagina_actual === 'agenda.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-day-fill"></i>
            <span>Agenda del día</span>
        </a>

        <a href="<?= $base_path ?>views/barbero/citas.php"
        class="nav-item <?= $pagina_actual === 'citas.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i>
            <span>Todas las citas</span>
        </a>

        <a href="<?= $base_path ?>views/barbero/clientes.php"
        class="nav-item <?= $pagina_actual === 'clientes.php' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>Historial</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Disponibilidad</div>

        <a href="<?= $base_path ?>views/barbero/disponibilidad.php"
        class="nav-item <?= $pagina_actual === 'disponibilidad.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-fill"></i>
            <span>Mi disponibilidad</span>
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
            <?php if (!empty($_SESSION['usuario_foto'])): ?>
                <div class="sidebar-avatar" style="background-image:url('<?= $base_path ?>public/uploads/perfiles/<?= htmlspecialchars($_SESSION['usuario_foto']) ?>'); background-size:cover; background-position:center;"></div>
            <?php else: ?>
                <div class="sidebar-avatar"><?= htmlspecialchars($iniciales) ?></div>
            <?php endif; ?>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="sidebar-user-role">Barbero</div>
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
