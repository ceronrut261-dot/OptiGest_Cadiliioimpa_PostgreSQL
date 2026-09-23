<?php $__env->startSection('titulo', 'Asistente IA'); ?>
<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4"><i class="bi bi-robot"></i> Asistente Inteligente</h2>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('asistente.consultar')); ?>">
            <?php echo csrf_field(); ?>
            <label class="form-label small">Pregunta sobre el estado operativo actual</label>
            <div class="input-group">
                <input type="text" name="pregunta" class="form-control" placeholder="Ej. ¿qué materiales están bajo el stock mínimo?" required>
                <button class="btn btn-primary">Preguntar</button>
            </div>
        </form>
    </div>
</div>

<?php if(session('ultimaRespuesta')): ?>
    <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
        <div class="card-body">
            <p class="small text-muted mb-1">Tu pregunta:</p>
            <p class="fw-semibold"><?php echo e(session('ultimaRespuesta')->pregunta); ?></p>
            <p class="small text-muted mb-1">Respuesta:</p>
            <p style="white-space:pre-line;"><?php echo e(session('ultimaRespuesta')->respuesta); ?></p>
        </div>
    </div>
<?php endif; ?>

<h3 class="h6">Historial reciente</h3>
<div class="list-group">
    <?php $__empty_1 = true; $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="list-group-item">
            <p class="fw-semibold mb-1 small"><?php echo e($item->pregunta); ?></p>
            <p class="text-muted small mb-0" style="white-space:pre-line;"><?php echo e($item->respuesta); ?></p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="list-group-item text-muted">Aún no has hecho preguntas al asistente.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/asistente/index.blade.php ENDPATH**/ ?>