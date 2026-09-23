<?php $__env->startSection('titulo', isset($material) ? 'Editar material' : 'Nuevo material'); ?>

<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4"><?php echo e(isset($material) ? 'Editar material' : 'Nuevo material'); ?></h2>

<form method="POST" action="<?php echo e(isset($material) ? route('inventario.materiales.update', $material) : route('inventario.materiales.store')); ?>" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    <?php echo csrf_field(); ?>
    <?php if(isset($material)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <div class="mb-3">
        <label class="form-label small">Nombre</label>
        <input type="text" name="nombre" value="<?php echo e(old('nombre', $material->nombre ?? '')); ?>" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Categoría</label>
            <input type="text" name="categoria" value="<?php echo e(old('categoria', $material->categoria ?? '')); ?>" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Unidad de medida</label>
            <input type="text" name="unidad_medida" value="<?php echo e(old('unidad_medida', $material->unidad_medida ?? 'unidad')); ?>" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"><?php echo e(old('descripcion', $material->descripcion ?? '')); ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label small">Precio (Q)</label>
            <input type="number" step="0.01" name="precio" value="<?php echo e(old('precio', $material->precio ?? '')); ?>" class="form-control" required>
        </div>
        <?php if (! (isset($material))): ?>
        <div class="col-md-4 mb-3">
            <label class="form-label small">Stock inicial</label>
            <input type="number" name="stock" value="<?php echo e(old('stock', 0)); ?>" class="form-control">
        </div>
        <?php endif; ?>
        <div class="col-md-4 mb-3">
            <label class="form-label small">Stock mínimo (alerta)</label>
            <input type="number" name="stock_minimo" value="<?php echo e(old('stock_minimo', $material->stock_minimo ?? 10)); ?>" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Proveedor</label>
        <select name="proveedor_id" class="form-select">
            <option value="">— Sin proveedor —</option>
            <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($proveedor->id); ?>" <?php echo e(old('proveedor_id', $material->proveedor_id ?? '') == $proveedor->id ? 'selected' : ''); ?>>
                    <?php echo e($proveedor->nombre); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?php echo e(route('inventario.materiales.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/materiales/create.blade.php ENDPATH**/ ?>