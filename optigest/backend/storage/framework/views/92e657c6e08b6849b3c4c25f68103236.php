<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .encabezado { text-align: center; margin-bottom: 10px; }
        .encabezado img { width: 150px; }
        h1 { font-size: 15px; margin: 5px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .entrada { color: #146c2e; }
        .salida { color: #a3231f; }
    </style>
</head>
<body>
    <div class="encabezado">
        <img src="<?php echo e(public_path('images/logo-cadiliompa.png')); ?>">
    </div>
    <h1>Reporte de Movimientos de Inventario</h1>
    <p>Constru Fontanería Cadiliompa — Generado: <?php echo e($fecha->format('d/m/Y H:i')); ?>

        <?php if($tipo): ?> — Filtro: <?php echo e(ucfirst($tipo)); ?> <?php endif; ?>
    </p>

    <table>
        <thead>
            <tr><th>Fecha</th><th>Material</th><th>Tipo</th><th class="text-end">Cantidad</th><th class="text-end">Stock resultante</th><th>Motivo</th><th>Usuario</th></tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($mov->fecha->format('d/m/Y H:i')); ?></td>
                    <td><?php echo e($mov->material->nombre); ?></td>
                    <td class="<?php echo e($mov->tipo); ?>"><?php echo e(ucfirst($mov->tipo)); ?></td>
                    <td class="text-end"><?php echo e($mov->cantidad); ?></td>
                    <td class="text-end"><?php echo e($mov->stock_resultante); ?></td>
                    <td><?php echo e($mov->motivo); ?></td>
                    <td><?php echo e($mov->usuario->name); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/inventario/movimientos/pdf.blade.php ENDPATH**/ ?>