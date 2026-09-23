<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }
        .encabezado { text-align: center; margin-bottom: 10px; }
        .encabezado img { width: 150px; }
        h1 { font-size: 15px; text-align: center; margin: 5px 0 15px; text-transform: uppercase; }
        .datos { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .datos td { padding: 3px 5px; vertical-align: top; }
        .datos .etiqueta { font-weight: bold; width: 120px; }
        table.detalle { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.detalle th, table.detalle td { border: 1px solid #999; padding: 4px 6px; }
        table.detalle th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .caja { border: 1px solid #999; padding: 8px; margin-bottom: 12px; min-height: 40px; }
        .firmas { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .firmas td { text-align: center; padding-top: 30px; border-top: 1px solid #333; font-size: 10px; width: 33%; }
        .firmas-fila td { border-top: none; padding: 0 15px; }
    </style>
</head>
<body>
    <div class="encabezado">
        <img src="<?php echo e(public_path('images/logo-cadiliompa.png')); ?>">
    </div>
    <h1>Salida de Materiales — <?php echo e($salida->codigo); ?></h1>

    <table class="datos">
        <tr>
            <td class="etiqueta">Personal que entrega:</td><td><?php echo e($salida->usuario->name); ?></td>
            <td class="etiqueta">Fecha:</td><td><?php echo e($salida->fecha->format('d/m/Y H:i')); ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Nombre del proyecto:</td><td><?php echo e($salida->proyecto); ?></td>
            <td class="etiqueta">Persona que recibe:</td><td><?php echo e($salida->persona_recibe); ?></td>
        </tr>
        <?php if($salida->ticket): ?>
        <tr>
            <td class="etiqueta">Ticket relacionado:</td><td colspan="3"><?php echo e($salida->ticket->codigo); ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <table class="detalle">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-end">Cantidad enviada</th>
                <th class="text-end">Valor unitario</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $salida->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($detalle->descripcion); ?></td>
                    <td class="text-end"><?php echo e($detalle->cantidad); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($detalle->valor_unitario, 2)); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($detalle->total, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Total</strong></td>
                <td class="text-end"><strong>Q<?php echo e(number_format($salida->total, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <p><strong>Observaciones:</strong></p>
    <div class="caja"><?php echo e($salida->observaciones ?: '—'); ?></div>

    <p><strong>¿Falta algo por comprar?</strong></p>
    <div class="caja"><?php echo e($salida->falta_comprar ?: 'No, la entrega está completa.'); ?></div>

    <table class="firmas">
        <tr class="firmas-fila"><td></td><td></td><td></td></tr>
        <tr>
            <td>Firma de entrega</td>
            <td>Firma de recibido de proyecto</td>
            <td>Firma de recibido</td>
        </tr>
    </table>
</body>
</html><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/salidas/pdf.blade.php ENDPATH**/ ?>