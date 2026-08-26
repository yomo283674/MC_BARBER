<?php
/**
 * nav_admin.php
 * Sidebar + topbar reutilizables para el dashboard del ADMINISTRADOR.
 * Variables esperadas:
 *   $pagina_activa — string ('dashboard'|'usuarios'|'barberos'|'citas'|'servicios'|'reportes'|'auditoria'|'configuracion'|'perfil')
 *   $titulo_pagina — string mostrado en el topbar
 *   $base_path     — string con prefijo de rutas ('../../')
 */
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
$inicial        = strtoupper(mb_substr($nombre_usuario, 0, 1));
$pagina_activa  = $pagina_activa ?? '';
$titulo_pagina  = $titulo_pagina ?? 'Panel Administrador';
$base_path      = $base_path     ?? '../../';
?>


<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= $base_path ?>public/img/logo_corona.jpg" alt="MC Barber">
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">MC BARBER</span>
            <span class="sidebar-brand-sub">Administrador</span>
        </div>

    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Principal</span>
        <a href="<?= $base_path ?>views/admin/dashboard.php"
        class="nav-item <?= $pagina_activa === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
        </a>

        <span class="nav-section-label">Gestión</span>
        <a href="<?= $base_path ?>views/admin/barberos.php"
        class="nav-item <?= $pagina_activa === 'barberos' ? 'active' : '' ?>">
            <i class="bi bi-scissors"></i><span>Barberos</span>
        </a>
        <a href="<?= $base_path ?>views/admin/usuarios.php"
        class="nav-item <?= $pagina_activa === 'usuarios' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i><span>Usuarios</span>
        </a>
        <a href="<?= $base_path ?>views/admin/citas.php"
        class="nav-item <?= $pagina_activa === 'citas' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i><span>Citas</span>
        </a>
        <a href="<?= $base_path ?>views/admin/servicios.php"
        class="nav-item <?= $pagina_activa === 'servicios' ? 'active' : '' ?>">
            <i class="bi bi-tags-fill"></i><span>Servicios</span>
        </a>

        <span class="nav-section-label">Sistema</span>
        <a href="<?= $base_path ?>views/admin/reportes.php"
        class="nav-item <?= $pagina_activa === 'reportes' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-fill"></i><span>Reportes</span>
        </a>
        <a href="<?= $base_path ?>views/admin/auditoria.php"
        class="nav-item <?= $pagina_activa === 'auditoria' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock-fill"></i><span>Auditoría</span>
        </a>
        <a href="<?= $base_path ?>views/admin/configuracion.php"
        class="nav-item <?= $pagina_activa === 'configuracion' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i><span>Configuración</span>
        </a>

        <span class="nav-section-label">Cuenta</span>
        <a href="<?= $base_path ?>views/admin/perfil.php"
        class="nav-item <?= $pagina_activa === 'perfil' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i><span>Mi Perfil</span>
        </a>
        <a href="<?= $base_path ?>controllers/auth/logoutController.php"
        class="nav-item logout"
        onclick="confirmarCerrarSesion(event, this.href)">
            <i class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= htmlspecialchars($inicial) ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="sidebar-user-role">Administrador</div>
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
<!-- MAIN CONTENT WRAPPER -->
<div class="main-content">
    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="topbar-title"><?= htmlspecialchars($titulo_pagina) ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Hola, <strong><?= htmlspecialchars(explode(' ', $nombre_usuario)[0]) ?></strong></span>
        </div>
    </header>

    <!-- PAGE CONTENT STARTS HERE -->
    <div class="page-content">
