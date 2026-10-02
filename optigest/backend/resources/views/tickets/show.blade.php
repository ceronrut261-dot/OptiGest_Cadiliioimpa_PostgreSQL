@extends('layouts.app')
@section('titulo', 'Ticket ' . $ticket->codigo)
@section('contenido')
@php
    $q = fn ($n) => 'Q' . number_format((float) $n, 2);
    $tiposDoc = \App\Models\TicketGasto::TIPOS_DOCUMENTO;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Ticket {{ $ticket->codigo }}</h2>
    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-primary btn-sm">Editar</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> {{ $ticket->cliente->nombre }}
            <a href="{{ route('clientes.edit', $ticket->cliente) }}" class="small">(editar)</a>
        </p>
        @if($ticket->cliente->telefono)
            <p><strong>Teléfono:</strong> {{ $ticket->cliente->telefono }}</p>
        @endif
        @if($ticket->cliente->direccion)
            <p><strong>Dirección:</strong> {{ $ticket->cliente->direccion }}</p>
        @endif
        <p><strong>Descripción:</strong> {{ $ticket->descripcion }}</p>

        <p class="mb-1"><strong>Equipo asignado:</strong></p>
        @forelse($ticket->tecnicos as $t)
            <span class="badge {{ $t->pivot->rol === 'responsable' ? 'bg-primary' : 'bg-secondary' }} me-1">
                {{ $t->name }} · {{ $t->pivot->rol === 'responsable' ? 'responsable' : 'apoyo' }}
                @if($veFinanzas && (float) $t->pivot->monto_trato > 0)
                    · {{ $q($t->pivot->monto_trato) }}
                @endif
            </span>
        @empty
            <span class="text-muted">— Sin asignar —</span>
        @endforelse

        <p class="mt-3"><strong>Estado actual:</strong> <span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ',$ticket->estado) }}</span></p>

        <form action="{{ route('tickets.estado', $ticket) }}" method="POST" class="d-flex gap-2 mt-3">
            @csrf @method('PATCH')
            <select name="estado" class="form-select form-select-sm" style="max-width:220px;">
                @foreach(['pendiente','asignado','en_proceso','completado','cancelado'] as $estado)
                    <option value="{{ $estado }}" {{ $ticket->estado == $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Actualizar estado</button>
        </form>
    </div>
</div>

{{-- ============ Resumen financiero (solo administrador / cotizador) ============ --}}
@if($veFinanzas)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Resumen financiero del ticket</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="small text-muted">Ingreso (cotizaciones aprobadas, sin IVA)</div>
                <div class="fs-5 fw-semibold">{{ $q($resumen['ingreso_aprobado']) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Pago a técnicos (por trato)</div>
                <div class="fs-5 fw-semibold">{{ $q($resumen['pago_tecnicos']) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Gastos adicionales</div>
                <div class="fs-5 fw-semibold">{{ $q($resumen['gastos_total']) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">Materiales de bodega</div>
                <div class="fs-5 fw-semibold">{{ $q($resumen['materiales_bodega']) }}</div>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <span>Utilidad estimada:
                <strong class="{{ $resumen['utilidad'] < 0 ? 'text-danger' : 'text-success' }}">{{ $q($resumen['utilidad']) }}</strong>
                @unless($resumen['hay_cotizacion_aprobada'])
                    <span class="text-muted small">(aún no hay cotización aprobada)</span>
                @endunless
            </span>
            @if($resumen['gastos_pendientes_cobro'] > 0)
                <span class="text-warning-emphasis">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ $q($resumen['gastos_pendientes_cobro']) }} de gastos cobrables aún no están en ninguna cotización.
                    <a href="{{ route('cotizaciones.create') }}">Crear cotización</a>
                </span>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ============ Materiales entregados / faltantes ============ --}}
@if($ticket->salidas->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Salidas de materiales de este ticket</div>
    <ul class="list-group list-group-flush">
        @foreach($ticket->salidas as $salida)
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>{{ $salida->codigo }} · recibió {{ $salida->persona_recibe }}</span>
                    <a href="{{ route('salidas.pdf', $salida) }}" class="small">Vale PDF</a>
                </div>
                @if($salida->falta_comprar)
                    <div class="mt-1 small">
                        <span class="badge bg-warning text-dark">Faltó comprar</span>
                        {{ $salida->falta_comprar }}
                        @if($puedeEditar)
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 js-registrar-compra"
                                data-salida="{{ $salida->id }}" data-descripcion="{{ $salida->falta_comprar }}">
                                Registrar compra
                            </button>
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endif

{{-- ============ Gastos adicionales y comprobantes ============ --}}
<div class="card border-0 shadow-sm mb-3" id="gastos">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Gastos adicionales y comprobantes</span>
        @if($ticket->gastos->isNotEmpty())
            <a href="{{ route('tickets.gastos.reporte', $ticket) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Descargar reporte PDF
            </a>
        @endif
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
            @forelse($ticket->gastos as $gasto)
                <tr>
                    <td class="text-nowrap">{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                    <td>
                        {{ $gasto->descripcion }}
                        @if($gasto->comercio)<div class="small text-muted">{{ $gasto->comercio }}</div>@endif
                        <div class="small text-muted">Registró: {{ $gasto->registrador->name ?? '—' }}</div>
                    </td>
                    <td class="small">
                        {{ $tiposDoc[$gasto->tipo_documento] ?? $gasto->tipo_documento }}
                        @if($gasto->numero_documento)<div class="text-muted">{{ $gasto->numero_documento }}</div>@endif
                    </td>
                    <td class="text-end text-nowrap">{{ $q($gasto->monto) }}</td>
                    <td class="small">
                        @if($gasto->cobrar_al_cliente)
                            @if($gasto->cotizacion)
                                <a href="{{ route('cotizaciones.show', $gasto->cotizacion) }}">{{ $gasto->cotizacion->codigo }}</a>
                            @else
                                <span class="badge bg-warning text-dark">Pendiente de cotizar</span>
                            @endif
                        @else
                            <span class="text-muted">Gasto de la empresa</span>
                        @endif
                    </td>
                    <td>
                        @if($gasto->tiene_archivo)
                            @if($gasto->es_imagen)
                                <a href="{{ route('tickets.gastos.archivo', [$ticket, $gasto]) }}" target="_blank">
                                    <img src="{{ route('tickets.gastos.archivo', [$ticket, $gasto]) }}" alt="Comprobante"
                                         style="height:48px;width:48px;object-fit:cover;" class="rounded border">
                                </a>
                            @else
                                <a href="{{ route('tickets.gastos.archivo', [$ticket, $gasto]) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                </a>
                            @endif
                        @elseif($puedeEditar)
                            <form action="{{ route('tickets.gastos.adjuntar', [$ticket, $gasto]) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-1">
                                @csrf
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
                        @else
                            <span class="badge bg-danger">Falta</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($veFinanzas)
                            <form action="{{ route('tickets.gastos.destroy', [$ticket, $gasto]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este gasto y su comprobante?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-link text-danger btn-sm p-0" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-muted text-center py-3">Aún no hay gastos adicionales en este ticket.</td></tr>
            @endforelse
            </tbody>
            @if($ticket->gastos->isNotEmpty())
            <tfoot>
                <tr class="table-light">
                    <th colspan="3" class="text-end">Total gastos</th>
                    <th class="text-end">{{ $q($ticket->gastos->sum('monto')) }}</th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($puedeEditar)
    <div class="card-body border-top">
        <h3 class="h6">Registrar un gasto (compra de material, transporte, etc.)</h3>
        <form action="{{ route('tickets.gastos.store', $ticket) }}" method="POST" enctype="multipart/form-data" id="form-gasto">
            @csrf
            <input type="hidden" name="salida_id" id="gasto-salida" value="">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">¿Qué se compró o pagó?</label>
                    <input type="text" name="descripcion" id="gasto-descripcion" class="form-control form-control-sm" required maxlength="255" value="{{ old('descripcion') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Monto (Q)</label>
                    <input type="number" step="0.01" min="0" name="monto" class="form-control form-control-sm" required value="{{ old('monto') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fecha</label>
                    <input type="date" name="fecha_gasto" max="{{ now()->toDateString() }}" class="form-control form-control-sm" required value="{{ old('fecha_gasto', now()->toDateString()) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Comercio / proveedor</label>
                    <input type="text" name="comercio" class="form-control form-control-sm" maxlength="255" value="{{ old('comercio') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Tipo de documento</label>
                    <select name="tipo_documento" class="form-select form-select-sm">
                        @foreach($tiposDoc as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ old('tipo_documento', 'factura') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">No. de documento</label>
                    <input type="text" name="numero_documento" class="form-control form-control-sm" maxlength="60" value="{{ old('numero_documento') }}">
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
                <input class="form-check-input" type="checkbox" name="cobrar_al_cliente" value="1" id="cobrar" {{ old('cobrar_al_cliente', '1') ? 'checked' : '' }}>
                <label class="form-check-label small" for="cobrar">
                    Cobrar este gasto al cliente (se suma automáticamente a la cotización abierta de este ticket)
                </label>
            </div>
            <button class="btn btn-primary btn-sm mt-3">Guardar gasto</button>
        </form>
    </div>
    @endif
</div>

<h3 class="h6">Cotizaciones relacionadas</h3>
<ul class="list-group">
    @forelse($ticket->cotizaciones as $cot)
        <li class="list-group-item d-flex justify-content-between">
            <a href="{{ route('cotizaciones.show', $cot) }}">{{ $cot->codigo }}</a>
            <span>
                @if($veFinanzas)<span class="me-2">{{ $q($cot->total) }}</span>@endif
                <span class="badge bg-secondary text-capitalize">{{ $cot->estado }}</span>
            </span>
        </li>
    @empty
        <li class="list-group-item text-muted">Sin cotizaciones asociadas.</li>
    @endforelse
</ul>
@endsection

@section('scripts')
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
@endsection
