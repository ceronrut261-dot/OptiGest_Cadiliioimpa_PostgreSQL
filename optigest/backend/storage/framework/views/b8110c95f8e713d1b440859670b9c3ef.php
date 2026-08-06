<?php $__env->startSection('titulo', isset($ticket) ? 'Editar ticket' : 'Nuevo ticket'); ?>
<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4"><?php echo e(isset($ticket) ? 'Editar ticket' : 'Nuevo ticket'); ?></h2>
<form method="POST" action="<?php echo e(isset($ticket) ? route('tickets.update', $ticket) : route('tickets.store')); ?>" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    <?php echo csrf_field(); ?>
    <?php if(isset($ticket)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <div class="mb-3"><label class="form-label small">Cliente</label><input type="text" name="cliente" value="<?php echo e(old('cliente', $ticket->cliente ?? '')); ?>" class="form-control" required></div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label small">Teléfono</label><input type="text" name="telefono_cliente" value="<?php echo e(old('telefono_cliente', $ticket->telefono_cliente ?? '')); ?>" class="form-control"></div>
        <div class="col-md-6 mb-3"><label class="form-label small">Dirección</label><input type="text" name="direccion" value="<?php echo e(old('direccion', $ticket->direccion ?? '')); ?>" class="form-control"></div>
    </div>
    <div class="mb-3"><label class="form-label small">Descripción</label><textarea name="descripcion" class="form-control" rows="3" required><?php echo e(old('descripcion', $ticket->descripcion ?? '')); ?></textarea></div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Prioridad</label>
            <select name="prioridad" class="form-select">
                <?php $__currentLoopData = ['baja','media','alta','urgente']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p); ?>" <?php echo e(old('prioridad', $ticket->prioridad ?? 'media') == $p ? 'selected' : ''); ?>><?php echo e(ucfirst($p)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Técnico asignado</label>
            <select name="tecnico_id" class="form-select">
                <option value="">— Sin asignar —</option>
                <?php $__currentLoopData = $tecnicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tecnico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($tecnico->id); ?>" <?php echo e(old('tecnico_id', $ticket->tecnico_id ?? '') == $tecnico->id ? 'selected' : ''); ?>><?php echo e($tecnico->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
    <div class="mb-3"><label class="form-label small">Fecha programada</label><input type="datetime-local" name="fecha_programada" class="form-control" value="<?php echo e(old('fecha_programada', isset($ticket->fecha_programada) ? $ticket->fecha_programada->format('Y-m-d\TH:i') : '')); ?>"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/tickets/create.blade.php ENDPATH**/ ?>