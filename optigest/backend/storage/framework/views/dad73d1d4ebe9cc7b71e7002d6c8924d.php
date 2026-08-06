<?php $__env->startSection('titulo', 'Reporte de Inventario'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Reporte Gerencial de Inventario</h2>
    <a href="<?php echo e(route('inventario.reportes.stock.pdf')); ?>" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Valor total de inventario</div>
                <div class="h4">Q<?php echo e(number_format($valorTotalInventario, 2)); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Materiales en alerta</div>
                <div class="h4 text-danger"><?php echo e($materialesBajoStock->count()); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Categorías</div>
                <div class="h4"><?php echo e($resumenPorCategoria->count()); ?></div>
            </div>
        </div>
    </div>
</div>

<h3 class="h6">Resumen por categoría</h3>
<div class="table-responsive mb-4">
    <table class="table table-sm bg-white">
        <thead class="table-light">
            <tr><th>Categoría</th><th class="text-end">Ítems</th><th class="text-end">Valor</th><th class="text-end">Bajo stock</th></tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $resumenPorCategoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $resumen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($categoria); ?></td>
                    <td class="text-end"><?php echo e($resumen['cantidad_items']); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($resumen['valor_total'], 2)); ?></td>
                    <td class="text-end"><?php echo e($resumen['bajo_stock']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<h3 class="h6">Materiales en alerta de bajo stock</h3>
<div class="table-responsive">
    <table class="table table-sm table-danger bg-white">
        <thead><tr><th>Código</th><th>Nombre</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $materialesBajoStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($material->codigo); ?></td>
                    <td><?php echo e($material->nombre); ?></td>
                    <td class="text-end"><?php echo e($material->stock); ?></td>
                    <td class="text-end"><?php echo e($material->stock_minimo); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center text-muted">Sin alertas de bajo stock.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/reportes/stock.blade.php ENDPATH**/ ?>