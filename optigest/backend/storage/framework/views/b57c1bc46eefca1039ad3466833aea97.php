
<?php $__env->startSection('titulo', 'Cotización ' . $cotizacion->codigo); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotización <?php echo e($cotizacion->codigo); ?></h2>
    <div>
        <a href="<?php echo e(route('cotizaciones.pdf', $cotizacion)); ?>" class="btn btn-outline-danger btn-sm">PDF</a>
        <?php if($cotizacion->estado !== 'aprobada' && $cotizacion->estado !== 'rechazada'): ?>
            <form action="<?php echo e(route('cotizaciones.aprobar', $cotizacion)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-success btn-sm">Aprobar</button>
            </form>
            <form action="<?php echo e(route('cotizaciones.rechazar', $cotizacion)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-outline-danger btn-sm">Rechazar</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> <?php echo e($cotizacion->cliente->nombre); ?></p>
        <p><strong>Estado:</strong> <span class="badge bg-secondary text-capitalize"><?php echo e($cotizacion->estado); ?></span></p>
        <p><strong>Cotizador:</strong> <?php echo e($cotizacion->cotizador->name); ?></p>
        <?php if($cotizacion->ticket): ?>
            <p><strong>Ticket relacionado:</strong> <a href="<?php echo e(route('tickets.show', $cotizacion->ticket)); ?>"><?php echo e($cotizacion->ticket->codigo); ?></a></p>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
<table class="table table-sm bg-white">
    <thead class="table-light"><tr><th>Material</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $cotizacion->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($detalle->material->nombre); ?></td>
            <td class="text-end"><?php echo e($detalle->cantidad); ?></td>
            <td class="text-end">Q<?php echo e(number_format($detalle->precio_unitario,2)); ?></td>
            <td class="text-end">Q<?php echo e(number_format($detalle->subtotal,2)); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end">Q<?php echo e(number_format($cotizacion->total,2)); ?></th></tr>
    </tfoot>
</table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/cotizaciones/show.blade.php ENDPATH**/ ?>