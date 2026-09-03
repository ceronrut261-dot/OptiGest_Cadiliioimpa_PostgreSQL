
<?php $__env->startSection('titulo', 'Tickets'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Tickets de Servicio</h2>
    <a href="<?php echo e(route('tickets.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo ticket</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Cliente</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($ticket->codigo); ?></td>
            <td><?php echo e($ticket->cliente->nombre); ?></td>
            <td class="text-capitalize"><?php echo e($ticket->prioridad); ?></td>
            <td><span class="badge bg-secondary text-capitalize"><?php echo e(str_replace('_',' ',$ticket->estado)); ?></span></td>
            <td><?php echo e($ticket->tecnico?->name ?? '—'); ?></td>
            <td class="text-end"><a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No hay tickets registrados.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($tickets->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/tickets/index.blade.php ENDPATH**/ ?>