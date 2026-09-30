
<?php $__env->startSection('titulo', isset($cliente) ? 'Editar cliente' : 'Nuevo cliente'); ?>
<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4"><?php echo e(isset($cliente) ? 'Editar cliente' : 'Nuevo cliente'); ?></h2>
<form method="POST" action="<?php echo e(isset($cliente) ? route('clientes.update', $cliente) : route('clientes.store')); ?>" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    <?php echo csrf_field(); ?>
    <?php if(isset($cliente)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <?php if(!isset($cliente) && request('origen')): ?>
        <input type="hidden" name="origen" value="<?php echo e(request('origen')); ?>">
    <?php endif; ?>
    <div class="mb-3"><label class="form-label small">Nombre</label><input type="text" name="nombre" value="<?php echo e(old('nombre', $cliente->nombre ?? '')); ?>" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label small">Teléfono</label><input type="text" name="telefono" value="<?php echo e(old('telefono', $cliente->telefono ?? '')); ?>" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Dirección</label><input type="text" name="direccion" value="<?php echo e(old('direccion', $cliente->direccion ?? '')); ?>" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Email</label><input type="email" name="email" value="<?php echo e(old('email', $cliente->email ?? '')); ?>" class="form-control"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/clientes/create.blade.php ENDPATH**/ ?>