<?php $__env->startSection('titulo', 'Iniciar sesión'); ?>

<?php $__env->startSection('contenido'); ?>
<form method="POST" action="<?php echo e(route('login')); ?>">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label small">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" class="form-check-input" id="remember">
        <label class="form-check-label small" for="remember">Recordarme</label>
    </div>

    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Iniciar sesión</button>

    <div class="d-flex justify-content-between small">
        <a href="<?php echo e(route('password.request')); ?>">¿Olvidaste tu contraseña?</a>
        <a href="<?php echo e(route('register')); ?>">Crear cuenta</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/auth/login.blade.php ENDPATH**/ ?>