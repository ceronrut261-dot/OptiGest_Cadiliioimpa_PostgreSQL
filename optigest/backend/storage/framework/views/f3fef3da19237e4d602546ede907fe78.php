<?php $__env->startSection('titulo', 'Dashboard'); ?>

<?php $__env->startSection('contenido'); ?>
<?php
    $hora = (int) now()->format('G');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $primerNombre = explode(' ', trim(auth()->user()->name))[0];
    $fecha = now()->locale('es')->translatedFormat('l j \d\e F \d\e Y');

    $colorTicket = [
        'pendiente' => '#f59e0b', 'asignado' => '#0ea5e9', 'en_proceso' => '#6366f1',
        'completado' => '#10b981', 'cancelado' => '#94a3b8',
    ];
    $colorCot = [
        'borrador' => '#94a3b8', 'enviada' => '#0ea5e9', 'aprobada' => '#10b981', 'rechazada' => '#ef4444',
    ];
    $badgeTicket = [
        'pendiente' => 'warning', 'asignado' => 'info', 'en_proceso' => 'primary',
        'completado' => 'success', 'cancelado' => 'secondary',
    ];

    $meta = 0.5; // horas (meta del proyecto: 30 min)
    $horas = (float) $tiempoPromedioCotizacionHoras;
    $cumpleMeta = $horas > 0 && $horas <= $meta;
    $pctMeta = $horas > 0 ? min(100, round(($horas / ($meta * 2)) * 100)) : 0;
?>

<style>
    .dash-hero {
        background: linear-gradient(135deg, #0a58ca 0%, #3b82f6 55%, #60a5fa 100%);
        border-radius: 1rem; color: #fff; position: relative; overflow: hidden;
    }
    .dash-hero::after {
        content: ""; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px;
        border-radius: 50%; background: rgba(255,255,255,.12);
    }
    .dash-hero::before {
        content: ""; position: absolute; right: 90px; bottom: -90px; width: 180px; height: 180px;
        border-radius: 50%; background: rgba(255,255,255,.08);
    }
    .dash-hero .btn { position: relative; z-index: 1; }
    .kpi-card { border: 0; border-radius: 1rem; box-shadow: 0 2px 12px rgba(15,23,42,.06); transition: transform .15s, box-shadow .15s; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(15,23,42,.12); }
    .kpi-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .kpi-value { font-size: 1.9rem; font-weight: 700; line-height: 1.1; }
    .panel-card { border: 0; border-radius: 1rem; box-shadow: 0 2px 12px rgba(15,23,42,.06); overflow: hidden; }
    .panel-card .card-header { background: #fff; border-bottom: 1px solid #eef1f5; font-weight: 600; padding: .9rem 1.1rem; }
    .chart-box { position: relative; height: 230px; }
    .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
    .stock-bar { height: 6px; border-radius: 6px; background: #eef1f5; overflow: hidden; }
    .stock-bar > span { display: block; height: 100%; border-radius: 6px; background: linear-gradient(90deg, #ef4444, #f97316); }
    .list-row { transition: background .12s; }
    .list-row:hover { background: #f8fafc; }
</style>


<div class="dash-hero p-4 p-md-4 mb-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div style="position:relative; z-index:1;">
            <div class="text-white-50 small text-capitalize"><?php echo e($fecha); ?></div>
            <h2 class="h3 fw-bold mb-1"><?php echo e($saludo); ?>, <?php echo e($primerNombre); ?> 👋</h2>
            <div class="text-white-50">Resumen operativo de Constru Fontanería Cadiliompa</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Ticket::class)): ?>
                <a href="<?php echo e(route('tickets.create')); ?>" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-plus-circle"></i> Nuevo ticket
                </a>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'administrador|cotizador')): ?>
                <a href="<?php echo e(route('cotizaciones.create')); ?>" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-plus"></i> Nueva cotización
                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('catalogo.index')); ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-grid-3x3-gap"></i> Catálogo
            </a>
            <a href="<?php echo e(route('inventario.importar.form')); ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-file-earmark-arrow-up"></i> Importar Excel
            </a>
        </div>
    </div>
</div>


<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#e0edff;color:#2563eb;"><i class="bi bi-tools"></i></div>
            <div>
                <div class="text-muted small">Tickets abiertos</div>
                <div class="kpi-value"><?php echo e($kpis['tickets_abiertos']); ?></div>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#d9f7ec;color:#059669;"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="text-muted small">Completados este mes</div>
                <div class="kpi-value"><?php echo e($kpis['tickets_completados_mes']); ?></div>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#fff0d6;color:#d97706;"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="text-muted small">Cotizaciones pendientes</div>
                <div class="kpi-value"><?php echo e($kpis['cotizaciones_pendientes']); ?></div>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <?php $alerta = $kpis['materiales_bajo_stock'] > 0; ?>
        <div class="card kpi-card h-100" <?php if($alerta): ?> style="box-shadow:0 0 0 2px #fecaca, 0 2px 12px rgba(15,23,42,.06);" <?php endif; ?>>
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background:<?php echo e($alerta ? '#fee2e2' : '#e8f5e9'); ?>;color:<?php echo e($alerta ? '#dc2626' : '#2e7d32'); ?>;">
                    <i class="bi <?php echo e($alerta ? 'bi-exclamation-triangle' : 'bi-box-seam'); ?>"></i>
                </div>
                <div>
                    <div class="text-muted small">Materiales bajo stock</div>
                    <div class="kpi-value <?php echo e($alerta ? 'text-danger' : ''); ?>"><?php echo e($kpis['materiales_bajo_stock']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if($veFinanzas): ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#d9f7ec;color:#059669;"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="text-muted small">Total cotizado y aprobado este mes</div>
                <div class="kpi-value" style="font-size:1.6rem;">Q<?php echo e(number_format($finanzas['ingreso_mes'], 2)); ?></div>
                <div class="small text-muted">
                    <?php if($finanzas['iva_mes'] > 0): ?>
                        Incluye Q<?php echo e(number_format($finanzas['iva_mes'], 2)); ?> de IVA ·
                    <?php endif; ?>
                    Es lo cobrado a clientes, no la ganancia
                </div>
            </div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#fff0d6;color:#d97706;"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="text-muted small">Gastos por cobrar al cliente</div>
                <div class="kpi-value" style="font-size:1.6rem;">Q<?php echo e(number_format($finanzas['gastos_por_cobrar'], 2)); ?></div>
                <div class="small text-muted">Aún no están en una cotización</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-4">
        <?php $faltan = $finanzas['gastos_sin_comprobante'] > 0; ?>
        <div class="card kpi-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:<?php echo e($faltan ? '#fee2e2' : '#e8f5e9'); ?>;color:<?php echo e($faltan ? '#dc2626' : '#2e7d32'); ?>;">
                <i class="bi bi-paperclip"></i>
            </div>
            <div>
                <div class="text-muted small">Gastos sin comprobante</div>
                <div class="kpi-value <?php echo e($faltan ? 'text-danger' : ''); ?>" style="font-size:1.6rem;"><?php echo e($finanzas['gastos_sin_comprobante']); ?></div>
                <div class="small text-muted"><?php echo e($faltan ? 'Falta adjuntar factura o foto' : 'Todo respaldado'); ?></div>
            </div>
        </div></div>
    </div>
</div>
<?php endif; ?>


<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card panel-card h-100">
            <div class="card-header"><i class="bi bi-pie-chart text-primary"></i> Tickets por estado</div>
            <div class="card-body">
                <?php if($ticketsPorEstado->sum() > 0): ?>
                    <div class="chart-box"><canvas id="graficaTickets"></canvas></div>
                <?php else: ?>
                    <div class="text-muted text-center py-5">Sin datos aún</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card panel-card h-100">
            <div class="card-header"><i class="bi bi-bar-chart text-primary"></i> Cotizaciones por estado</div>
            <div class="card-body">
                <?php if($cotizacionesPorEstado->sum() > 0): ?>
                    <div class="chart-box"><canvas id="graficaCotizaciones"></canvas></div>
                <?php else: ?>
                    <div class="text-muted text-center py-5">Sin datos aún</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card panel-card h-100">
            <div class="card-header"><i class="bi bi-stopwatch text-primary"></i> Rendimiento del proyecto</div>
            <div class="card-body">
                <div class="text-muted small">Tiempo promedio de cotización aprobada</div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="kpi-value"><?php echo e($horas > 0 ? $horas : '—'); ?></span>
                    <span class="text-muted">hrs</span>
                    <?php if($horas > 0): ?>
                        <span class="badge <?php echo e($cumpleMeta ? 'bg-success' : 'bg-warning text-dark'); ?> ms-auto">
                            <?php echo e($cumpleMeta ? 'Cumple la meta' : 'Sobre la meta'); ?>

                        </span>
                    <?php endif; ?>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar <?php echo e($cumpleMeta ? 'bg-success' : 'bg-warning'); ?>" style="width: <?php echo e($pctMeta); ?>%"></div>
                </div>
                <div class="small text-muted mt-2">Meta del proyecto: &lt; 0.5 hrs (30 min)</div>

                <hr>
                <div class="text-muted small">Valor total de inventario</div>
                <div class="h4 mb-0">Q<?php echo e(number_format($kpis['valor_inventario'], 2)); ?></div>
            </div>
        </div>
    </div>
</div>


<div class="row g-3">
    <div class="col-lg-6">
        <div class="card panel-card h-100">
            <div class="card-header"><i class="bi bi-exclamation-triangle text-danger"></i> Alertas de bajo inventario</div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $materialesBajoStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $material->stock_minimo > 0 ? min(100, round($material->stock / $material->stock_minimo * 100)) : 0; ?>
                    <div class="list-group-item list-row">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-medium"><?php echo e($material->nombre); ?></span>
                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill"><?php echo e($material->stock); ?> / mín. <?php echo e($material->stock_minimo); ?></span>
                        </div>
                        <div class="stock-bar"><span style="width: <?php echo e($pct); ?>%"></span></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                        Sin alertas de bajo stock por el momento.
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end border-0">
                <a href="<?php echo e(route('inventario.reportes.stock')); ?>" class="small text-decoration-none">Ver reporte completo →</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card panel-card h-100">
            <div class="card-header"><i class="bi bi-tools text-primary"></i> Tickets recientes</div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $ticketsRecientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="list-group-item list-group-item-action list-row d-flex justify-content-between align-items-center">
                        <span>
                            <span class="fw-semibold"><?php echo e($ticket->codigo); ?></span>
                            <span class="text-muted"> — <?php echo e($ticket->cliente->nombre); ?></span>
                        </span>
                        <span class="badge bg-<?php echo e($badgeTicket[$ticket->estado] ?? 'secondary'); ?>-subtle text-<?php echo e($badgeTicket[$ticket->estado] ?? 'secondary'); ?>-emphasis rounded-pill text-capitalize">
                            <?php echo e(str_replace('_', ' ', $ticket->estado)); ?>

                        </span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-center text-muted py-4">No hay tickets registrados todavía.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end border-0">
                <a href="<?php echo e(route('tickets.index')); ?>" class="small text-decoration-none">Ver todos los tickets →</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return; // sin internet: el dashboard sigue funcionando sin gráficas

    const etiquetas = s => s.replace('_', ' ').replace(/^./, c => c.toUpperCase());
    const opciones = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 14 } } },
    };

    const tickets = <?php echo json_encode($ticketsPorEstado, 15, 512) ?>;
    const coloresTickets = <?php echo json_encode($colorTicket, 15, 512) ?>;
    const elT = document.getElementById('graficaTickets');
    if (elT) {
        new Chart(elT, {
            type: 'doughnut',
            data: {
                labels: Object.keys(tickets).map(etiquetas),
                datasets: [{ data: Object.values(tickets), backgroundColor: Object.keys(tickets).map(k => coloresTickets[k] || '#94a3b8'), borderWidth: 2, borderColor: '#fff' }],
            },
            options: { ...opciones, cutout: '65%' },
        });
    }

    const cots = <?php echo json_encode($cotizacionesPorEstado, 15, 512) ?>;
    const coloresCots = <?php echo json_encode($colorCot, 15, 512) ?>;
    const elC = document.getElementById('graficaCotizaciones');
    if (elC) {
        new Chart(elC, {
            type: 'bar',
            data: {
                labels: Object.keys(cots).map(etiquetas),
                datasets: [{ data: Object.values(cots), backgroundColor: Object.keys(cots).map(k => coloresCots[k] || '#94a3b8'), borderRadius: 8, maxBarThickness: 46 }],
            },
            options: {
                ...opciones,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f5' } }, x: { grid: { display: false } } },
            },
        });
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/dashboard.blade.php ENDPATH**/ ?>