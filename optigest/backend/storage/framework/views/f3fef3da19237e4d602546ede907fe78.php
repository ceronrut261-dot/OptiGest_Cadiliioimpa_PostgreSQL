

<?php $__env->startSection('titulo', 'Dashboard'); ?>

<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4">Panel Gerencial — KPIs Operativos</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tickets abiertos</div>
                <div class="h3 mb-0"><?php echo e($kpis['tickets_abiertos']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Completados este mes</div>
                <div class="h3 mb-0"><?php echo e($kpis['tickets_completados_mes']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Cotizaciones pendientes</div>
                <div class="h3 mb-0"><?php echo e($kpis['cotizaciones_pendientes']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 <?php echo e($kpis['materiales_bajo_stock'] > 0 ? 'border-danger' : ''); ?>">
            <div class="card-body">
                <div class="text-muted small">Materiales bajo stock</div>
                <div class="h3 mb-0 <?php echo e($kpis['materiales_bajo_stock'] > 0 ? 'text-danger' : ''); ?>">
                    <?php echo e($kpis['materiales_bajo_stock']); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Valor total de inventario</div>
                <div class="h4">Q<?php echo e(number_format($kpis['valor_inventario'], 2)); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tiempo promedio de cotización aprobada</div>
                <div class="h4"><?php echo e($tiempoPromedioCotizacionHoras); ?> hrs</div>
                <div class="text-muted small">Meta del proyecto: &lt; 0.5 hrs (30 min)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tickets por estado</div>
                <?php $__empty_1 = true; $__currentLoopData = $ticketsPorEstado; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between small">
                        <span class="text-capitalize"><?php echo e(str_replace('_', ' ', $estado)); ?></span>
                        <strong><?php echo e($total); ?></strong>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span class="text-muted small">Sin datos aún</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-exclamation-triangle text-danger"></i> Alertas de bajo inventario
            </div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $materialesBajoStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo e($material->nombre); ?></span>
                        <span class="badge bg-danger rounded-pill"><?php echo e($material->stock); ?> / mín. <?php echo e($material->stock_minimo); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-muted">Sin alertas de bajo stock por el momento.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?php echo e(route('inventario.reportes.stock')); ?>" class="small">Ver reporte completo →</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tools"></i> Tickets recientes
            </div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $ticketsRecientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><?php echo e($ticket->codigo); ?> — <?php echo e($ticket->cliente->nombre); ?></span>
                        <span class="badge bg-secondary text-capitalize"><?php echo e(str_replace('_', ' ', $ticket->estado)); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-muted">No hay tickets registrados todavía.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/dashboard.blade.php ENDPATH**/ ?>