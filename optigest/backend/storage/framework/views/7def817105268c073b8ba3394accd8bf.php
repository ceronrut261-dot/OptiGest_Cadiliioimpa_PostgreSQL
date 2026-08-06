<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .danger { background: #fdecea; }
    </style>
</head>
<body>
    <h1>Reporte de Inventario — OptiGest</h1>
    <p>Constru Fontanería Cadiliompa — Generado: <?php echo e($fecha->format('d/m/Y H:i')); ?></p>

    <table>
        <thead>
            <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Proveedor</th><th class="text-end">Precio</th><th class="text-end">Stock</th></tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($material->bajo_stock ? 'danger' : ''); ?>">
                    <td><?php echo e($material->codigo); ?></td>
                    <td><?php echo e($material->nombre); ?></td>
                    <td><?php echo e($material->categoria); ?></td>
                    <td><?php echo e($material->proveedor?->nombre ?? '-'); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($material->precio, 2)); ?></td>
                    <td class="text-end"><?php echo e($material->stock); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/reportes/stock-pdf.blade.php ENDPATH**/ ?>