<?php
define('PROFUNDIDAD', '../../');
require_once PROFUNDIDAD . 'includes/auth_guard.php';
require_once PROFUNDIDAD . 'includes/session_timeout.php';
verificarRol(['CLIENTE']);
require_once PROFUNDIDAD . 'models/Servicio.php';

$servicioModel = new Servicio();
$servicios = $servicioModel->obtenerActivos();

$pagina_activa = 'servicios';
$titulo_pagina = 'Servicios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios â€” MC Barbería</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>
<body class="dashboard-body">
<?php require_once PROFUNDIDAD . 'includes/nav_cliente.php'; ?>
<div class="page-header">
    <h1>Catálogo de Servicios</h1>
    <p>Todos los servicios disponibles en MC Barbería.</p>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;">
    <?php if (empty($servicios)): ?>
        <div class="empty-state" style="grid-column:1/-1;"><i class="bi bi-scissors"></i><h3>Sin servicios disponibles</h3></div>
    <?php else: ?>
    <?php foreach ($servicios as $s): ?>
    <div style="background: linear-gradient(145deg, #1f2937, #111827); border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid rgba(212,175,55,0.15); overflow: hidden; display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;" 
         onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.2)'; this.style.borderColor='rgba(212,175,55,0.4)';" 
         onmouseout="this.style.transform=''; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.1)'; this.style.borderColor='rgba(212,175,55,0.15)';">
        
        <?php if (!empty($s['imagen'])): ?>
            <div style="position: relative; height: 200px; overflow: hidden; background: #000;">
                <div style="position: absolute; inset: 0; background-image: url('<?= PROFUNDIDAD . 'public/uploads/servicios/' . htmlspecialchars($s['imagen']) ?>'); background-size: cover; background-position: center; transition: transform 0.5s ease-out; opacity: 0.9;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'"></div>
                <!-- Gradient overlay to blend image with dark card smoothly -->
                <div style="position: absolute; inset: 0; background: linear-gradient(to top, #1f2937 0%, transparent 60%); pointer-events: none;"></div>
            </div>
        <?php else: ?>
            <div style="height: 200px; background: radial-gradient(circle at center, rgba(212,175,55,0.15) 0%, #111827 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                <i class="bi bi-scissors" style="font-size: 64px; color: #d4af37; filter: drop-shadow(0 4px 15px rgba(212,175,55,0.2)); opacity: 0.8;"></i>
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 60%; background: linear-gradient(to top, #1f2937 0%, transparent 100%);"></div>
            </div>
        <?php endif; ?>
        
        <div style="padding: 24px; flex-grow: 1; display: flex; flex-direction: column; z-index: 1; position: relative; margin-top: -10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <div style="font-size: 22px; font-weight: 800; color: #f9fafb; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(212,175,55,0.1); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(212,175,55,0.2);">
                        <i class="bi bi-gem" style="font-size: 14px; color: #d4af37;"></i>
                    </div>
                    <?= htmlspecialchars($s['nombre']) ?>
                </div>
            </div>
            
            <div style="font-size: 14px; color: #9ca3af; margin-bottom: 24px; min-height: 42px; line-height: 1.6; flex-grow: 1; padding-left: 38px;">
                <?= htmlspecialchars($s['descripcion'] ?? 'Servicio profesional de barbería.') ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; margin-bottom: 20px;">
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; text-shadow: 0 2px 10px rgba(212,175,55,0.2); line-height: 1; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-tag-fill" style="color: rgba(212,175,55,0.3); font-size: 18px;"></i>
                    <span><span style="font-size: 14px; color: #d4af37; vertical-align: super; margin-right: 2px;">$</span><?= number_format($s['precio'],0,',','.') ?></span>
                </div>
                <div style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 8px 14px; border-radius: 20px; color: #d1d5db; display: flex; align-items: center; gap: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);">
                    <i class="bi bi-hourglass-split" style="color: #d4af37; font-size: 14px;"></i> <?= $s['duracion_min'] ?> min
                </div>
            </div>
            
            <a href="agendar.php" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px; background: rgba(212,175,55,0.05); color: #d4af37; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; padding: 14px; border-radius: 12px; border: 1px solid rgba(212,175,55,0.4); text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.background='linear-gradient(135deg, #d4af37, #b58a4a)'; this.style.color='#000'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(212,175,55,0.2)';" onmouseout="this.style.background='rgba(212,175,55,0.05)'; this.style.color='#d4af37'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="bi bi-calendar2-check-fill" style="font-size: 16px;"></i> Agendar Turno
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once PROFUNDIDAD . 'includes/nav_footer.php'; ?>
</body>
</html>

