<?php $__env->startSection('titulo', 'Cotizaciones'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotizaciones</h2>
    <a href="<?php echo e(route('cotizaciones.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva cotización</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $cotizaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($cot->codigo); ?></td>
            <td><?php echo e($cot->cliente); ?></td>
            <td><span class="badge bg-secondary text-capitalize"><?php echo e($cot->estado); ?></span></td>
            <td class="text-end">Q<?php echo e(number_format($cot->total, 2)); ?></td>
            <td><?php echo e($cot->fecha->format('d/m/Y')); ?></td>
            <td class="text-end"><a href="<?php echo e(route('cotizaciones.show', $cot)); ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No hay cotizaciones registradas.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($cotizaciones->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/cotizaciones/index.blade.php ENDPATH**/ ?>