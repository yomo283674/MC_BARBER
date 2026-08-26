<?php
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);
require_once PROFUNDIDAD . 'models/Turno.php';

$id_cliente = usuarioId();
$turnoModel = new Turno();
$turnoHoy   = $turnoModel->obtenerTurnoHoy($id_cliente);
$antes      = $turnoHoy ? $turnoModel->personasAntes((int)$turnoHoy['posicion'], date('Y-m-d')) : 0;

$pagina_activa = 'turno';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Turno â€” MC Barbería</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">
<?php require_once PROFUNDIDAD . 'includes/nav_cliente.php'; ?>

<div class="page-header">
    <h1>Turno en Tiempo Real</h1>
    <p>Tu posición actual en la cola del día de hoy.</p>
</div>

<?php if ($turnoHoy): ?>
<style>
    .pro-turno-wrapper {
        max-width: 480px;
        margin: 0 auto;
        font-family: 'Inter', sans-serif;
    }
    .pro-turno-card {
        background: linear-gradient(145deg, #111827, #000000);
        border-radius: 28px;
        padding: 50px 20px 60px;
        text-align: center;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(212,175,55,0.2);
    }
    .pro-turno-card::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 40%);
        animation: rotateGlow 20s linear infinite;
        z-index: 0;
        pointer-events: none;
    }
    @keyframes rotateGlow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .pro-turno-content {
        position: relative;
        z-index: 1;
    }
    .pro-turno-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 4px;
        color: #d4af37;
        font-weight: 800;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .pro-turno-numero {
        font-size: 96px;
        font-weight: 900;
        line-height: 1;
        background: linear-gradient(135deg, #d4af37 0%, #fde08b 50%, #d4af37 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 30px;
        filter: drop-shadow(0 10px 15px rgba(212,175,55,0.2));
        letter-spacing: -4px;
    }
    .pro-turno-espera {
        background: rgba(255,255,255,0.03);
        backdrop-filter: blur(10px);
        padding: 12px 24px;
        border-radius: 40px;
        display: inline-block;
        font-size: 14px;
        border: 1px solid rgba(212,175,55,0.3);
        color: #f9fafb;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 0 10px rgba(212,175,55,0.05);
    }
    .pro-turno-espera strong {
        color: #d4af37;
        font-weight: 800;
    }
    .pro-info-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 32px 24px;
        margin-top: -40px;
        position: relative;
        z-index: 2;
        box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.1), 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(229,231,235,0.8);
        width: 92%;
        margin-left: auto;
        margin-right: auto;
    }
    .pro-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-bottom: 1px dashed rgba(229,231,235,1);
    }
    .pro-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .pro-info-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .pro-info-label-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(212,175,55,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(212,175,55,0.2);
        color: #d4af37;
        font-size: 14px;
    }
    .pro-info-value {
        font-weight: 800;
        color: #111827;
        font-size: 15px;
        text-align: right;
    }
</style>

<div class="pro-turno-wrapper">
    <div class="pro-turno-card" id="turnoCard">
        <div class="pro-turno-content">
            <div class="pro-turno-label">
                <i class="bi bi-star-fill" style="font-size: 10px;"></i> 
                Tu Turno Actual 
                <i class="bi bi-star-fill" style="font-size: 10px;"></i>
            </div>
            <div class="pro-turno-numero" id="turnoPos">#<?= $turnoHoy['posicion'] ?></div>
            <div class="pro-turno-espera" id="turnoAntes">
                <?= $antes > 0 ? "Personas antes que tú: <strong>$antes</strong>" : "<strong>¡Es tu turno!</strong> Prepárate" ?>
            </div>
        </div>
    </div>

    <div class="pro-info-card">
        <div class="pro-info-row">
            <span class="pro-info-label">
                <div class="pro-info-label-icon"><i class="bi bi-gem"></i></div> 
                Servicio
            </span>
            <span class="pro-info-value"><?= htmlspecialchars($turnoHoy['servicio']) ?></span>
        </div>
        <div class="pro-info-row">
            <span class="pro-info-label">
                <div class="pro-info-label-icon"><i class="bi bi-person-badge"></i></div> 
                Barbero
            </span>
            <span class="pro-info-value" style="text-transform:capitalize;"><?= htmlspecialchars($turnoHoy['barbero']) ?></span>
        </div>
        <div class="pro-info-row">
            <span class="pro-info-label">
                <div class="pro-info-label-icon"><i class="bi bi-hourglass-split"></i></div> 
                Hora Estimada
            </span>
            <span class="pro-info-value"><?= substr($turnoHoy['hora'],0,5) ?></span>
        </div>
        <div class="pro-info-row" style="background:linear-gradient(135deg, rgba(212,175,55,0.05), rgba(212,175,55,0.1)); padding:16px; border-radius:14px; margin-top:16px; border: 1px solid rgba(212,175,55,0.2);">
            <span class="pro-info-label" style="color:#b58a4a;">
                <div class="pro-info-label-icon" style="background:rgba(255,255,255,0.5);"><i class="bi bi-info-circle"></i></div> 
                Estado
            </span>
            <span class="badge-estado badge-<?= strtolower(str_replace('_','',$turnoHoy['estado'])) ?>" style="font-size:13px; font-weight:800; padding:8px 16px; text-transform:uppercase; letter-spacing:1px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <?= str_replace('_',' ', ucfirst(strtolower($turnoHoy['estado']))) ?>
            </span>
        </div>
    </div>

    <div style="text-align:center; margin-top:30px; font-size:13px; font-weight:600; color:#9ca3af; display:flex; align-items:center; justify-content:center; gap:8px;">
        <div style="width: 24px; height: 24px; border-radius: 50%; background: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
            <i class="bi bi-arrow-repeat" style="font-size:14px;color:#d4af37;animation:spin 2s linear infinite;"></i>
        </div>
        Actualizando en tiempo real
    </div>
</div>

<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<script>
    // Auto-refresh cada 30 segundos (sin recargar toda la pagina)
    function actualizarTurno() {
        fetch('../../controllers/cliente/turnoController.php?accion=estado')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.posicion) {
                    document.getElementById('turnoPos').textContent = '#' + data.posicion;
                    document.getElementById('turnoAntes').innerHTML = data.antes > 0
                        ? 'Personas antes que tú: <strong>' + data.antes + '</strong>'
                        : '<strong>¡Es tu turno!</strong> Prepárate';
                } else {
                    location.reload();
                }
            })
            .catch(function() {});
    }
    setInterval(actualizarTurno, 30000);
</script>

<?php else: ?>
<div style="max-width:480px;margin:0 auto;">
    <div class="empty-state" style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:48px 24px;">
        <i class="bi bi-people" style="color:var(--border);font-size:56px;"></i>
        <h3>No tienes citas para hoy</h3>
        <p>Cuando tengas una cita programada para hoy, podrás ver tu posición en la cola aquí.</p>
        <a href="agendar.php" class="btn btn-primary" style="margin-top:20px;">
            <i class="bi bi-calendar-plus"></i> Agendar cita
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once PROFUNDIDAD . 'includes/nav_footer.php'; ?>
</body>
</html>

