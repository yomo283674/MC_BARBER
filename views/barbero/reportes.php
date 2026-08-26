<?php
/**
 * views/barbero/reportes.php
 * Reportes de rendimiento del barbero - últimos 30 días por defecto.
 * Muestra: ingresos, citas por estado, top servicios y gráfica de citas por día.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'controllers/barbero/reportesController.php';

$id_barbero = (int)$_SESSION['usuario_id'];

// Rango de fechas
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');

$ctrl  = new BarberoReportesController();
$datos = $ctrl->getDatos($id_barbero, $desde, $hasta);

$resumen    = $datos['resumen'];
$por_dia    = $datos['por_dia'];
$svcs_top   = $datos['servicios_top'];
$tasa_cancel= $datos['tasa_cancelacion'];

// Preparar datos para Chart.js
$chart_labels  = array_column($por_dia, 'dia');
$chart_valores = array_column($por_dia, 'total');

$pagina_activa = 'reportes';
$titulo_pagina = 'Mis Reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes &mdash; Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'includes/nav_barbero.php'; ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:22px;font-weight:800;display:flex;align-items:center;gap:10px;letter-spacing:-0.02em">
            Mis Reportes <i class="bi bi-bar-chart-line-fill" style="color:var(--gold);font-size:22px"></i>
        </h1>
        <p style="font-size:14px;color:var(--text-light);margin-top:4px">Análisis de rendimiento del <strong><?= date('d/m/Y', strtotime($desde)) ?></strong> al <strong><?= date('d/m/Y', strtotime($hasta)) ?></strong>.</p>
    </div>
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;background:var(--white);padding:12px 16px;border-radius:12px;box-shadow:var(--shadow);border:1px solid var(--border)">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:700;color:var(--text-light);letter-spacing:0.05em">DESDE</label>
            <input type="date" name="desde" value="<?= $desde ?>"
                   style="height:40px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:13px;background:var(--background);color:var(--text);font-family:inherit;outline:none;transition:all 0.2s" onfocus="this.style.borderColor='var(--gold)';this.style.background='var(--white)'" onblur="this.style.borderColor='var(--border)';this.style.background='var(--background)'">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11px;font-weight:700;color:var(--text-light);letter-spacing:0.05em">HASTA</label>
            <input type="date" name="hasta" value="<?= $hasta ?>"
                   style="height:40px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:13px;background:var(--background);color:var(--text);font-family:inherit;outline:none;transition:all 0.2s" onfocus="this.style.borderColor='var(--gold)';this.style.background='var(--white)'" onblur="this.style.borderColor='var(--border)';this.style.background='var(--background)'">
        </div>
        <button type="submit" class="btn btn-primary" style="height:40px;padding:0 20px;border-radius:8px;font-weight:600;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(181,138,74,0.25)">
            <i class="bi bi-search"></i> Filtrar
        </button>
        <a href="reportes.php" class="btn" style="height:40px;padding:0 16px;background:var(--background);border:1px solid var(--border);color:var(--text-light);display:flex;align-items:center;gap:6px;font-size:13px;border-radius:8px;font-weight:600;transition:all 0.2s;text-decoration:none" onmouseover="this.style.background='#e5e5e5';this.style.color='var(--text)'" onmouseout="this.style.background='var(--background)';this.style.color='var(--text-light)'">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </a>
    </form>
</div>

<!-- KPIs -->
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($resumen['ingresos'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">Ingresos estimados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-check2-all"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $resumen['completadas'] ?? 0 ?></div>
            <div class="stat-label">Citas completadas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= ($resumen['pendientes'] ?? 0) + ($resumen['aceptadas'] ?? 0) ?></div>
            <div class="stat-label">Citas pendientes/aceptadas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(220,38,38,0.1);color:var(--danger)"><i class="bi bi-x-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $tasa_cancel ?>%</div>
            <div class="stat-label">Tasa de cancelación</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Gráfica de citas por día -->
    <div class="content-card">
        <div class="content-card-header">
            <h3><i class="bi bi-graph-up" style="color:var(--gold);margin-right:8px"></i>Citas completadas por día</h3>
        </div>
        <div class="content-card-body">
            <?php if (empty($por_dia)): ?>
                <div class="empty-state" style="padding:40px 0">
                    <i class="bi bi-graph-down"></i>
                    <h3>Sin datos</h3>
                    <p>No hay citas completadas en el período seleccionado.</p>
                </div>
            <?php else: ?>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartCitasDia"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top servicios + resumen por estado -->
    <div style="display:flex;flex-direction:column;gap:20px">
        <!-- Resumen por estado -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-pie-chart" style="color:var(--gold);margin-right:8px"></i>Resumen por estado</h3>
            </div>
            <div class="content-card-body">
                <?php
                $estados_info = [
                    ['label'=>'Completadas',  'val'=>$resumen['completadas']??0, 'color'=>'var(--success)', 'icon'=>'bi-check2-all'],
                    ['label'=>'Aceptadas',    'val'=>$resumen['aceptadas']??0,   'color'=>'var(--info)',    'icon'=>'bi-check2'],
                    ['label'=>'Pendientes',   'val'=>$resumen['pendientes']??0,  'color'=>'var(--warning)', 'icon'=>'bi-hourglass'],
                    ['label'=>'Canceladas',   'val'=>$resumen['canceladas']??0,  'color'=>'var(--danger)',  'icon'=>'bi-x-circle'],
                ];
                $total = (int)($resumen['total'] ?? 0);
                foreach ($estados_info as $ei):
                    $pct = $total > 0 ? round(($ei['val']/$total)*100) : 0;
                ?>
                <div style="margin-bottom:16px;padding:12px;border-radius:10px;background:#fcfcfc;border:1px solid #f3f4f6;transition:transform 0.2s" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:8px">
                        <span style="font-weight:600;display:flex;align-items:center;gap:6px"><i class="bi <?= $ei['icon'] ?>" style="color:<?= $ei['color'] ?>;font-size:15px"></i> <?= $ei['label'] ?></span>
                        <span style="font-weight:800;color:var(--text)"><?= $ei['val'] ?> <span style="color:var(--text-muted);font-weight:500;font-size:12px;margin-left:4px">(<?= $pct ?>%)</span></span>
                    </div>
                    <div style="height:8px;background:var(--border);border-radius:4px;overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,0.05)">
                        <div style="height:100%;width:<?= $pct ?>%;background:<?= $ei['color'] ?>;border-radius:4px;transition:width 1s cubic-bezier(0.4, 0, 0.2, 1);box-shadow:0 0 8px <?= $ei['color'] ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top servicios -->
        <div class="content-card">
            <div class="content-card-header">
                <h3><i class="bi bi-star" style="color:var(--gold);margin-right:8px"></i>Top servicios</h3>
            </div>
            <div class="content-card-body p-0">
                <?php if (empty($svcs_top)): ?>
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">Sin datos aún</div>
                <?php else: ?>
                <?php $max_svc = max(array_column($svcs_top, 'total')); ?>
                <?php foreach (array_slice($svcs_top, 0, 5) as $idx => $sv): ?>
                <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;transition:background 0.2s" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg, var(--gold-soft), rgba(181,138,74,0.25));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:var(--gold-hover);flex-shrink:0;box-shadow:0 2px 6px rgba(181,138,74,0.15)">
                        <?= $idx+1 ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:-0.01em"><?= htmlspecialchars($sv['nombre']) ?></div>
                        <div style="height:5px;background:var(--background);border-radius:2.5px;margin-top:6px;overflow:hidden">
                            <div style="height:100%;width:<?= round(($sv['total']/$max_svc)*100) ?>%;background:linear-gradient(90deg, var(--gold-light), var(--gold));border-radius:2.5px;transition:width 1s"></div>
                        </div>
                    </div>
                    <div style="font-size:15px;font-weight:800;color:var(--gold);flex-shrink:0;background:var(--gold-soft);padding:4px 10px;border-radius:8px"><?= $sv['total'] ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once $base_path . 'includes/nav_footer.php'; ?>

<?php if (!empty($por_dia)): ?>
<script>
(function() {
    const labels = <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d)), $chart_labels)) ?>;
    const data   = <?= json_encode(array_map('intval', $chart_valores)) ?>;

    const ctx = document.getElementById('chartCitasDia').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(181, 138, 74, 0.5)');
    gradient.addColorStop(1, 'rgba(181, 138, 74, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Citas completadas',
                data,
                backgroundColor: gradient,
                borderColor: '#b58a4a',
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#b58a4a',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } },
            plugins: {
                legend: { display: false },
                tooltip: { 
                    backgroundColor: '#111827',
                    titleColor: '#e5e7eb',
                    bodyColor: '#ffffff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: { size: 13, family: "'Inter', sans-serif", weight: '600' },
                    bodyFont: { size: 15, family: "'Inter', sans-serif", weight: 'bold' },
                    callbacks: { label: ctx => ctx.parsed.y + ' citas completadas' } 
                }
            },
            scales: {
                x: { 
                    grid: { display: false, drawBorder: false }, 
                    ticks: { font: { size: 12, family: "'Inter', sans-serif" }, color: '#6b7280', padding: 10 } 
                },
                y: { 
                    beginAtZero: true, 
                    border: { display: false },
                    ticks: { stepSize: 1, font: { size: 12, family: "'Inter', sans-serif" }, color: '#6b7280', padding: 15 }, 
                    grid: { color: '#f3f4f6', drawTicks: false, borderDash: [4, 4] } 
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>

