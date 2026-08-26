<?php
/**
 * nav_footer.php — Cierre del layout del dashboard (igual para los 3 roles)
 * Incluir al final de cada vista del dashboard, antes de </body>
 */
?>
    </div><!-- /.page-content -->
</div><!-- /.main-content -->

<script src="<?= $base_path ?? '../../' ?>public/js/swal-custom.js"></script>
<script>
    /* Sidebar toggle */
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }

    /* Session timeout warning — 14 min avisa, 15 min cierra */
    (function() {
        var warningShown = false;
        var lastActivity = Date.now();
        var WARNING_MS  = 14 * 60 * 1000;
        var TIMEOUT_MS  = 15 * 60 * 1000;

        function resetActivity() { lastActivity = Date.now(); warningShown = false; }

        ['click','keydown','mousemove','touchstart'].forEach(function(ev) {
            document.addEventListener(ev, resetActivity, { passive: true });
        });

        setInterval(function() {
            var idle = Date.now() - lastActivity;
            if (idle >= TIMEOUT_MS) {
                window.location.href = '../../views/auth/login.php?timeout=1';
            } else if (idle >= WARNING_MS && !warningShown && typeof Swal !== 'undefined') {
                warningShown = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesión por expirar',
                    text: 'Tu sesión cerrará por inactividad en 1 minuto.',
                    timer: 60000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Seguir activo',
                    confirmButtonColor: '#b58a4a'
                }).then(function() { resetActivity(); });
            }
        }, 10000);
    })();
</script>

<!-- Integración Global de Calendario (Flatpickr) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<style>
/* Estilo personalizado de Flatpickr para MC Barber */
.flatpickr-calendar {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    padding: 10px;
    font-family: inherit;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
    background: var(--gold) !important;
    border-color: var(--gold) !important;
    color: #fff !important;
}
.flatpickr-day:hover {
    background: var(--gold-soft);
    border-color: transparent;
    color: var(--gold);
}
.flatpickr-months .flatpickr-month {
    color: var(--black);
    fill: var(--black);
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: transparent;
    font-weight: 700;
}
.flatpickr-weekdays .flatpickr-weekday {
    color: var(--text-muted);
    font-weight: 700;
}
/* Ocultar el ícono del calendario nativo y agregar el propio */
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
input[type="date"] {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23b58a4a' class='bi bi-calendar' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
}
input[type="time"] {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23b58a4a' class='bi bi-clock' viewBox='0 0 16 16'%3E%3Cpath d='M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z'/%3E%3Cpath d='M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inicializar inputs de fecha
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        flatpickr(input, {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: input.getAttribute('min') || null,
            maxDate: input.getAttribute('max') || null,
            disableMobile: "true",
            onChange: function(selectedDates, dateStr, instance) {
                input.value = dateStr;
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    });

    // Inicializar inputs de hora
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        flatpickr(input, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            disableMobile: "true",
            onChange: function(selectedDates, dateStr, instance) {
                input.value = dateStr;
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    });
});

function confirmarCerrarSesion(e, url) {
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
}
</script>
