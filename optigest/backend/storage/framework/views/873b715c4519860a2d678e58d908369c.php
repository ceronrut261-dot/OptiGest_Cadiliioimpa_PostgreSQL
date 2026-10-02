<?php $__env->startSection('titulo', 'Ticket ' . $ticket->codigo); ?>
<?php $__env->startSection('contenido'); ?>
<?php
    $q = fn ($n) => 'Q' . number_format((float) $n, 2);
    $tiposDoc = \App\Models\TicketGasto::TIPOS_DOCUMENTO;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Ticket <?php echo e($ticket->codigo); ?></h2>
    <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-outline-primary btn-sm">Editar</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> <?php echo e($ticket->cliente->nombre); ?>

            <a href="<?php echo e(route('clientes.edit', $ticket->cliente)); ?>" class="small">(editar)</a>
        </p>
        <?php if($ticket->cliente->telefono): ?>
            <p><strong>Teléfono:</strong> <?php echo e($ticket->cliente->telefono); ?></p>
        <?php endif; ?>
        <?php if($ticket->cliente->direccion): ?>
            <p><strong>Dirección:</strong> <?php echo e($ticket->cliente->direccion); ?></p>
        <?php endif; ?>
        <p><strong>Descripción:</strong> <?php echo e($ticket->descripcion); ?></p>

        <p class="mb-1"><strong>Equipo asignado:</strong></p>
        <?php $__empty_1 = true; $__currentLoopData = $ticket->tecnicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <span class="badge <?php echo e($t->pivot->rol === 'responsable' ? 'bg-primary' : 'bg-secondary'); ?> me-1">
                <?php echo e($t->name); ?> · <?php echo e($t->pivot->rol === 'responsable' ? 'responsable' : 'apoyo'); ?>

                <?php if($veFinanzas && (float) $t->pivot->monto_trato > 0): ?>
                    · <?php echo e($q($t->pivot->monto_trato)); ?>

                <?php endif; ?>
            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <span class="text-muted">— Sin asignar —</span>
        <?php endif; ?>

        <p class="mt-3"><strong>Estado actual:</strong> <span class="badge bg-secondary text-capitalize"><?php echo e(str_replace('_',' ',$ticket->estado)); ?></span></p>

        <form action="<?php echo e(route('tickets.estado', $ticket)); ?>" method="POST" class="d-flex gap-2 mt-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <select name="estado" class="form-select form-select-sm" style="max-width:220px;">
                <?php $__currentLoopData = ['pendiente','asignado','en_proceso','completado','cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($estado); ?>" <?php echo e($ticket->estado == $estado ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_',' ',$estado))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn-sm btn-primary">Actualizar estado</button>
        </form>
    </div>
</div>


<?php if($veFinanzas): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Resumen financiero del ticket</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="small text-muted">Ingreso (cotizaciones aprobadas, sin IVA)</div>
                <div class="fs-5 fw-semibold"><?php echo e($q($resumen['ingreso_aprobado'])); ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Pago a técnicos (por trato)</div>
                <div class="fs-5 fw-semibold"><?php echo e($q($resumen['pago_tecnicos'])); ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Gastos adicionales</div>
                <div class="fs-5 fw-semibold"><?php echo e($q($resumen['gastos_total'])); ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Materiales de bodega</div>
                <div class="fs-5 fw-semibold"><?php echo e($q($resumen['materiales_bodega'])); ?></div>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <span>Utilidad estimada:
                <strong class="<?php echo e($resumen['utilidad'] < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e($q($resumen['utilidad'])); ?></strong>
                <?php if (! ($resumen['hay_cotizacion_aprobada'])): ?>
                    <span class="text-muted small">(aún no hay cotización aprobada)</span>
                <?php endif; ?>
            </span>
            <?php if($resumen['gastos_pendientes_cobro'] > 0): ?>
                <span class="text-warning-emphasis">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo e($q($resumen['gastos_pendientes_cobro'])); ?> de gastos cobrables aún no están en ninguna cotización.
                    <a href="<?php echo e(route('cotizaciones.create')); ?>">Crear cotización</a>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if($ticket->salidas->isNotEmpty()): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Salidas de materiales de este ticket</div>
    <ul class="list-group list-group-flush">
        <?php $__currentLoopData = $ticket->salidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salida): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span><?php echo e($salida->codigo); ?> · recibió <?php echo e($salida->persona_recibe); ?></span>
                    <a href="<?php echo e(route('salidas.pdf', $salida)); ?>" class="small">Vale PDF</a>
                </div>
                <?php if($salida->falta_comprar): ?>
                    <div class="mt-1 small">
                        <span class="badge bg-warning text-dark">Faltó comprar</span>
                        <?php echo e($salida->falta_comprar); ?>

                        <?php if($puedeEditar): ?>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 js-registrar-compra"
                                data-salida="<?php echo e($salida->id); ?>" data-descripcion="<?php echo e($salida->falta_comprar); ?>">
                                Registrar compra
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>


<div class="card border-0 shadow-sm mb-3" id="gastos">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Gastos adicionales y comprobantes</span>
        <?php if($ticket->gastos->isNotEmpty()): ?>
            <a href="<?php echo e(route('tickets.gastos.reporte', $ticket)); ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Descargar reporte PDF
            </a>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th><th>Detalle</th><th>Documento</th>
                    <th class="text-end">Monto</th><th>Cobro</th><th>Comprobante</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $ticket->gastos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gasto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-nowrap"><?php echo e($gasto->fecha_gasto->format('d/m/Y')); ?></td>
                    <td>
                        <?php echo e($gasto->descripcion); ?>

                        <?php if($gasto->comercio): ?><div class="small text-muted"><?php echo e($gasto->comercio); ?></div><?php endif; ?>
                        <div class="small text-muted">Registró: <?php echo e($gasto->registrador->name ?? '—'); ?></div>
                    </td>
                    <td class="small">
                        <?php echo e($tiposDoc[$gasto->tipo_documento] ?? $gasto->tipo_documento); ?>

                        <?php if($gasto->numero_documento): ?><div class="text-muted"><?php echo e($gasto->numero_documento); ?></div><?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap"><?php echo e($q($gasto->monto)); ?></td>
                    <td class="small">
                        <?php if($gasto->cobrar_al_cliente): ?>
                            <?php if($gasto->cotizacion): ?>
                                <a href="<?php echo e(route('cotizaciones.show', $gasto->cotizacion)); ?>"><?php echo e($gasto->cotizacion->codigo); ?></a>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente de cotizar</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">Gasto de la empresa</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($gasto->tiene_archivo): ?>
                            <?php if($gasto->es_imagen): ?>
                                <a href="<?php echo e(route('tickets.gastos.archivo', [$ticket, $gasto])); ?>" target="_blank">
                                    <img src="<?php echo e(route('tickets.gastos.archivo', [$ticket, $gasto])); ?>" alt="Comprobante"
                                         style="height:48px;width:48px;object-fit:cover;" class="rounded border">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('tickets.gastos.archivo', [$ticket, $gasto])); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                </a>
                            <?php endif; ?>
                        <?php elseif($puedeEditar): ?>
                            <form action="<?php echo e(route('tickets.gastos.adjuntar', [$ticket, $gasto])); ?>" method="POST" enctype="multipart/form-data" class="d-flex gap-1">
                                <?php echo csrf_field(); ?>
                                <span class="badge bg-danger align-self-center">Falta</span>
                                <label class="btn btn-outline-primary btn-sm mb-0" title="Tomar foto">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" name="foto" accept="image/*" capture="environment" class="d-none js-auto-envio js-comprimir">
                                </label>
                                <label class="btn btn-outline-secondary btn-sm mb-0" title="Adjuntar archivo">
                                    <i class="bi bi-paperclip"></i>
                                    <input type="file" name="archivo" accept="image/*,application/pdf" class="d-none js-auto-envio js-comprimir">
                                </label>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-danger">Falta</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if($veFinanzas): ?>
                            <form action="<?php echo e(route('tickets.gastos.destroy', [$ticket, $gasto])); ?>" method="POST"
                                  onsubmit="return confirm('¿Eliminar este gasto y su comprobante?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-link text-danger btn-sm p-0" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-muted text-center py-3">Aún no hay gastos adicionales en este ticket.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if($ticket->gastos->isNotEmpty()): ?>
            <tfoot>
                <tr class="table-light">
                    <th colspan="3" class="text-end">Total gastos</th>
                    <th class="text-end"><?php echo e($q($ticket->gastos->sum('monto'))); ?></th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <?php if($puedeEditar): ?>
    <div class="card-body border-top">
        <h3 class="h6">Registrar un gasto (compra de material, transporte, etc.)</h3>
        <form action="<?php echo e(route('tickets.gastos.store', $ticket)); ?>" method="POST" enctype="multipart/form-data" id="form-gasto">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="salida_id" id="gasto-salida" value="">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">¿Qué se compró o pagó?</label>
                    <input type="text" name="descripcion" id="gasto-descripcion" class="form-control form-control-sm" required maxlength="255" value="<?php echo e(old('descripcion')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Monto (Q)</label>
                    <input type="number" step="0.01" min="0" name="monto" class="form-control form-control-sm" required value="<?php echo e(old('monto')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fecha</label>
                    <input type="date" name="fecha_gasto" max="<?php echo e(now()->toDateString()); ?>" class="form-control form-control-sm" required value="<?php echo e(old('fecha_gasto', now()->toDateString())); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Comercio / proveedor</label>
                    <input type="text" name="comercio" class="form-control form-control-sm" maxlength="255" value="<?php echo e(old('comercio')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Tipo de documento</label>
                    <select name="tipo_documento" class="form-select form-select-sm">
                        <?php $__currentLoopData = $tiposDoc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($valor); ?>" <?php echo e(old('tipo_documento', 'factura') === $valor ? 'selected' : ''); ?>><?php echo e($etiqueta); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">No. de documento</label>
                    <input type="text" name="numero_documento" class="form-control form-control-sm" maxlength="60" value="<?php echo e(old('numero_documento')); ?>">
                </div>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                <label class="btn btn-outline-primary btn-sm mb-0">
                    <i class="bi bi-camera"></i> Tomar foto
                    <input type="file" name="foto" accept="image/*" capture="environment" class="d-none js-comprimir js-nombre-archivo">
                </label>
                <label class="btn btn-outline-secondary btn-sm mb-0">
                    <i class="bi bi-paperclip"></i> Adjuntar foto o PDF
                    <input type="file" name="archivo" accept="image/*,application/pdf" class="d-none js-comprimir js-nombre-archivo">
                </label>
                <span class="small text-muted" id="nombre-archivo">Sin archivo (puedes adjuntarlo después).</span>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="cobrar_al_cliente" value="1" id="cobrar" <?php echo e(old('cobrar_al_cliente', '1') ? 'checked' : ''); ?>>
                <label class="form-check-label small" for="cobrar">
                    Cobrar este gasto al cliente (se suma automáticamente a la cotización abierta de este ticket)
                </label>
            </div>
            <button class="btn btn-primary btn-sm mt-3">Guardar gasto</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<h3 class="h6">Cotizaciones relacionadas</h3>
<ul class="list-group">
    <?php $__empty_1 = true; $__currentLoopData = $ticket->cotizaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="list-group-item d-flex justify-content-between">
            <a href="<?php echo e(route('cotizaciones.show', $cot)); ?>"><?php echo e($cot->codigo); ?></a>
            <span>
                <?php if($veFinanzas): ?><span class="me-2"><?php echo e($q($cot->total)); ?></span><?php endif; ?>
                <span class="badge bg-secondary text-capitalize"><?php echo e($cot->estado); ?></span>
            </span>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="list-group-item text-muted">Sin cotizaciones asociadas.</li>
    <?php endif; ?>
</ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Reduce las fotos de la cámara antes de subirlas (los celulares generan
// fotos de varios MB y PHP suele limitar la subida a 2 MB por defecto).
async function comprimirImagen(archivo, maxLado = 1600, calidad = 0.8) {
    if (!archivo.type.startsWith('image/')) return archivo;
    try {
        const bmp = await createImageBitmap(archivo);
        const escala = Math.min(1, maxLado / Math.max(bmp.width, bmp.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bmp.width * escala);
        canvas.height = Math.round(bmp.height * escala);
        canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', calidad));
        if (!blob || blob.size >= archivo.size) return archivo;
        return new File([blob], archivo.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
    } catch (e) {
        return archivo; // si el navegador no puede, se sube tal cual
    }
}

document.querySelectorAll('.js-comprimir').forEach(input => {
    input.addEventListener('change', async () => {
        if (!input.files.length) return;
        const nuevo = await comprimirImagen(input.files[0]);
        const dt = new DataTransfer();
        dt.items.add(nuevo);
        input.files = dt.files;

        // Solo un archivo por gasto: se limpia el otro selector.
        const form = input.closest('form');
        form.querySelectorAll('.js-comprimir').forEach(o => { if (o !== input) o.value = ''; });

        const etiqueta = document.getElementById('nombre-archivo');
        if (etiqueta && input.classList.contains('js-nombre-archivo')) {
            etiqueta.textContent = 'Adjunto: ' + nuevo.name;
        }
        if (input.classList.contains('js-auto-envio')) form.submit();
    });
});

// "Registrar compra" desde una salida con faltante: precarga el formulario.
document.querySelectorAll('.js-registrar-compra').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('gasto-salida').value = btn.dataset.salida;
        document.getElementById('gasto-descripcion').value = btn.dataset.descripcion;
        document.getElementById('form-gasto').scrollIntoView({ behavior: 'smooth' });
        document.getElementById('gasto-descripcion').focus();
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/tickets/show.blade.php ENDPATH**/ ?>