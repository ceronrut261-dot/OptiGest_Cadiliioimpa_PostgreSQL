<?php $__env->startSection('titulo', 'Nueva cotización'); ?>
<?php $__env->startSection('contenido'); ?>
<h2 class="h4 mb-4">Nueva cotización</h2>

<form method="POST" action="<?php echo e(route('cotizaciones.store')); ?>" class="bg-white p-4 rounded shadow-sm" style="max-width:720px;" id="form-cotizacion">
    <?php echo csrf_field(); ?>

    <div class="mb-3"><label class="form-label small">Cliente</label><input type="text" name="cliente" class="form-control" required></div>

    <div class="mb-3">
        <label class="form-label small">Ticket relacionado (opcional)</label>
        <select name="ticket_id" class="form-select">
            <option value="">— Ninguno —</option>
            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($ticket->id); ?>"><?php echo e($ticket->codigo); ?> — <?php echo e($ticket->cliente); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <label class="form-label small">Materiales</label>
    <div id="lineas-materiales">
        <div class="row g-2 mb-2 linea-material">
            <div class="col-7">
                <select name="materiales[0][id]" class="form-select form-select-sm">
                    <?php $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($material->id); ?>"><?php echo e($material->nombre); ?> (stock: <?php echo e($material->stock); ?>) — Q<?php echo e(number_format($material->precio,2)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="materiales[0][cantidad]" min="1" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="agregar-linea">+ Agregar material</button>

    <div class="mb-3"><label class="form-label small">Observaciones</label><textarea name="observaciones" class="form-control" rows="2"></textarea></div>

    <button type="submit" class="btn btn-primary">Crear cotización (borrador)</button>
    <a href="<?php echo e(route('cotizaciones.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
</form>

<script>
let contador = 1;
document.getElementById('agregar-linea').addEventListener('click', function () {
    const contenedor = document.getElementById('lineas-materiales');
    const original = contenedor.querySelector('.linea-material');
    const clon = original.cloneNode(true);
    clon.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${contador}]`);
        if (el.tagName === 'INPUT') el.value = 1;
    });
    contenedor.appendChild(clon);
    contador++;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/cotizaciones/create.blade.php ENDPATH**/ ?>