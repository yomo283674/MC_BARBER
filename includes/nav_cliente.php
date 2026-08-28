<?php
/**
 * nav_cliente.php
 * Sidebar y topbar reutilizables para el dashboard del CLIENTE.
 * Variables disponibles:
 *   $pagina_activa — string con el nombre de la pagina actual
 *   $titulo_pagina — string con el titulo del topbar
 */
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Cliente';
$inicial = strtoupper(mb_substr($nombre_usuario, 0, 1));
$pagina_activa = $pagina_activa ?? '';
$titulo_pagina  = $titulo_pagina ?? 'Panel Cliente';
$base_path      = $base_path     ?? '../../';
global $globalConfig;
$nombre_negocio = $globalConfig['nombre_negocio'] ?? 'MC BARBER';
$logo_url = $globalConfig['logo_url'] ?? 'public/img/logo_corona.jpg';
$logo_src = str_starts_with($logo_url, 'http') ? $logo_url : $base_path . $logo_url;
?>


<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= htmlspecialchars($logo_src) ?>" alt="<?= htmlspecialchars($nombre_negocio) ?>">
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name"><?= htmlspecialchars($nombre_negocio) ?></span>
            <span class="sidebar-brand-sub">Mi Cuenta</span>
        </div>

    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Principal</span>
        <a href="dashboard.php" class="nav-item <?= $pagina_activa === 'inicio' ? 'active' : '' ?>">
            <i class="bi bi-house-fill"></i><span>Inicio</span>
        </a>
        <a href="agendar.php" class="nav-item <?= $pagina_activa === 'agendar' ? 'active' : '' ?>">
            <i class="bi bi-calendar-plus-fill"></i><span>Agendar Cita</span>
        </a>
        <a href="mis_citas.php" class="nav-item <?= $pagina_activa === 'mis_citas' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i><span>Mis Citas</span>
        </a>

        <span class="nav-section-label">Explorar</span>
        <a href="servicios.php" class="nav-item <?= $pagina_activa === 'servicios' ? 'active' : '' ?>">
            <i class="bi bi-scissors"></i><span>Servicios</span>
        </a>
        <a href="turno.php" class="nav-item <?= $pagina_activa === 'turno' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i><span>Turno en Tiempo Real</span>
        </a>

        <span class="nav-section-label">Cuenta</span>
        <a href="perfil.php" class="nav-item <?= $pagina_activa === 'perfil' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i><span>Mi Perfil</span>
        </a>
        <a href="../../controllers/auth/logoutController.php" class="nav-item logout" onclick="confirmarCerrarSesion(event, this.href)">
            <i class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($_SESSION['usuario_foto'])): ?>
                    <?php 
                        // Path depends on where the file is included from, mostly views/cliente/
                        $foto_path = str_replace('views/cliente', '', $base_path ?? '../../') . 'public/uploads/perfiles/' . $_SESSION['usuario_foto'];
                        // A safer way is using absolute path from root or a relative path from the current URL 
                        // But $base_path or PROFUNDIDAD is usually defined in the view
                        $path_prefix = defined('PROFUNDIDAD') ? PROFUNDIDAD : (isset($base_path) ? $base_path : '../../');
                    ?>
                    <img src="<?= $path_prefix ?>public/uploads/perfiles/<?= htmlspecialchars($_SESSION['usuario_foto']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= htmlspecialchars($inicial) ?>
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
