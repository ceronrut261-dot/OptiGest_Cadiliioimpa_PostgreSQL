<?php $__env->startSection('titulo', 'Recuperar contraseña'); ?>

<?php $__env->startSection('contenido'); ?>
<p class="small text-muted">
    Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
</p>

<form method="POST" action="<?php echo e(route('password.email')); ?>">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Enviar enlace de recuperación</button>

    <div class="text-center small">
        <a href="<?php echo e(route('login')); ?>">← Volver a iniciar sesión</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>