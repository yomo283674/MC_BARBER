<?php
/**
 * views/barbero/clientes.php
 * Lista de clientes únicos que han tenido citas con este barbero.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'config/database.php';

$id_barbero = (int)$_SESSION['usuario_id'];

// Clientes únicos con resumen de citas
$stmt = $conn->prepare(
    "SELECT u.id_usuario, u.nombre, u.email, u.telefono,
            COUNT(c.id_cita) AS total_citas,
            SUM(c.estado = 'COMPLETADA') AS completadas,
            SUM(c.estado = 'CANCELADA')  AS canceladas,
            MAX(c.fecha) AS ultima_visita,
            SUM(s.precio * (c.estado = 'COMPLETADA')) AS ingresos
    FROM citas c
    INNER JOIN usuarios u ON c.id_cliente = u.id_usuario
    INNER JOIN servicios s ON c.id_servicio = s.id_servicio
    WHERE c.id_barbero = ?
    GROUP BY u.id_usuario
    ORDER BY ultima_visita DESC"
);
$stmt->bind_param('i', $id_barbero);
$stmt->execute();
$clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$busqueda      = strtolower(trim($_GET['q'] ?? ''));
if ($busqueda) {
    $clientes = array_filter($clientes, fn($c) =>
        str_contains(strtolower($c['nombre']), $busqueda) ||
        str_contains(strtolower($c['email']),  $busqueda)
    );
}

$pagina_activa = 'clientes';
$titulo_pagina = 'Mis Clientes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Clientes &mdash; Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'includes/nav_barbero.php'; ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
            Mis Clientes <i class="bi bi-people-fill" style="color:var(--gold);font-size:24px;"></i>
        </h1>
        <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Historial de clientes que han agendado contigo.</p>
    </div>
    <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
            placeholder="Buscar por nombre o email&hellip;"
            style="height:40px;border:1px solid var(--border);border-radius:8px;padding:0 14px;font-size:13px;width:240px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
    </form>
</div>

<!-- KPIs -->
<?php
$total_clientes  = count($clientes);
$total_ingresos  = array_sum(array_column($clientes, 'ingresos'));
$total_citas_sum = array_sum(array_column($clientes, 'total_citas'));
?>
<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_clientes ?></div>
            <div class="stat-label">Clientes únicos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_citas_sum ?></div>
            <div class="stat-label">Citas totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($total_ingresos, 0, ',', '.') ?></div>
            <div class="stat-label">Ingresos totales</div>
        </div>
    </div>
</div>

<!-- Tabla de clientes -->
<div class="content-card">
    <div class="content-card-header">
        <h3><i class="bi bi-people" style="color:var(--gold);margin-right:8px"></i>Clientes</h3>
        <span style="font-size:12px;color:var(--text-muted)"><?= count($clientes) ?> clientes</span>
    </div>
    <div class="content-card-body p-0">
        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h3>Sin clientes aún</h3>
                <p><?= $busqueda ? 'No se encontraron clientes con esa búsqueda.' : 'Aún no tienes clientes que hayan agendado contigo.' ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Citas</th>
                            <th>Completadas</th>
                            <th>Ingresos</th>
                            <th>Última visita</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cl): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, var(--gold-soft), rgba(181,138,74,0.25));
                                                display:flex;align-items:center;justify-content:center;
                                                font-weight:800;color:var(--gold-hover);font-size:15px;flex-shrink:0;box-shadow:0 2px 8px rgba(181,138,74,0.15)">
                                        <?= strtoupper(mb_substr($cl['nombre'],0,1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:700;font-size:14px;color:var(--text);margin-bottom:2px;letter-spacing:-0.01em;"><?= htmlspecialchars($cl['nombre']) ?></div>
                                        <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:4px">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($cl['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($cl['telefono']): ?>
                                <div style="font-size:13px;color:var(--text-light);display:flex;align-items:center;gap:6px">
                                    <i class="bi bi-telephone-fill" style="color:var(--gold-light)"></i> <?= htmlspecialchars($cl['telefono']) ?>
                                </div>
                                <?php else: ?>
                                <span style="font-size:12px;color:var(--text-muted);font-style:italic">No registrado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;background:var(--background);border:1px solid var(--border);border-radius:8px;font-weight:700;font-size:13px;color:var(--text)">
                                    <?= $cl['total_citas'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;background:rgba(22,163,74,0.1);color:var(--success);border-radius:6px;font-size:12px;font-weight:700">
                                        <i class="bi bi-check-circle-fill"></i> <?= $cl['completadas'] ?>
                                    </span>
                                    <?php if ($cl['canceladas'] > 0): ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;background:rgba(220,38,38,0.1);color:var(--danger);border-radius:6px;font-size:12px;font-weight:700">
                                        <i class="bi bi-x-circle-fill"></i> <?= $cl['canceladas'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:800;color:var(--gold-hover);font-size:14.5px;display:flex;align-items:center;gap:2px">
                                    <span style="color:var(--gold-light);font-size:14px">$</span><?= number_format($cl['ingresos'] ?? 0, 0, ',', '.') ?>
                                </div>
                            </td>
                            <td style="font-size:13px;font-weight:500;color:var(--text-light)">
                                <div style="display:flex;align-items:center;gap:6px">
                                    <i class="bi bi-calendar3" style="color:var(--text-muted)"></i>
                                    <?= $cl['ultima_visita'] ? date('d/m/Y', strtotime($cl['ultima_visita'])) : '&mdash;' ?>
                                </div>
                            </td>
                            <td>
                                <a href="cliente_historial.php?id=<?= $cl['id_usuario'] ?>" class="btn-table-action" title="Ver Historial">
                                    <i class="bi bi-clock-history"></i> Historial
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once $base_path . 'includes/nav_footer.php'; ?>
</body>
</html>

