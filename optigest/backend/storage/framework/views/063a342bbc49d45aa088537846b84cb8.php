<?php $__env->startSection('titulo', 'Salidas de materiales'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Salidas de materiales</h2>
    <a href="<?php echo e(route('salidas.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-up"></i> Nueva salida</a>
</div>

<?php if(session('status')): ?>
    <div class="alert alert-success"><?php echo e(session('status')); ?></div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Proyecto</th><th>Entregó</th><th>Recibió</th><th>Fecha</th><th class="text-end">Total</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $salidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salida): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($salida->codigo); ?></td>
            <td><?php echo e($salida->proyecto); ?></td>
            <td><?php echo e($salida->usuario->name); ?></td>
            <td><?php echo e($salida->persona_recibe); ?></td>
            <td><?php echo e($salida->fecha->format('d/m/Y')); ?></td>
            <td class="text-end">Q<?php echo e(number_format($salida->total, 2)); ?></td>
            <td class="text-end">
                <a href="<?php echo e(route('salidas.pdf', $salida)); ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Vale PDF</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay salidas registradas.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($salidas->links()); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/salidas/index.blade.php ENDPATH**/ ?>