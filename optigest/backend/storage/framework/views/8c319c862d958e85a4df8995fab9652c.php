<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h1>Cotización <?php echo e($cotizacion->codigo); ?> — OptiGest</h1>
    <p>Constru Fontanería Cadiliompa</p>
    <p><strong>Cliente:</strong> <?php echo e($cotizacion->cliente); ?> &nbsp; <strong>Fecha:</strong> <?php echo e($cotizacion->fecha->format('d/m/Y')); ?></p>

    <table>
        <thead><tr><th>Material</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $cotizacion->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($detalle->material->nombre); ?></td>
                <td class="text-end"><?php echo e($detalle->cantidad); ?></td>
                <td class="text-end">Q<?php echo e(number_format($detalle->precio_unitario,2)); ?></td>
                <td class="text-end">Q<?php echo e(number_format($detalle->subtotal,2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <p class="text-end"><strong>Total: Q<?php echo e(number_format($cotizacion->total,2)); ?></strong></p>
</body>
</html>
<?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/cotizaciones/pdf.blade.php ENDPATH**/ ?>