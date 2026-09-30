<?php $__env->startSection('titulo', 'Usuarios'); ?>
<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Usuarios</h2>
    <a href="<?php echo e(route('usuarios.create')); ?>" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Crear usuario</a>
</div>

<?php if(session('status')): ?>
    <div class="alert alert-success"><?php echo e(session('status')); ?></div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="<?php echo e($usuario->activo ? '' : 'table-secondary text-muted'); ?>">
            <td><?php echo e($usuario->name); ?></td>
            <td><?php echo e($usuario->email); ?></td>
            <td><?php echo e($usuario->roles->pluck('name')->implode(', ') ?: '—'); ?></td>
            <td>
                <?php if($usuario->activo): ?>
                    <span class="badge bg-success">Activo</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Desactivado</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <?php if($usuario->id === auth()->id()): ?>
                    <span class="text-muted small">(tu cuenta)</span>
                <?php elseif($usuario->activo): ?>
                    <form action="<?php echo e(route('usuarios.destroy', $usuario)); ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Desactivar a <?php echo e($usuario->name); ?>? No podrá iniciar sesión, pero su historial se conserva.');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                <?php else: ?>
                    <form action="<?php echo e(route('usuarios.reactivar', $usuario)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($usuarios->links()); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/usuarios/index.blade.php ENDPATH**/ ?>