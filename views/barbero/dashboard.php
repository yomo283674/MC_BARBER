<?php
/**
 * views/barbero/dashboard.php
 * Dashboard principal del Barbero.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);

require_once $base_path . 'controllers/barbero/dashboardController.php';

$id_barbero = (int)$_SESSION['usuario_id'];
$ctrl       = new BarberoDashboardController();
$stats      = $ctrl->getStats($id_barbero);
$agenda_hoy = $ctrl->getAgendaHoy($id_barbero);
$pendientes = $ctrl->getCitasPendientes($id_barbero);
$disponib   = $ctrl->getDisponibilidadProxima($id_barbero);
$servicios  = $ctrl->getServicios();

$hoy_label = date('l, d \d\e F \d\e Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Barbero | MC Barber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'views/layouts/sidebar_barbero.php'; ?>

<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Menú">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="topbar-title">Mi Dashboard</h1>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting" style="background:#111827; color:#fff; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-calendar2-week" style="color:var(--gold);"></i>
                <span><?= date('d/m/Y') ?></span>
            </span>
        </div>
    </header>

    <main class="page-content">
        <div class="page-header" style="margin-bottom:24px;">
            <h1 style="font-size:28px; font-weight:800; color:#111827; letter-spacing:-0.03em; display:flex; align-items:center; gap:12px;">
                Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                <i class="bi bi-stars" style="color:var(--gold); font-size:26px;"></i>
            </h1>
            <p style="color:#6b7280; font-size:15px; margin-top:4px;">Aquí está tu agenda y actividad para el día de hoy.</p>
        </div>

        <!-- ── KPIs ── -->
        <div class="grid-4" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
            <div style="background:#fff; border-radius:20px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); border:1px solid #f3f4f6; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg, #fef3c7, #fde68a); display:flex; align-items:center; justify-content:center; color:#d97706; font-size:24px;">
                    <i class="bi bi-calendar-day-fill"></i>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:#111827; line-height:1;"><?= $stats['citas_hoy'] ?></div>
                    <div style="font-size:13px; font-weight:600; color:#6b7280; margin-top:4px;">Citas hoy</div>
                </div>
            </div>

            <div style="background:#fff; border-radius:20px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); border:1px solid #f3f4f6; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg, #fee2e2, #fecaca); display:flex; align-items:center; justify-content:center; color:#dc2626; font-size:24px;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:#111827; line-height:1;"><?= $stats['pendientes'] ?></div>
                    <div style="font-size:13px; font-weight:600; color:#6b7280; margin-top:4px;">Pendientes</div>
                </div>
            </div>

            <div style="background:#fff; border-radius:20px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); border:1px solid #f3f4f6; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg, #dcfce7, #bbf7d0); display:flex; align-items:center; justify-content:center; color:#16a34a; font-size:24px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:#111827; line-height:1;"><?= $stats['aceptadas'] ?></div>
                    <div style="font-size:13px; font-weight:600; color:#6b7280; margin-top:4px;">Aceptadas</div>
                </div>
            </div>

            <div style="background:#fff; border-radius:20px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); border:1px solid #f3f4f6; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg, #dbeafe, #bfdbfe); display:flex; align-items:center; justify-content:center; color:#2563eb; font-size:24px;">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:#111827; line-height:1;"><?= $stats['completadas_hoy'] ?></div>
                    <div style="font-size:13px; font-weight:600; color:#6b7280; margin-top:4px;">Completadas hoy</div>
                </div>
            </div>
        </div>

        <!-- â”€â”€ GRID PRINCIPAL â”€â”€ -->
        <div class="grid-2" style="margin-bottom:20px">
            <!-- Agenda del día -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-calendar-day me-2" style="color:var(--gold)"></i>Agenda de hoy</h3>
                    <small style="color:var(--text-muted);font-size:11px"><?= date('d/m/Y') ?></small>
                </div>
                <div class="content-card-body">
                    <?php if (empty($agenda_hoy)): ?>
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 64px 0; text-align:center;">
                            <div style="width:80px; height:80px; background:linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius:24px; display:flex; align-items:center; justify-content:center; margin-bottom: 20px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);">
                                <i class="bi bi-calendar-x-fill" style="font-size: 32px; color: #9ca3af;"></i>
                            </div>
                            <h4 style="margin:0 0 8px; font-size:16px; font-weight:700; color:#111827;">Día despejado</h4>
                            <p style="margin:0; font-size: 14px; color: #6b7280; max-width: 250px;">No tienes citas programadas para hoy. Tómate un respiro.</p>
                        </div>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:10px">
                            <?php foreach ($agenda_hoy as $c):
                                $estadoClass = match($c['estado']) {
                                    'COMPLETADA'   => 'bg-primary',
                                    'CANCELADA'    => 'bg-danger',
                                    'REPROGRAMADA' => 'bg-warning',
                                    'ACEPTADA'     => 'bg-success',
                                    default        => 'bg-secondary'
                                };
                            ?>
                            <div style="display:flex;align-items:center;gap:16px;padding:16px;background:#ffffff;border-radius:12px;border:1px solid #f3f4f6;border-left:4px solid var(--gold);box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                                <div style="text-align:center;min-width:52px">
                                    <div style="font-size:18px;font-weight:800;color:var(--gold)"><?= substr($c['hora'], 0, 5) ?></div>
                                </div>
                                <div style="flex:1">
                                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($c['cliente']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($c['servicio']) ?> · $<?= number_format($c['precio'], 0, ',', '.') ?></div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                                    <span class="badge-estado badge-<?= strtolower($c['estado']) ?>">
                                        <?= ucfirst(strtolower($c['estado'])) ?>
                                    </span>
                                    <?php if (in_array($c['estado'], ['PENDIENTE', 'ACEPTADA'])): ?>
                                    <div style="display:flex;gap:6px; margin-top: 4px;">
                                        <?php if ($c['estado'] === 'PENDIENTE'): ?>
                                        <button onclick="accionCita(<?= $c['id_cita'] ?>, 'aceptar')"
                                                class="btn btn-sm"
                                                style="background:rgba(22,163,74,0.1);color:var(--success);font-size:12px;padding:4px 10px; font-weight: 600; border-radius: 6px; transition: all 0.2s;"
                                                onmouseover="this.style.background='var(--success)'; this.style.color='#fff';"
                                                onmouseout="this.style.background='rgba(22,163,74,0.1)'; this.style.color='var(--success)';">
                                            <i class="bi bi-check2"></i> Aceptar
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($c['estado'] === 'ACEPTADA'): ?>
                                        <button onclick="accionCita(<?= $c['id_cita'] ?>, 'completar')"
                                                class="btn btn-sm"
                                                style="background:var(--gold-soft);color:var(--gold);font-size:12px;padding:4px 10px; font-weight: 600; border-radius: 6px; transition: all 0.2s;"
                                                onmouseover="this.style.background='var(--gold)'; this.style.color='#fff';"
                                                onmouseout="this.style.background='var(--gold-soft)'; this.style.color='var(--gold)';">
                                            <i class="bi bi-check2-all"></i> Completar
                                        </button>
                                        <?php endif; ?>
                                        <button onclick="accionCita(<?= $c['id_cita'] ?>, 'cancelar')"
                                                class="btn btn-sm"
                                                style="background:rgba(220,38,38,0.1);color:var(--danger);font-size:12px;padding:4px 10px; font-weight: 600; border-radius: 6px; transition: all 0.2s;"
                                                onmouseover="this.style.background='var(--danger)'; this.style.color='#fff';"
                                                onmouseout="this.style.background='rgba(220,38,38,0.1)'; this.style.color='var(--danger)';">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Próximas citas + Disponibilidad -->
            <div style="display:flex;flex-direction:column;gap:20px">
                <!-- Citas pendientes próximas -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h3><i class="bi bi-clock-fill me-2" style="color:var(--gold)"></i>Próximas citas</h3>
                    </div>
                    <div class="content-card-body p-0">
                        <?php
                        $prox = array_slice(array_values($pendientes), 0, 5);
                        if (empty($prox)):
                        ?>
                            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 48px 0;">
                                <div style="width: 56px; height: 56px; background: #f8fafc; border-radius: 16px; display:flex; align-items:center; justify-content:center; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                    <i class="bi bi-cup-hot-fill" style="font-size: 24px; color: #94a3b8;"></i>
                                </div>
                                <p style="margin:0; font-size: 13.5px; font-weight: 600; color: #64748b;">Sin citas próximas pendientes</p>
                            </div>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($prox as $c): ?>
                                <li style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
                                    <div style="width:36px;height:36px;background:var(--gold-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <i class="bi bi-person" style="color:var(--gold)"></i>
                                    </div>
                                    <div style="flex:1">
                                        <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($c['cliente']) ?></div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            <?= date('d/m/Y', strtotime($c['fecha'])) ?> a las <?= substr($c['hora'], 0, 5) ?>
                                        </div>
                                    </div>
                                    <span class="badge-estado badge-<?= strtolower($c['estado']) ?>">
                                        <?= ucfirst(strtolower($c['estado'])) ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Disponibilidad próxima -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h3><i class="bi bi-calendar2-plus me-2" style="color:var(--gold)"></i>Mi disponibilidad</h3>
                        <a href="disponibilidad.php" class="btn btn-sm d-inline-flex align-items-center gap-1" style="background:var(--gold-soft); color:var(--gold); font-size:12px; font-weight:700; padding:6px 14px; border-radius:20px; border:1px solid rgba(181,138,74,0.2); transition:all 0.2s; letter-spacing:0.02em; text-decoration:none;" onmouseover="this.style.background='var(--gold)'; this.style.color='#fff';" onmouseout="this.style.background='var(--gold-soft)'; this.style.color='var(--gold)';">
                            <i class="bi bi-sliders"></i> Gestionar
                        </a>
                    </div>
                    <div class="content-card-body">
                        <?php if (empty($disponib)): ?>
                            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 32px 0;">
                                <div style="width: 48px; height: 48px; background: #fffbeb; border-radius: 50%; display:flex; align-items:center; justify-content:center; margin-bottom: 12px;">
                                    <i class="bi bi-calendar2-plus" style="font-size: 20px; color: #d97706;"></i>
                                </div>
                                <p style="margin:0; font-size: 13px; font-weight: 500; color: #6b7280;">Sin disponibilidad para los próximos 7 días.</p>
                                <a href="disponibilidad.php" class="btn btn-sm mt-3" style="background:#111827; color:#fff; font-size:13px; border-radius: 8px; padding: 8px 16px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <i class="bi bi-plus me-1"></i>Agregar horarios
                                </a>
                            </div>
                        <?php else: ?>
                            <?php
                            // Agrupar por fecha
                            $por_fecha = [];
                            foreach ($disponib as $d) { $por_fecha[$d['fecha']][] = $d; }
                            $dias_es = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
                            ?>
                            <?php foreach (array_slice($por_fecha, 0, 3, true) as $fecha => $slots): 
                                // Lógica de agrupación de franjas continuas
                                usort($slots, function($a, $b) {
                                    return strtotime($a['hora_inicio']) - strtotime($b['hora_inicio']);
                                });

                                $bloques_agrupados = [];
                                $bloque_actual = null;

                                foreach ($slots as $s) {
                                    if ($bloque_actual === null) {
                                        $bloque_actual = $s;
                                    } else {
                                        if ($s['hora_inicio'] === $bloque_actual['hora_fin'] && $s['disponible'] == $bloque_actual['disponible']) {
                                            $bloque_actual['hora_fin'] = $s['hora_fin'];
                                        } else {
                                            $bloques_agrupados[] = $bloque_actual;
                                            $bloque_actual = $s;
                                        }
                                    }
                                }
                                if ($bloque_actual !== null) {
                                    $bloques_agrupados[] = $bloque_actual;
                                }
                            ?>
                            <div style="background:#ffffff; border-radius:14px; padding:20px; margin-bottom:16px; border:1px solid #f3f4f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition:all 0.2s ease;" onmouseover="this.style.borderColor='#e5e7eb';this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'" onmouseout="this.style.borderColor='#f3f4f6';this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'">
                                <div style="display:flex; align-items:center; margin-bottom:16px; gap:12px;">
                                    <div style="background:var(--gold-soft); color:var(--gold); padding:6px 12px; border-radius:8px; font-weight:800; font-size:13.5px; display:flex; align-items:center; gap:6px;">
                                        <i class="bi bi-calendar-event"></i> <?= date('d/m', strtotime($fecha)) ?>
                                    </div>
                                    <div style="font-size:15px; font-weight:800; color:var(--text-color);">
                                        <?= $dias_es[date('l', strtotime($fecha))] ?>
                                    </div>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <?php foreach ($bloques_agrupados as $s): ?>
                                    <div style="background:<?= $s['disponible'] ? 'rgba(22,163,74,0.06)' : 'rgba(220,38,38,0.06)' ?>; 
                                                color:<?= $s['disponible'] ? 'var(--success)' : 'var(--danger)' ?>; 
                                                padding:10px 14px; 
                                                border-radius:10px; 
                                                font-size:13.5px; 
                                                font-weight:600;
                                                border-left: 4px solid <?= $s['disponible'] ? 'var(--success)' : 'var(--danger)' ?>;
                                                display: flex; align-items: center; gap: 10px;">
                                        <i class="bi <?= $s['disponible'] ? 'bi-clock-fill' : 'bi-x-circle-fill' ?>" style="font-size: 15px;"></i>
                                        <span><?= substr($s['hora_inicio'],0,5) ?> – <?= substr($s['hora_fin'],0,5) ?></span>
                                        <span style="font-size:11px; margin-left: auto; opacity: 0.9; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <?= $s['disponible'] ? 'Disponible' : 'Ocupado' ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios disponibles -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-scissors me-2" style="color:var(--gold)"></i>Servicios del salón</h3>
            </div>
            <div class="content-card-body">
                <?php if (empty($servicios)): ?>
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 48px 0;">
                        <div style="width: 56px; height: 56px; background: #f3f4f6; border-radius: 50%; display:flex; align-items:center; justify-content:center; margin-bottom: 16px;">
                            <i class="bi bi-scissors" style="font-size: 24px; color: #9ca3af;"></i>
                        </div>
                        <p style="margin:0; font-size: 14px; font-weight: 500; color: #6b7280;">Sin servicios activos en el salón.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-3" style="gap:20px;">
                        <?php foreach (array_slice($servicios, 0, 6) as $s): ?>
                        <div style="background:#ffffff; border-radius:20px; border:1px solid #f3f4f6; overflow:hidden; transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01)';this.style.borderColor='#e5e7eb'" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.02)';this.style.borderColor='#f3f4f6'">
                            <?php if (!empty($s['imagen'])): ?>
                                <div style="height:150px; background-image:url('<?= $base_path ?>public/uploads/servicios/<?= htmlspecialchars($s['imagen']) ?>'); background-size:cover; background-position:center; position:relative;">
                                    <div style="position:absolute; bottom:0; left:0; right:0; height:40%; background:linear-gradient(to top, rgba(0,0,0,0.2), transparent);"></div>
                                </div>
                            <?php else: ?>
                                <div style="height:150px; background:linear-gradient(135deg, #1f2937, #111827); display:flex; align-items:center; justify-content:center; position:relative;">
                                    <i class="bi bi-scissors" style="font-size:40px;color:rgba(255,255,255,0.05);"></i>
                                    <i class="bi bi-scissors" style="font-size:32px;color:var(--gold); position:absolute;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="padding:18px;">
                                <h4 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 6px 0; letter-spacing:-0.01em;"><?= htmlspecialchars($s['nombre']) ?></h4>
                                <div style="font-size:13px; color:#6b7280; line-height:1.5; margin-bottom:18px; min-height: 38px;">
                                    <?= htmlspecialchars(mb_strimwidth($s['descripcion'] ?? '', 0, 65, '…')) ?>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="display:flex; align-items:baseline; gap:2px;">
                                        <span style="font-size:14px; font-weight:700; color:#d97706;">$</span>
                                        <span style="font-size:20px; font-weight:800; color:#111827; letter-spacing:-0.02em;"><?= number_format($s['precio'], 0, ',', '.') ?></span>
                                    </div>
                                    <div style="font-size:12px; background:#f8fafc; border:1px solid #e2e8f0; padding:6px 12px; border-radius:20px; color:#475569; font-weight:600; display:flex; align-items:center; gap:6px;">
                                        <i class="bi bi-clock-history"></i> <?= $s['duracion_min'] ?> min
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function accionCita(id_cita, accion) {
    const mensajes = {
        aceptar:   { title:'¿Aceptar cita?',   text:'Confirma que aceptas esta cita.',                icon:'question', confirmText:'Sí, aceptar'   },
        completar: { title:'¿Marcar completada?', text:'Se marcará la cita como completada.',          icon:'success',  confirmText:'Sí, completar'  },
        cancelar:  { title:'¿Cancelar cita?',   text:'Esta acción notificará al cliente.',             icon:'warning',  confirmText:'Sí, cancelar'   },
    };
    const m = mensajes[accion];

    Swal.fire({
        title: m.title,
        text: m.text,
        icon: m.icon,
        showCancelButton: true,
        confirmButtonColor: '#b58a4a',
        cancelButtonColor: '#aaa',
        confirmButtonText: m.confirmText,
        cancelButtonText: 'No'
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = `../../controllers/barbero/citaActionController.php?id=${id_cita}&accion=${accion}`;
        }
    });
}
</script>
</body>
</html>

