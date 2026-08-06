<?php $__env->startSection('titulo', 'Proveedores'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Proveedores</h2>
    <a href="<?php echo e(route('proveedores.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo proveedor</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>NIT</th><th>Contacto</th><th>Teléfono</th><th class="text-end">Materiales</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($proveedor->nombre); ?></td>
            <td><?php echo e($proveedor->nit); ?></td>
            <td><?php echo e($proveedor->contacto); ?></td>
            <td><?php echo e($proveedor->telefono); ?></td>
            <td class="text-end"><?php echo e($proveedor->materiales_count); ?></td>
            <td class="text-end">
                <a href="<?php echo e(route('proveedores.edit', $proveedor)); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form action="<?php echo e(route('proveedores.destroy', $proveedor)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No hay proveedores registrados.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($proveedores->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/proveedores/index.blade.php ENDPATH**/ ?>