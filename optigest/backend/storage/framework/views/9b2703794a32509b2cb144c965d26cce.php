

<?php $__env->startSection('titulo', 'Crear usuario'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="row">
    <div class="col-md-6">
        <h4 class="mb-3">Crear nuevo usuario</h4>
        <p class="text-muted small">
            Solo un administrador puede crear cuentas para el personal de
            Constru Fontanería Cadiliompa. Elige con cuidado el rol: define
            qué módulos y qué tickets/cotizaciones podrá gestionar.
        </p>

        <form method="POST" action="<?php echo e(route('usuarios.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label small">Nombre completo</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label small">Correo electrónico</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small">Rol</label>
                <select name="role" class="form-select" required>
                    <option value="">Selecciona un rol...</option>
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($rol); ?>" <?php if(old('role') === $rol): echo 'selected'; endif; ?>><?php echo e(ucfirst($rol)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Crear usuario</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/auth/register.blade.php ENDPATH**/ ?>