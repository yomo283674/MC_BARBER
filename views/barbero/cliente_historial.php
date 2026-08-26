<?php
/**
 * views/barbero/cliente_historial.php
 * Muestra el historial detallado de citas de un cliente con este barbero.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'config/database.php';

$id_barbero = (int)$_SESSION['usuario_id'];
$id_cliente = (int)($_GET['id'] ?? 0);

if (!$id_cliente) {
    header('Location: clientes.php');
    exit;
}

// 1. Obtener info básica del cliente
$stmt_cli = $conn->prepare("SELECT nombre, email, telefono FROM usuarios WHERE id_usuario = ? AND id_rol = 3 LIMIT 1");
$stmt_cli->bind_param('i', $id_cliente);
$stmt_cli->execute();
$cliente_info = $stmt_cli->get_result()->fetch_assoc();
$stmt_cli->close();

if (!$cliente_info) {
    $_SESSION['flash_error'] = 'Cliente no encontrado.';
    header('Location: clientes.php');
    exit;
}

// 2. Obtener historial de citas con este barbero
$stmt_citas = $conn->prepare(
    "SELECT c.id_cita, c.fecha, c.hora, c.estado, 
            s.nombre AS servicio, s.precio, s.duracion_min
     FROM citas c
     INNER JOIN servicios s ON c.id_servicio = s.id_servicio
     WHERE c.id_cliente = ? AND c.id_barbero = ?
     ORDER BY c.fecha DESC, c.hora DESC"
);
$stmt_citas->bind_param('ii', $id_cliente, $id_barbero);
$stmt_citas->execute();
$citas = $stmt_citas->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_citas->close();

$pagina_activa = 'clientes';
$titulo_pagina = 'Historial de ' . htmlspecialchars(explode(' ', $cliente_info['nombre'])[0]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> — Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
</head>
<body class="dashboard-body">

<?php require_once $base_path . 'views/layouts/sidebar_barbero.php'; ?>

<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <h1 class="topbar-title">Historial del Cliente</h1>
        </div>
        <div class="topbar-right">
            <span class="topbar-greeting">Hola, <strong><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]) ?></strong></span>
        </div>
    </header>

    <div class="page-content">
        <div style="margin-bottom: 24px;">
            <a href="clientes.php" style="color: var(--gold); background: var(--gold-soft); padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(181,138,74,0.15); border: 1px solid rgba(181,138,74,0.3);"
               onmouseover="this.style.background='var(--gold)'; this.style.color='#fff'; this.style.transform='translateX(-4px)'; this.style.boxShadow='0 4px 12px rgba(181,138,74,0.3)';"
               onmouseout="this.style.background='var(--gold-soft)'; this.style.color='var(--gold)'; this.style.transform='translateX(0)'; this.style.boxShadow='0 2px 8px rgba(181,138,74,0.15)';">
                <i class="bi bi-arrow-left" style="font-size: 16px;"></i> Volver a Clientes
            </a>
        </div>

        <div class="page-header" style="margin-bottom:24px;">
            <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
                Historial del Cliente <i class="bi bi-clock-history" style="color:var(--gold);font-size:24px;"></i>
            </h1>
            <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Revisa las citas pasadas y el perfil de este cliente.</p>
        </div>

        <div class="content-card" style="border-top: 4px solid var(--gold); background: linear-gradient(135deg, #ffffff 0%, #fcfcfc 100%); margin-bottom: 30px;">
            <div class="content-card-body" style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--gold-soft); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; color: var(--gold); border: 2px solid var(--gold); box-shadow: 0 4px 12px rgba(181,138,74,0.15);">
                    <?= strtoupper(mb_substr($cliente_info['nombre'], 0, 1)) ?>
                </div>
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 6px; letter-spacing: -0.02em;">
                        <?= htmlspecialchars($cliente_info['nombre']) ?>
                    </h1>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; color: var(--text-muted); font-weight: 500;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-envelope-at" style="color: var(--gold);"></i> <?= htmlspecialchars($cliente_info['email']) ?>
                        </span>
                        <?php if ($cliente_info['telefono']): ?>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-telephone" style="color: var(--gold);"></i> <?= htmlspecialchars($cliente_info['telefono']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card" style="box-shadow: var(--shadow-lg);">
            <div class="content-card-header" style="background: rgba(181, 138, 74, 0.03);">
                <h3 style="font-size: 16px; font-weight: 700;"><i class="bi bi-clock-history" style="color:var(--gold);margin-right:10px; font-size: 18px;"></i>Detalle de Citas</h3>
                <span style="font-size:12px; font-weight: 600; color: var(--gold); background: var(--gold-soft); padding: 4px 10px; border-radius: 20px;"><?= count($citas) ?> cita(s) en total</span>
            </div>
            <div class="content-card-body p-0">
                <?php if (empty($citas)): ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h3>Sin historial</h3>
                        <p>Este cliente no tiene citas registradas contigo.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Servicio</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas as $c): ?>
                                <tr>
                                    <td style="font-weight:600">
                                        <?= date('d/m/Y', strtotime($c['fecha'])) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock" style="color:var(--text-muted);font-size:12px"></i> 
                                        <?= substr($c['hora'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($c['servicio']) ?></div>
                                        <div style="font-size:11px;color:var(--text-muted)"><?= $c['duracion_min'] ?> min</div>
                                    </td>
                                    <td style="font-weight:700;color:var(--gold)">
                                        $<?= number_format($c['precio'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="badge-estado badge-<?= strtolower($c['estado']) ?>">
                                            <?= ucfirst(strtolower($c['estado'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
</body>
</html>
