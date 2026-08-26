<?php
/**
 * views/barbero/citas.php
 * Todas las citas del barbero con filtros de estado.
 */
$base_path = '../../';
require_once $base_path . 'includes/auth_guard.php';
require_once $base_path . 'includes/session_timeout.php';
verificarRol(['BARBERO'], $base_path);
require_once $base_path . 'models/Cita.php';

$id_barbero = (int) $_SESSION['usuario_id'];
$citaModel = new Cita();

$filtro = $_GET['estado'] ?? 'todas';
$todasCitas = $citaModel->obtenerPorBarbero($id_barbero);

$citas = match ($filtro) {
    'pendientes' => array_filter($todasCitas, fn($c) => $c['estado'] === 'PENDIENTE'),
    'aceptadas' => array_filter($todasCitas, fn($c) => $c['estado'] === 'ACEPTADA'),
    'completadas' => array_filter($todasCitas, fn($c) => $c['estado'] === 'COMPLETADA'),
    'canceladas' => array_filter($todasCitas, fn($c) => $c['estado'] === 'CANCELADA'),
    default => $todasCitas,
};

// Flash
$flash_tipo = $_SESSION['flash_tipo'] ?? '';
$flash_msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_msg']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Barbero | MC Barber</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_path ?>public/css/components.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base_path ?>public/js/swal-custom.js?v=<?= time() ?>"></script>
</head>

<body class="dashboard-body">

    <?php require_once $base_path . 'views/layouts/sidebar_barbero.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <h1 class="topbar-title">Mis Citas</h1>
            </div>
            <div class="topbar-right">
                <span class="topbar-greeting">Hola,
                    <strong><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]) ?></strong></span>
            </div>
        </header>

        <div class="page-content">
            <?php if ($flash_msg): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: '<?= $flash_tipo ?>', title: '<?= $flash_tipo === 'success' ? '¡Listo!' : 'Aviso' ?>',
                            text: '<?= addslashes($flash_msg) ?>', confirmButtonColor: '#b58a4a', timer: 3500, timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>

            <div class="page-header">
                <h1 style="font-size:26px;font-weight:800;display:flex;align-items:center;gap:12px;letter-spacing:-0.02em;color:#111827">
                    Gestión de Citas <i class="bi bi-journal-text" style="color:var(--gold);font-size:24px;"></i>
                </h1>
                <p style="color:var(--text-muted); margin-top:4px; font-size:15px;">Acepta, cancela o reprograma tus citas asignadas.</p>
            </div>

            <!-- Filtros -->
            <div style="display:inline-flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; padding: 6px; background: #f8f9fa; border-radius: 16px; border: 1px solid #f3f4f6; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                <?php
                $filtros = [
                    'todas' => ['label' => 'Todas', 'icon' => 'bi-list-ul'],
                    'pendientes' => ['label' => 'Pendientes', 'icon' => 'bi-hourglass-split'],
                    'aceptadas' => ['label' => 'Aceptadas', 'icon' => 'bi-check2-circle'],
                    'completadas' => ['label' => 'Completadas', 'icon' => 'bi-star'],
                    'canceladas' => ['label' => 'Canceladas', 'icon' => 'bi-x-circle']
                ];
                foreach ($filtros as $key => $data):
                    $isActive = ($filtro === $key);
                    if ($isActive) {
                        $bg = '#111827'; // Dark background for active
                        $textColor = '#ffffff';
                        $iconColor = 'var(--gold)';
                        $shadow = '0 2px 8px rgba(0,0,0,0.15)';
                    } else {
                        $bg = 'transparent';
                        $textColor = '#6b7280';
                        $iconColor = '#9ca3af';
                        $shadow = 'none';
                    }
                    ?>
                    <a href="?estado=<?= $key ?>"
                        style="text-decoration:none; padding: 8px 18px; border-radius: 12px; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; background: <?= $bg ?>; color: <?= $textColor ?>; box-shadow: <?= $shadow ?>; letter-spacing: 0.2px;"
                        onmouseover="if(!<?= $isActive ? 'true' : 'false' ?>) { this.style.background='#ffffff'; this.style.color='#111827'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.05)'; }"
                        onmouseout="if(!<?= $isActive ? 'true' : 'false' ?>) { this.style.background='transparent'; this.style.color='#6b7280'; this.style.boxShadow='none'; }">
                        <i class="bi <?= $data['icon'] ?>" style="color: <?= $iconColor ?>; font-size: 15px;"></i>
                        <?= $data['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Tabla de citas -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3><i class="bi bi-calendar-check" style="color:var(--gold);margin-right:8px;"></i>
                        <?= $filtros[$filtro]['label'] ?>
                    </h3>
                    <span style="font-size:13px;color:var(--text-light);"><?= count($citas) ?>resultados</span>
                </div>
                <div class="content-card-body" style="padding:0;">
                    <?php if (empty($citas)): ?>
                        <div class="empty-state"><i class="bi bi-calendar-x"></i>
                            <h3>Sin citas</h3>
                            <p>No hay citas en esta categoria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper" style="background: transparent; padding: 0 10px;">
                            <style>
                                .premium-table {
                                    width: 100%;
                                    border-collapse: separate;
                                    border-spacing: 0 12px;
                                }

                                .premium-table th {
                                    font-size: 11px;
                                    font-weight: 700;
                                    color: var(--text-muted);
                                    text-transform: uppercase;
                                    padding: 0 16px 4px;
                                    border: none;
                                    letter-spacing: 0.8px;
                                }

                                .premium-table td {
                                    background: var(--white);
                                    padding: 18px 16px;
                                    border: none;
                                    vertical-align: middle;
                                }

                                .premium-table tr td:first-child {
                                    border-radius: 12px 0 0 12px;
                                    border-left: 3px solid transparent;
                                    transition: all 0.3s ease;
                                }

                                .premium-table tr td:last-child {
                                    border-radius: 0 12px 12px 0;
                                }

                                .premium-table tbody tr {
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
                                    transition: all 0.3s ease;
                                }

                                .premium-table tbody tr:hover {
                                    box-shadow: 0 8px 20px rgba(181, 138, 74, 0.12);
                                    transform: translateY(-3px);
                                }

                                .premium-table tbody tr:hover td:first-child {
                                    border-left-color: var(--gold);
                                }

                                .action-btn {
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    width: 38px;
                                    height: 38px;
                                    border-radius: 12px;
                                    font-size: 16px;
                                    border: none;
                                    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                                    cursor: pointer;
                                    background: #f3f4f6;
                                }

                                .action-btn:active {
                                    transform: scale(0.92);
                                }

                                .action-btn.btn-accept {
                                    background: #e6f4ea;
                                    color: #1e8e3e;
                                }

                                .action-btn.btn-accept:hover {
                                    background: #d4edda;
                                    color: #155724;
                                    box-shadow: 0 4px 12px rgba(30, 142, 62, 0.2);
                                    transform: translateY(-2px);
                                }

                                .action-btn.btn-complete {
                                    background: #fdf3e1;
                                    color: #d97706;
                                }

                                .action-btn.btn-complete:hover {
                                    background: #fcebc5;
                                    color: #b45309;
                                    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);
                                    transform: translateY(-2px);
                                }

                                .action-btn.btn-reschedule {
                                    background: #f3f4f6;
                                    color: #4b5563;
                                }

                                .action-btn.btn-reschedule:hover {
                                    background: #e5e7eb;
                                    color: #1f2937;
                                    box-shadow: 0 4px 12px rgba(75, 85, 99, 0.2);
                                    transform: translateY(-2px);
                                }

                                .action-btn.btn-cancel {
                                    background: #fce8e8;
                                    color: #d93025;
                                }

                                .action-btn.btn-cancel:hover {
                                    background: #fad2d2;
                                    color: #b02a37;
                                    box-shadow: 0 4px 12px rgba(217, 48, 37, 0.2);
                                    transform: translateY(-2px);
                                }
                            </style>
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th style="text-align: center;">Servicio</th>
                                        <th style="text-align: center;">Fecha y Hora</th>
                                        <th style="text-align: center;">Precio</th>
                                        <th style="text-align: center;">Estado</th>
                                        <th style="text-align: right;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citas as $c): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div
                                                        style="width: 40px; height: 40px; border-radius: 50%; background: var(--gold-soft); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; overflow: hidden;">
                                                        <?php if (!empty($c['cliente_foto'])): ?>
                                                            <img src="<?= $base_path ?>public/uploads/perfiles/<?= htmlspecialchars($c['cliente_foto']) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                                        <?php else: ?>
                                                            <?= strtoupper(mb_substr($c['cliente'], 0, 1)) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:700; font-size:14px; color: var(--text-color);">
                                                            <?= htmlspecialchars($c['cliente']) ?></div>
                                                        <?php if ($c['cliente_telefono']): ?>
                                                            <div style="font-size:12px; color:var(--text-muted); margin-top: 2px;">
                                                                <i class="bi bi-telephone-fill"
                                                                    style="color: var(--gold); font-size: 10px; margin-right: 2px;"></i>
                                                                <?= htmlspecialchars($c['cliente_telefono']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="font-size: 13.5px; font-weight: 600; color: var(--text-color);">
                                                    <?= htmlspecialchars($c['servicio']) ?></div>
                                                <div
                                                    style="font-size:11px; color:var(--gold); font-weight: 600; background: var(--gold-soft); display: inline-block; padding: 2px 6px; border-radius: 4px; margin-top: 4px;">
                                                    <i class="bi bi-clock"></i> <?= $c['duracion_min'] ?> min
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                                    <div style="background: var(--dark); color: #fff; padding: 6px 14px; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.15); letter-spacing: 0.5px;">
                                                        <i class="bi bi-calendar3" style="color: var(--gold); font-size: 14px;"></i>
                                                        <?= date('d/m/Y', strtotime($c['fecha'])) ?>
                                                    </div>
                                                    <div style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 12px; color: var(--text-muted); font-weight: 600; background: var(--background); padding: 2px 10px; border-radius: 12px; border: 1px solid var(--border);">
                                                        <i class="bi bi-clock-fill" style="color: #a0aec0; font-size: 11px;"></i>
                                                        <?= substr($c['hora'], 0, 5) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div
                                                    style="font-size: 15px; font-weight: 800; color: var(--gold); background: rgba(181, 138, 74, 0.08); display: inline-block; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(181, 138, 74, 0.2); box-shadow: 0 2px 6px rgba(181, 138, 74, 0.05);">
                                                    $<?= number_format($c['precio'], 0, ',', '.') ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge-estado badge-<?= strtolower($c['estado']) ?>"
                                                    style="font-size: 11px; padding: 5px 12px;">
                                                    <?= ucfirst(strtolower($c['estado'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                    <?php if ($c['estado'] === 'PENDIENTE'): ?>
                                                        <button class="action-btn btn-accept"
                                                            onclick="accion(<?= $c['id_cita'] ?>,'aceptar')" title="Aceptar Cita">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if (in_array($c['estado'], ['PENDIENTE', 'ACEPTADA'])): ?>
                                                        <button class="action-btn btn-reschedule"
                                                            onclick="abrirReprogramar(<?= $c['id_cita'] ?>,'<?= $c['fecha'] ?>','<?= substr($c['hora'], 0, 5) ?>')"
                                                            title="Reprogramar">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <button class="action-btn btn-cancel"
                                                            onclick="accion(<?= $c['id_cita'] ?>,'cancelar')" title="Cancelar">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($c['estado'] === 'ACEPTADA'): ?>
                                                        <button class="action-btn btn-complete"
                                                            onclick="accion(<?= $c['id_cita'] ?>,'completar')"
                                                            title="Marcar Completada">
                                                            <i class="bi bi-check-all"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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

    <!-- Modal Reprogramar -->
    <div class="modal-overlay" id="modalReprogramar">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="bi bi-arrow-repeat" style="color:var(--gold);margin-right:8px;"></i>Reprogramar Cita</h3>
                <button class="modal-close" onclick="cerrarModal()"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body">
                <div id="infoActual"
                    style="background:var(--background);border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;color:var(--text-light);">
                </div>
                <form id="formReprogramar" method="POST"
                    action="<?= $base_path ?>controllers/barbero/citasController.php">
                    <input type="hidden" name="accion" value="reprogramar">
                    <input type="hidden" name="id_cita" id="repCitaId">
                    <div class="form-group">
                        <label class="form-label">Nueva fecha</label>
                        <input type="date" name="nueva_fecha" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva hora</label>
                        <input type="time" name="nueva_hora" class="form-control" required>
                        <p style="font-size:12px;color:var(--text-muted);margin-top:6px;">
                            <i class="bi bi-clock"></i> El cliente tendra<strong>3 minutos</strong> para confirmar o
                            cancelar.
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="document.getElementById('formReprogramar').submit()">
                    <i class="bi bi-arrow-repeat"></i> Confirmar reprogramacion
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
        function accion(id, accion) {
            var t = {
                aceptar: { title: 'Aceptar cita', text: '¿Aceptas esta cita?', btn: 'Sí, aceptar', color: '#16a34a' },
                cancelar: { title: 'Cancelar cita', text: '¿Cancelas esta cita?', btn: 'Sí, cancelar', color: '#dc2626' },
                completar: { title: 'Completar cita', text: '¿Marcar como completada?', btn: 'Completar', color: '#b58a4a' }
            };
            var d = t[accion];
            Swal.fire({
                title: d.title, text: d.text, icon: 'question', showCancelButton: true,
                confirmButtonColor: d.color, cancelButtonColor: '#6b7280',
                confirmButtonText: d.btn, cancelButtonText: 'Volver'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                var f = document.createElement('form');
                f.method = 'POST';
                f.action = '<?= $base_path ?>controllers/barbero/citasController.php';
                f.innerHTML = '<input name="accion" value="' + accion + '"><input name="id_cita" value="' + id + '">';
                document.body.appendChild(f); f.submit();
            });
        }
        function abrirReprogramar(id, fecha, hora) {
            document.getElementById('repCitaId').value = id;
            document.getElementById('infoActual').innerHTML = '<i class="bi bi-calendar3"></i> Cita actual: <strong>' + fecha + ' a las ' + hora + '</strong>';
            document.getElementById('modalReprogramar').classList.add('open');
        }
        function cerrarModal() {
            document.getElementById('modalReprogramar').classList.remove('open');
        }
    </script>
</body>

</html>