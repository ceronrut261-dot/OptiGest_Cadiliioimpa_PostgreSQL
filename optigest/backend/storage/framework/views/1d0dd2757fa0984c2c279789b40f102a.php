
<?php $__env->startSection('titulo', 'Clientes'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Clientes</h2>
    <a href="<?php echo e(route('clientes.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
</div>

<?php if(session('status')): ?>
    <div class="alert alert-success"><?php echo e(session('status')); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" style="max-width:320px;">
        <?php if($verInactivos): ?>
            <input type="hidden" name="inactivos" value="1">
        <?php endif; ?>
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control form-control-sm" placeholder="Buscar por nombre o teléfono...">
    </form>

    <a href="<?php echo e(route('clientes.index', $verInactivos ? [] : ['inactivos' => 1])); ?>" class="btn btn-sm btn-outline-secondary">
        <?php echo e($verInactivos ? 'Ver solo activos' : 'Ver desactivados'); ?>

    </a>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th class="text-end">Tickets</th><th class="text-end">Cotizaciones</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="<?php echo e($cliente->activo ? '' : 'table-secondary text-muted'); ?>">
            <td><?php echo e($cliente->nombre); ?></td>
            <td><?php echo e($cliente->telefono); ?></td>
            <td><?php echo e($cliente->direccion); ?></td>
            <td class="text-end"><?php echo e($cliente->tickets_count); ?></td>
            <td class="text-end"><?php echo e($cliente->cotizaciones_count); ?></td>
            <td>
                <?php if($cliente->activo): ?>
                    <span class="badge bg-success">Activo</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Desactivado</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a href="<?php echo e(route('clientes.edit', $cliente)); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <?php if($cliente->activo): ?>
                    <form action="<?php echo e(route('clientes.destroy', $cliente)); ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Desactivar a <?php echo e($cliente->nombre); ?>? No se borra su historial, solo deja de aparecer en la lista activa.');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                <?php else: ?>
                    <form action="<?php echo e(route('clientes.reactivar', $cliente)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No hay clientes <?php echo e($verInactivos ? 'desactivados' : 'registrados'); ?>.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($clientes->links()); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/clientes/index.blade.php ENDPATH**/ ?>