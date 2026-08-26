<?php
/**
 * views/admin/reportes.php
 * Reportes y estadísticas â€” Dashboard Administrador.
 */
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['ADMINISTRADOR']);

require_once PROFUNDIDAD . 'controllers/admin/estadisticasController.php';
global $conn;

$ctrl = new EstadisticasController($conn);
$kpis = $ctrl->kpis();
$ingresos_mes = $ctrl->ingresosPorMes();
$citas_estado = $ctrl->citasPorEstado();
$barberos_rend = $ctrl->rendimientoPorBarbero();
$servicios_pop = $ctrl->serviciosPopulares(8);

$pagina_activa = 'reportes';
$titulo_pagina = 'Reportes y Estadísticas';
$base_path = PROFUNDIDAD;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Administrador | MC Barber</title>
    <meta name="description" content="Reportes y estadísticas del sistema MC Barber">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= PROFUNDIDAD ?>public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .kpi-hero {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .kpi-hero-card {
            background: var(--surface);
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .kpi-hero-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(181, 138, 74, 0.15);
            border-color: rgba(181, 138, 74, 0.2);
        }

        .kpi-hero-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #fef08a);
            opacity: .15;
            transition: transform 0.5s ease;
        }

        .kpi-hero-card:hover::before {
            transform: scale(1.1);
            opacity: .2;
        }

        .kpi-hero-val {
            font-size: 36px;
            font-weight: 900;
            color: #111827;
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .kpi-hero-lbl {
            font-size: 12.5px;
            color: #6b7280;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
        }

        .kpi-hero-icon {
            position: absolute;
            top: 24px;
            right: 24px;
            font-size: 24px;
            color: var(--gold);
        }

        .kpi-hero-trend {
            font-size: 12.5px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
        }

        .chart-box {
            background: var(--surface);
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
        }

        .chart-box:hover {
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.08);
        }

        .chart-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #111827;
        }

        .content-card {
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            color: #6b7280;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f9fafb;
        }

        .data-table th:first-child {
            border-top-left-radius: 12px;
        }

        .data-table th:last-child {
            border-top-right-radius: 12px;
        }

        .data-table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            color: #374151;
            font-weight: 500;
        }

        .data-table tr:hover td {
            background: rgba(181, 138, 74, 0.03);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .progress-bar-wrap {
            height: 8px;
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--gold), #d97706);
        }
    </style>
</head>

<body class="dashboard-body">

    <?php require_once PROFUNDIDAD . 'includes/nav_admin.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <?php
        $meses_es = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];
        $mes_actual = $meses_es[date('F')] ?? date('F');
        ?>
        <h1>Resumen del Negocio</h1>
        <p><?= $mes_actual . ' ' . date('Y') ?> &mdash; Métricas del negocio</p>
        <div class="stats-grid">
            <!-- Ingresos -->
            <div class="stat-card"
                style="border: 1px solid rgba(181, 138, 74, 0.2); background: linear-gradient(145deg, #ffffff, #fafafa); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(181, 138, 74, 0.15)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.04)'">
                <div class="stat-info">
                    <div class="stat-value"
                        style="font-size:32px; font-weight:800; color:#111827; letter-spacing:-1px;">
                        $<?= number_format($kpis['ingresos_mes'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label"
                        style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                        Ingresos del mes</div>
                    <div
                        style="font-size:12px; color:#10b981; margin-top:8px; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-graph-up-arrow"></i> Citas completadas
                    </div>
                </div>
                <div class="stat-icon"
                    style="background:linear-gradient(135deg, rgba(181,138,74,0.1), rgba(181,138,74,0.2)); width:56px; height:56px; border-radius:16px;">
                    <i class="bi bi-cash-stack" style="color:var(--gold); font-size:24px;"></i>
                </div>
            </div>

            <!-- Citas -->
            <div class="stat-card"
                style="border: 1px solid rgba(0,0,0,0.04); background: linear-gradient(145deg, #ffffff, #fafafa); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.08)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.04)'">
                <div class="stat-info">
                    <div class="stat-value"
                        style="font-size:32px; font-weight:800; color:#111827; letter-spacing:-1px;">
                        <?= $kpis['citas_mes'] ?? 0 ?></div>
                    <div class="stat-label"
                        style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                        Citas este mes</div>
                    <div
                        style="font-size:12px; color:#b58a4a; margin-top:8px; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-calendar-check"></i> Total generadas
                    </div>
                </div>
                <div class="stat-icon amber"
                    style="background:linear-gradient(135deg, rgba(217,119,6,0.08), rgba(217,119,6,0.15)); width:56px; height:56px; border-radius:16px;">
                    <i class="bi bi-calendar-event" style="color:#d97706; font-size:24px;"></i>
                </div>
            </div>

            <!-- Clientes -->
            <div class="stat-card"
                style="border: 1px solid rgba(0,0,0,0.04); background: linear-gradient(145deg, #ffffff, #fafafa); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.08)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.04)'">
                <div class="stat-info">
                    <div class="stat-value"
                        style="font-size:32px; font-weight:800; color:#111827; letter-spacing:-1px;">
                        <?= $kpis['clientes_nuevos'] ?? 0 ?></div>
                    <div class="stat-label"
                        style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                        Clientes nuevos</div>
                    <div
                        style="font-size:12px; color:#3b82f6; margin-top:8px; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-people"></i> Registrados este mes
                    </div>
                </div>
                <div class="stat-icon blue"
                    style="background:linear-gradient(135deg, rgba(59,130,246,0.08), rgba(59,130,246,0.15)); width:56px; height:56px; border-radius:16px;">
                    <i class="bi bi-person-plus" style="color:#3b82f6; font-size:24px;"></i>
                </div>
            </div>

            <!-- Tasa Cancelación -->
            <div class="stat-card"
                style="border: 1px solid rgba(0,0,0,0.04); background: linear-gradient(145deg, #ffffff, #fafafa); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(220, 38, 38, 0.15)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.04)'">
                <div class="stat-info">
                    <div class="stat-value"
                        style="font-size:32px; font-weight:800; color:#111827; letter-spacing:-1px;">
                        <?= $kpis['tasa_cancelacion'] ?? 0 ?>%</div>
                    <div class="stat-label"
                        style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                        Tasa cancelación</div>
                    <div
                        style="font-size:12px; color:#9ca3af; margin-top:8px; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-info-circle"></i> Meta: &lt;20%
                    </div>
                </div>
                <div class="stat-icon red"
                    style="background:linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.15)); width:56px; height:56px; border-radius:16px;">
                    <i class="bi bi-x-circle" style="color:#ef4444; font-size:24px;"></i>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="grid-2" style="margin-bottom:24px;">
            <!-- Ingresos por mes -->
            <div class="chart-box">
                <div class="chart-title">
                    <i class="bi bi-graph-up-arrow" style="color:var(--gold); font-size:18px;"></i>
                    Ingresos últimos 6 meses
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>
            <!-- Citas por estado (dona) -->
            <div class="chart-box">
                <div class="chart-title">
                    <i class="bi bi-pie-chart-fill" style="color:var(--gold); font-size:18px;"></i>
                    Distribución de citas por estado
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartEstados"></canvas>
                </div>
            </div>
        </div>

        <!-- Tablas inferiores -->
        <div class="grid-2">
            <!-- Rendimiento por barbero -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-person-badge-fill me-2" style="color:var(--gold)"></i>Rendimiento barberos (30
                        días)</h3>
                </div>
                <div class="content-card-body p-0" style="overflow-x:auto">
                    <?php if (empty($barberos_rend)): ?>
                        <div style="padding:40px; text-align:center; color:var(--text-muted)">
                            <i class="bi bi-person-x" style="font-size:36px; opacity:.3"></i>
                            <p style="margin-top:10px">Sin datos disponibles</p>
                        </div>
                    <?php else: ?>
                        <?php $max_comp = max(array_column($barberos_rend, 'completadas') ?: [1]); ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Barbero</th>
                                    <th>Completadas</th>
                                    <th>Canceladas</th>
                                    <th>Ingresos</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barberos_rend as $br): ?>
                                    <tr>
                                        <td style="font-weight:700; color:#111827"><?= htmlspecialchars($br['barbero']) ?></td>
                                        <td style="color:#10b981; font-weight:700"><?= $br['completadas'] ?></td>
                                        <td style="color:#ef4444; font-weight:600"><?= $br['canceladas'] ?></td>
                                        <td style="font-weight:800; color:var(--gold)">
                                            $<?= number_format($br['ingresos'], 0, ',', '.') ?></td>
                                        <td style="min-width:100px">
                                            <div class="progress-bar-wrap">
                                                <div class="progress-bar-fill"
                                                    style="width:<?= $max_comp > 0 ? round(($br['completadas'] / $max_comp) * 100) : 0 ?>%; box-shadow:0 2px 4px rgba(181,138,74,0.3);">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Servicios populares -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-star-fill me-2" style="color:var(--gold)"></i>Servicios más solicitados</h3>
                </div>
                <div class="content-card-body">
                    <?php if (empty($servicios_pop)): ?>
                        <div style="padding:40px; text-align:center; color:var(--text-muted)">
                            <i class="bi bi-scissors" style="font-size:36px; opacity:.3"></i>
                            <p style="margin-top:10px">Sin datos disponibles</p>
                        </div>
                    <?php else: ?>
                        <?php $max_sv = max(array_column($servicios_pop, 'total_citas') ?: [1]); ?>
                        <?php foreach ($servicios_pop as $sv): ?>
                            <div style="margin-bottom:16px; padding:12px 16px; border-radius:14px; background:#ffffff; border:1px solid rgba(0,0,0,0.03); box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1)"
                                onmouseover="this.style.boxShadow='0 8px 24px rgba(181,138,74,0.12)'; this.style.transform='translateY(-3px)'; this.style.borderColor='rgba(181,138,74,0.2)';"
                                onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.02)'; this.style.transform='translateY(0)'; this.style.borderColor='rgba(0,0,0,0.03)';">
                                <div
                                    style="display:flex; justify-content:space-between; margin-bottom:12px; align-items:center;">
                                    <span
                                        style="font-size:14.5px; font-weight:800; color:#1f2937; letter-spacing:-0.01em;"><?= htmlspecialchars($sv['nombre']) ?></span>
                                    <span
                                        style="font-size:12px; color:#4b5563; font-weight:700; background:rgba(181,138,74,0.08); border: 1px solid rgba(181,138,74,0.1); padding:4px 10px; border-radius:20px; letter-spacing:0.02em;">
                                        <i class="bi bi-graph-up-arrow me-1" style="color:var(--gold)"></i>
                                        <?= $sv['total_citas'] ?> &mdash; <span
                                            style="color:var(--gold)">$<?= number_format($sv['precio'], 0, ',', '.') ?></span>
                                    </span>
                                </div>
                                <div class="progress-bar-wrap" style="height:6px; background:#f3f4f6;">
                                    <div class="progress-bar-fill"
                                        style="width:<?= $max_sv > 0 ? round(($sv['total_citas'] / $max_sv) * 100) : 0 ?>%; background:linear-gradient(90deg, #d97706, #fde68a); box-shadow:0 0 10px rgba(217,119,6,0.4);">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /page-content -->
    </div><!-- /main-content -->

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('open');
        }
        function closeSidebar() {
            document.querySelector('.sidebar').classList.remove('open');
            document.querySelector('.sidebar-overlay').classList.remove('open');
        }

        // --- Chart: Ingresos por mes ---
        const ingresosData = <?= json_encode($ingresos_mes) ?>;
        const meses = ingresosData.map(r => {
            const [y, m] = r.mes.split('-');
            return new Date(y, m - 1).toLocaleString('es', { month: 'short', year: '2-digit' }).toUpperCase();
        });
        const montos = ingresosData.map(r => parseFloat(r.ingresos) || 0);
        new Chart(document.getElementById('chartIngresos'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: montos,
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, '#d97706'); // Naranja fuerte arriba
                        gradient.addColorStop(1, '#fde68a'); // Amarillo dorado suave abajo
                        return gradient;
                    },
                    borderColor: 'transparent',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#f9fafb',
                        bodyColor: '#f9fafb',
                        padding: 14,
                        cornerRadius: 12,
                        titleFont: { size: 12, family: "'Inter', sans-serif", weight: '700' },
                        bodyFont: { size: 14, family: "'Inter', sans-serif", weight: '600' },
                        callbacks: { label: ctx => 'Ingresos: $' + ctx.raw.toLocaleString() }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        border: { display: false, dash: [4, 4] },
                        grid: { color: 'rgba(0,0,0,0.03)', drawTicks: false, tickLength: 0 },
                        ticks: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11, weight: '500' }, padding: 12, callback: v => '$' + v.toLocaleString() }
                    },
                    x: {
                        border: { display: false },
                        grid: { display: false, drawTicks: false },
                        ticks: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11, weight: '600', letterSpacing: 1 }, padding: 12 }
                    }
                }
            }
        });

        // --- Chart: Citas por estado (dona) ---
        const rawEstadosData = <?= json_encode($citas_estado) ?>;
        const allEstados = ['PENDIENTE', 'ACEPTADA', 'COMPLETADA', 'CANCELADA', 'REPROGRAMADA'];
        const estLabels = [];
        const estTotals = [];
        const estColorsMap = {
            PENDIENTE: '#f59e0b',
            ACEPTADA: '#10b981',
            COMPLETADA: '#3b82f6',
            CANCELADA: '#ef4444',
            REPROGRAMADA: '#b58a4a'
        };

        allEstados.forEach(est => {
            estLabels.push(est);
            const found = rawEstadosData.find(r => r.estado === est);
            estTotals.push(found ? parseInt(found.total) : 0);
        });

        new Chart(document.getElementById('chartEstados'), {
            type: 'doughnut',
            data: {
                labels: estLabels,
                datasets: [{
                    data: estTotals,
                    backgroundColor: estLabels.map(e => estColorsMap[e]),
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'center',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            padding: 15,
                            font: { family: "'Inter', sans-serif", size: 10.5, weight: '600' },
                            color: '#4b5563'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#f9fafb',
                        bodyColor: '#f9fafb',
                        padding: 12,
                        cornerRadius: 12,
                        bodyFont: { size: 13, family: "'Inter', sans-serif" },
                        callbacks: {
                            label: function (context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) label += context.parsed + ' citas';
                                return label;
                            }
                        }
                    }
                },
                cutout: '65%',
                layout: {
                    padding: { left: 0, right: 0, top: 10, bottom: 0 }
                }
            }
        });

        const p = new URLSearchParams(window.location.search);
        if (p.get('expired') === '1') {
            Swal.fire({ icon: 'warning', title: 'Sesión expirada', text: 'Tu sesión cerró por inactividad.', confirmButtonColor: '#b58a4a' });
        }
    </script>
</body>

</html>