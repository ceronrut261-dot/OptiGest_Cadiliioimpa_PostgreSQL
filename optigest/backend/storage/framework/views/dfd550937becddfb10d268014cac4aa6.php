<?php $__env->startSection('titulo', 'Nuevo movimiento'); ?>

<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4">Registrar movimiento de inventario</h2>

<form method="POST" action="<?php echo e(route('inventario.movimientos.store')); ?>" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
        <label class="form-label small">Material</label>
        <select name="material_id" class="form-select" required>
            <option value="">— Selecciona —</option>
            <?php $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($material->id); ?>"><?php echo e($material->codigo); ?> — <?php echo e($material->nombre); ?> (stock: <?php echo e($material->stock); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small">Tipo de movimiento</label>
        <select name="tipo" class="form-select" required>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small">Cantidad</label>
        <input type="number" name="cantidad" min="1" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Motivo (opcional)</label>
        <input type="text" name="motivo" class="form-control" placeholder="Ej. compra a proveedor, uso en servicio, ajuste">
    </div>

    <button type="submit" class="btn btn-primary">Registrar movimiento</button>
    <a href="<?php echo e(route('inventario.movimientos.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/movimientos/create.blade.php ENDPATH**/ ?>