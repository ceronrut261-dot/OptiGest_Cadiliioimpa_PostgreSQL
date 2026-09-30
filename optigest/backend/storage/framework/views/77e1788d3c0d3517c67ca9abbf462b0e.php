<?php $__env->startSection('titulo', 'Restablecer contraseña'); ?>

<?php $__env->startSection('contenido'); ?>
<form method="POST" action="<?php echo e(route('password.store')); ?>">
    <?php echo csrf_field(); ?>

    <input type="hidden" name="token" value="<?php echo e($request->route('token')); ?>">

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="<?php echo e(old('email', $request->email)); ?>" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label small">Nueva contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Confirmar nueva contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>