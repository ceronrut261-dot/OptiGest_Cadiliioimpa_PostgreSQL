<?php $__env->startSection('titulo', 'Movimientos de Inventario'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Movimientos de Inventario</h2>
    <a href="<?php echo e(route('inventario.movimientos.create')); ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo movimiento
    </a>
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th>Material</th>
                <th>Tipo</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Stock resultante</th>
                <th>Usuario</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($mov->fecha->format('d/m/Y H:i')); ?></td>
                    <td><?php echo e($mov->material->nombre); ?></td>
                    <td>
                        <span class="badge <?php echo e($mov->tipo === 'entrada' ? 'bg-success' : 'bg-warning text-dark'); ?>">
                            <?php echo e(ucfirst($mov->tipo)); ?>

                        </span>
                    </td>
                    <td class="text-end"><?php echo e($mov->cantidad); ?></td>
                    <td class="text-end"><?php echo e($mov->stock_resultante); ?></td>
                    <td><?php echo e($mov->usuario->name); ?></td>
                    <td class="small text-muted">
                        <?php echo e($mov->motivo); ?>

                        <?php if($mov->cotizacion): ?>
                            <br><span class="badge bg-info text-dark">Cotización <?php echo e($mov->cotizacion->codigo); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay movimientos registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo e($movimientos->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/movimientos/index.blade.php ENDPATH**/ ?>