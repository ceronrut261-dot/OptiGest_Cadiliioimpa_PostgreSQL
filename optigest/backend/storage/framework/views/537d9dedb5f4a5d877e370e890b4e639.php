<?php $__env->startSection('titulo', 'Materiales'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Materiales de Inventario</h2>
    <a href="<?php echo e(route('inventario.materiales.create')); ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo material
    </a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control form-control-sm" placeholder="Buscar por nombre, código o categoría">
    </div>
    <div class="col-md-3">
        <div class="form-check mt-1">
            <input type="checkbox" name="bajo_stock" value="1" class="form-check-input" id="bajoStock" <?php echo e(request('bajo_stock') ? 'checked' : ''); ?>>
            <label class="form-check-label small" for="bajoStock">Solo bajo stock (<?php echo e($totalBajoStock); ?>)</label>
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary btn-sm w-100">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle">
        <thead class="table-light">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Stock</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($material->bajo_stock ? 'table-danger' : ''); ?>">
                    <td><?php echo e($material->codigo); ?></td>
                    <td><?php echo e($material->nombre); ?></td>
                    <td><?php echo e($material->categoria); ?></td>
                    <td><?php echo e($material->proveedor?->nombre ?? '—'); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($material->precio, 2)); ?></td>
                    <td class="text-end">
                        <?php echo e($material->stock); ?>

                        <?php if($material->bajo_stock): ?>
                            <span class="badge bg-danger">bajo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?php echo e(route('inventario.materiales.edit', $material)); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="<?php echo e(route('inventario.materiales.destroy', $material)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar este material?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay materiales registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo e($materiales->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/materiales/index.blade.php ENDPATH**/ ?>