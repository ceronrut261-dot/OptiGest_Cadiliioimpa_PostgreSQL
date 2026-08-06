<?php $__env->startSection('titulo', 'Ticket ' . $ticket->codigo); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Ticket <?php echo e($ticket->codigo); ?></h2>
    <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-outline-primary btn-sm">Editar</a>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> <?php echo e($ticket->cliente); ?></p>
        <p><strong>Descripción:</strong> <?php echo e($ticket->descripcion); ?></p>
        <p><strong>Técnico:</strong> <?php echo e($ticket->tecnico?->name ?? '—'); ?></p>
        <p><strong>Estado actual:</strong> <span class="badge bg-secondary text-capitalize"><?php echo e(str_replace('_',' ',$ticket->estado)); ?></span></p>

        <form action="<?php echo e(route('tickets.estado', $ticket)); ?>" method="POST" class="d-flex gap-2 mt-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <select name="estado" class="form-select form-select-sm" style="max-width:220px;">
                <?php $__currentLoopData = ['pendiente','asignado','en_proceso','completado','cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($estado); ?>" <?php echo e($ticket->estado == $estado ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_',' ',$estado))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn-sm btn-primary">Actualizar estado</button>
        </form>
    </div>
</div>

<h3 class="h6">Cotizaciones relacionadas</h3>
<ul class="list-group">
    <?php $__empty_1 = true; $__currentLoopData = $ticket->cotizaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="list-group-item d-flex justify-content-between">
            <a href="<?php echo e(route('cotizaciones.show', $cot)); ?>"><?php echo e($cot->codigo); ?></a>
            <span class="badge bg-secondary text-capitalize"><?php echo e($cot->estado); ?></span>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="list-group-item text-muted">Sin cotizaciones asociadas.</li>
    <?php endif; ?>
</ul>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/tickets/show.blade.php ENDPATH**/ ?>