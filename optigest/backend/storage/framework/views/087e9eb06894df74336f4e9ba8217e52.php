<?php $__env->startSection('titulo', 'Importar Inventario'); ?>

<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4">Importar Inventario desde Excel</h2>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form action="<?php echo e(route('inventario.importar')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="mb-3">
        <label for="archivo" class="form-label">Selecciona archivo Excel/CSV</label>
        <input type="file" name="archivo" id="archivo" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload"></i> Cargar Inventario
    </button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/importar.blade.php ENDPATH**/ ?>