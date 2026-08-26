<?php
/**
 * views/admin/dashboard.php
 * Dashboard principal del Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/dashboardController.php';

// Variable de conexión global en database.php (incluido en models)
global $conn;
$ctrl   = new AdminDashboardController($conn);
$stats  = $ctrl->getStats();
$citas  = $ctrl->getCitasRecientes(10);
$users  = $ctrl->getUsuarios(8);
$top_sv = $ctrl->getServiciosMasSolicitados();
$audit  = $ctrl->getAuditoriaReciente(6);

$pagina_activa = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard â€” Administrador | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <style>
        .badge-estado { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .bg-pendiente { background-color: rgba(217, 119, 6, 0.15); color: #d97706; }
        .bg-aceptada { background-color: rgba(22, 163, 74, 0.15); color: #16a34a; }
        .bg-completada { background-color: rgba(37, 99, 235, 0.15); color: #2563eb; }
        .bg-cancelada { background-color: rgba(220, 38, 38, 0.15); color: #dc2626; }
        .bg-reprogramada { background-color: rgba(181, 138, 74, 0.15); color: #b58a4a; }
        .bg-activo { background-color: rgba(22, 163, 74, 0.15); color: #16a34a; }
        .bg-inactivo { background-color: rgba(220, 38, 38, 0.15); color: #dc2626; }
        .bg-suspendido { background-color: rgba(217, 119, 6, 0.15); color: #d97706; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { text-align: left; padding: 12px 16px; border-bottom: 1px solid var(--border); color: var(--text-light); font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .table td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13px; }
        .table tr:hover { background-color: rgba(0,0,0,0.02); }
        .action-btn { 
            background: rgba(212,175,55,0.05); 
            color: #b58a4a; 
            border: 1px solid rgba(212,175,55,0.3); 
            padding: 6px 14px; 
            border-radius: 20px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            font-weight: 700; 
            font-size: 11px !important; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            text-decoration: none;
        }
        .action-btn:hover { 
            background: linear-gradient(135deg, #d4af37, #b58a4a); 
            color: #ffffff !important; 
            border-color: transparent; 
            box-shadow: 0 4px 10px rgba(212,175,55,0.3); 
            transform: translateY(-2px); 
        }
    </style>
</head>
<body class="dashboard-body">

<?php require_once PROFUNDIDAD . 'views/layouts/sidebar_admin.php'; ?>

<!-- MAIN -->
<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()" id="burgerBtn" aria-label="Menú">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="topbar-title">Dashboard</h1>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">
                Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
            </span>
        </div>
    </header>

    <!-- Page content -->
    <main class="page-content">
        <!-- Page header -->
        <div class="page-header">
            <h1>Panel de control</h1>
            <p><?= date('l, d \d\e F \d\e Y') ?></p>
        </div>

        <!-- â”€â”€ KPI STATS â”€â”€ -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['clientes'] ?></div>
                    <div class="stat-label">Clientes registrados</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-scissors"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['barberos'] ?></div>
                    <div class="stat-label">Barberos activos</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['citas_hoy'] ?></div>
                    <div class="stat-label">Citas hoy</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['citas_mes'] ?></div>
                    <div class="stat-label">Citas este mes</div>
                </div>
            </div>
        </div>

        <!-- â”€â”€ GRID PRINCIPAL â”€â”€ -->
        <div class="grid-2" style="margin-bottom:20px">
            <!-- Citas recientes -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-calendar3 me-2" style="color:var(--gold)"></i>Citas recientes</h3>
                    <a href="citas.php" class="action-btn" style="text-decoration:none; font-size:12px;">
                        Ver todas
                    </a>
                </div>
                <div class="content-card-body p-0" style="overflow-x:auto;">
                    <?php if (empty($citas)): ?>
                        <div style="padding: 40px; text-align: center; color: var(--text-light);">
                            <i class="bi bi-calendar-x" style="font-size:36px;opacity:0.4"></i>
                            <p style="margin-top: 10px;">Sin citas registradas</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; padding: 4px 0;">
                            <?php foreach ($citas as $index => $c): ?>
                            <div style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; transition: all 0.3s ease; border-bottom: <?= ($index === count($citas) - 1) ? 'none' : '1px solid #f3f4f6' ?>;" onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='transparent';">
                                
                                <div style="display: flex; align-items: center; gap: 16px; flex: 2; min-width: 220px;">
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05)); border: 1px solid rgba(212,175,55,0.2); display: flex; align-items: center; justify-content: center; color: #d4af37; font-size: 18px; font-weight: 800; box-shadow: 0 4px 10px rgba(212,175,55,0.1);">
                                        <?= strtoupper(substr(htmlspecialchars($c['cliente']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-size: 15px; font-weight: 800; color: #111827; letter-spacing: -0.3px; margin-bottom: 4px;"><?= htmlspecialchars($c['cliente']) ?></div>
                                        <div style="font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px;">
                                            <i class="bi bi-scissors" style="color:#d4af37;"></i> <?= htmlspecialchars($c['servicio']) ?>
                                        </div>
                                    </div>
                                </div>

                                <div style="flex: 1.5; min-width: 150px;">
                                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px; font-weight: 600;">Barbero</div>
                                    <div style="font-size: 14px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px;">
                                        <div style="width: 20px; height: 20px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #6b7280;"><i class="bi bi-person-fill"></i></div>
                                        <span style="text-transform: capitalize;"><?= htmlspecialchars($c['barbero']) ?></span>
                                    </div>
                                </div>

                                <div style="flex: 1.5; min-width: 140px;">
                                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px; font-weight: 600;">Fecha y Hora</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px;">
                                        <?= date('d/m/Y', strtotime($c['fecha'])) ?> <span style="color:#d1d5db; font-weight: 400;">|</span> <span style="color:#6b7280; font-weight:600;"><?= substr($c['hora'], 0, 5) ?></span>
                                    </div>
                                </div>

                                <div style="flex: 1; text-align: right; min-width: 100px;">
                                    <span class="badge-estado bg-<?= strtolower($c['estado']) ?>" style="padding: 8px 16px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; justify-content: center;">
                                        <?= $c['estado'] ?>
                                    </span>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Usuarios recientes -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-people me-2" style="color:var(--gold)"></i>Usuarios</h3>
                    <a href="usuarios.php" class="action-btn" style="text-decoration:none; font-size:12px;">
                        Ver todos
                    </a>
                </div>
                <div class="content-card-body p-0" style="overflow-x:auto;">
                    <?php if (empty($users)): ?>
                        <div style="padding: 40px; text-align: center; color: var(--text-light);">
                            <i class="bi bi-person-x" style="font-size:36px;opacity:0.4"></i>
                            <p style="margin-top: 10px;">Sin usuarios registrados</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; padding: 4px 0;">
                            <?php foreach ($users as $index => $u): ?>
                            <div style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; transition: all 0.3s ease; border-bottom: <?= ($index === count($users) - 1) ? 'none' : '1px solid #f3f4f6' ?>;" onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='transparent';">
                                
                                <div style="display: flex; align-items: center; gap: 16px; flex: 2; min-width: 220px;">
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border: 1px solid #d1d5db; display: flex; align-items: center; justify-content: center; color: #4b5563; font-size: 18px; font-weight: 800; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                        <?= strtoupper(substr(htmlspecialchars($u['nombre']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-size: 15px; font-weight: 800; color: #111827; letter-spacing: -0.3px; margin-bottom: 4px;"><?= htmlspecialchars($u['nombre']) ?></div>
                                        <div style="font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px;">
                                            <i class="bi bi-envelope" style="color:#9ca3af;"></i> <?= htmlspecialchars($u['email']) ?>
                                        </div>
                                    </div>
                                </div>

                                <div style="flex: 1; min-width: 120px;">
                                    <span style="background: rgba(212,175,55,0.1); color: #b58a4a; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(212,175,55,0.2);">
                                        <i class="bi bi-shield-check"></i> <?= $u['rol'] ?>
                                    </span>
                                </div>

                                <div style="flex: 1; min-width: 100px;">
                                    <span class="badge-estado bg-<?= strtolower($u['estado']) ?>" style="padding: 8px 16px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; justify-content: center;">
                                        <?= $u['estado'] ?>
                                    </span>
                                </div>

                                <div style="text-align: right;">
                                    <a href="usuarios.php?id=<?= $u['id_usuario'] ?>" style="background: #ffffff; border: 1px solid #d1d5db; color: #374151; padding: 8px 16px; border-radius: 10px; font-size: 12px; text-decoration: none; font-weight: 700; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#f3f4f6'; this.style.color='#111827';" onmouseout="this.style.background='#ffffff'; this.style.color='#374151';">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- â”€â”€ FILA INFERIOR â”€â”€ -->
        <div class="grid-2">
            <!-- Servicios top -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-bar-chart me-2" style="color:var(--gold)"></i>Servicios más solicitados</h3>
                </div>
                <div class="content-card-body">
                    <?php if (empty($top_sv)): ?>
                        <div style="padding: 40px; text-align: center; color: var(--text-light);">
                            <i class="bi bi-scissors" style="font-size:32px;opacity:0.4"></i>
                            <p style="margin-top: 10px;">Sin datos aún</p>
                        </div>
                    <?php else: ?>
                        <div style="padding: 8px 12px;">
                        <?php $max = max(array_column($top_sv, 'total') ?: [1]); ?>
                        <?php foreach ($top_sv as $index => $sv): ?>
                            <div style="margin-bottom: <?= ($index === count($top_sv) - 1) ? '0' : '24px' ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05)); border: 1px solid rgba(212,175,55,0.2); color: #d4af37; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 2px 4px rgba(212,175,55,0.1);">
                                            #<?= $index + 1 ?>
                                        </div>
                                        <span style="font-size:14.5px; font-weight:800; color:#111827; letter-spacing: -0.3px;"><?= htmlspecialchars($sv['nombre']) ?></span>
                                    </div>
                                    <span style="font-size:13px; color:#4b5563; font-weight:800; background: #f8fafc; padding: 4px 12px; border-radius: 20px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: baseline; gap: 4px;">
                                        <?= $sv['total'] ?> <span style="font-weight: 600; font-size: 10px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.5px;">citas</span>
                                    </span>
                                </div>
                                <div style="height: 10px; border-radius: 10px; background: #f3f4f6; width: 100%; overflow: visible; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); position: relative;">
                                    <div style="width:<?= round(($sv['total']/$max)*100) ?>%; background: linear-gradient(90deg, #b58a4a, #d4af37, #fde68a); border-radius: 10px; height: 100%; box-shadow: 0 2px 8px rgba(212,175,55,0.4); transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative;">
                                        <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 20px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent); border-radius: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Auditoría reciente -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-shield-check me-2" style="color:var(--gold)"></i>Auditoría reciente</h3>
                    <a href="auditoria.php" class="action-btn" style="text-decoration:none; font-size:12px;">
                        Ver todo
                    </a>
                </div>
                <div class="content-card-body p-0">
                    <?php if (empty($audit)): ?>
                        <div style="padding: 40px; text-align: center; color: var(--text-light);">
                            <i class="bi bi-shield" style="font-size:32px;opacity:0.4"></i>
                            <p style="margin-top: 10px;">Sin registros aún</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; padding: 4px 0;">
                            <?php foreach ($audit as $index => $a): ?>
                            <?php
                                $accionFormat = ucwords(strtolower(str_replace('_', ' ', $a['accion'])));
                                $isExito = strtolower($a['resultado']) === 'exitoso';
                                $badgeBg = $isExito ? 'rgba(22, 163, 74, 0.08)' : 'rgba(220, 38, 38, 0.08)';
                                $badgeColor = $isExito ? '#16a34a' : '#dc2626';
                                $badgeBorder = $isExito ? 'rgba(22, 163, 74, 0.2)' : 'rgba(220, 38, 38, 0.2)';
                                $icon = match(true) {
                                    str_contains($a['accion'], 'ELIMINAR') => 'bi-trash',
                                    str_contains($a['accion'], 'EDITAR') || str_contains($a['accion'], 'ACTUALIZAR') => 'bi-pencil-square',
                                    str_contains($a['accion'], 'CREAR') || str_contains($a['accion'], 'AGREGAR') || str_contains($a['accion'], 'GENERAR') => 'bi-plus-circle',
                                    str_contains($a['accion'], 'ACTIVAR') => 'bi-check-circle',
                                    default => 'bi-shield-check'
                                };
                            ?>
                            <div style="padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; transition: all 0.3s ease; border-bottom: <?= ($index === count($audit) - 1) ? 'none' : '1px solid #f3f4f6' ?>;" onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='transparent';">
                                
                                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                    <div style="width: 38px; height: 38px; border-radius: 12px; background: #f3f4f6; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #4b5563; font-size: 15px; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                        <i class="bi <?= $icon ?>"></i>
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="font-size: 13.5px; font-weight: 700; color: #111827; letter-spacing: -0.2px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($accionFormat) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <i class="bi bi-person-fill" style="color: #9ca3af;"></i> 
                                            <span style="text-transform: capitalize;"><?= htmlspecialchars($a['usuario_nombre'] ?? $a['usuario'] ?? 'Sistema') ?></span>
                                            <span style="color:#d1d5db; font-size: 10px;">|</span> 
                                            <span style="font-weight: 600;"><?= date('d/m/Y H:i', strtotime($a['fecha_hora'])) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div style="flex-shrink: 0;">
                                    <span style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; padding: 6px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid <?= $badgeBorder ?>;">
                                        <?= $a['resultado'] ?>
                                    </span>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumen citas hoy (mini cards) -->
        <div class="grid-3 mt-3">
            <div class="content-card">
                <div class="content-card-body" style="text-align: center;">
                    <div style="font-size:28px;font-weight:800;color:var(--warning)"><?= $stats['citas_hoy_pend'] ?></div>
                    <div style="font-size:12px;color:var(--text-light);margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Pendientes hoy</div>
                </div>
            </div>
            <div class="content-card">
                <div class="content-card-body" style="text-align: center;">
                    <div style="font-size:28px;font-weight:800;color:var(--success)"><?= $stats['citas_hoy_acept'] ?></div>
                    <div style="font-size:12px;color:var(--text-light);margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Aceptadas hoy</div>
                </div>
            </div>
            <div class="content-card">
                <div class="content-card-body" style="text-align: center;">
                    <div style="font-size:28px;font-weight:800;color:var(--info)"><?= $stats['citas_hoy_comp'] ?></div>
                    <div style="font-size:12px;color:var(--text-light);margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Completadas hoy</div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('open');
}

// SweetAlert si viene de logout/expiración
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('expired') === '1') {
    Swal.fire({ icon:'warning', title:'Sesión expirada', text:'Tu sesión cerró por inactividad.', confirmButtonColor:'#b58a4a' });
}
</script>
</body>
</html>

