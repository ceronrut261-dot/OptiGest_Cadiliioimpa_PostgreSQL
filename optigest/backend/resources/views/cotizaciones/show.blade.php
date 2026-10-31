
@extends('layouts.app')

@section('titulo', 'Cotización ' . $cotizacion->codigo)

@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="h4 mb-0">Cotización {{ $cotizacion->codigo }}</h2>

    <div class="d-flex flex-wrap gap-2">
        {{-- Descargar PDF --}}
        <a href="{{ route('cotizaciones.pdf', $cotizacion) }}"
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>

        {{-- Editar solamente en borrador --}}
        @if ($cotizacion->estado === 'borrador')
            <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
               class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square"></i> Editar cotización
            </a>
        @endif

        {{-- Eliminar en cualquier estado --}}
        <form action="{{ route('cotizaciones.destroy', $cotizacion) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('¿Deseas eliminar la cotización {{ $cotizacion->codigo }}? Esta acción no se puede deshacer.');">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </form>

        {{-- Aprobar o rechazar borradores y cotizaciones enviadas --}}
        @if (in_array($cotizacion->estado, ['borrador', 'enviada'], true))
            <form action="{{ route('cotizaciones.aprobar', $cotizacion) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('¿Deseas aprobar esta cotización?');">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check-circle"></i> Aprobar
                </button>
            </form>

            <form action="{{ route('cotizaciones.rechazar', $cotizacion) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('¿Deseas rechazar esta cotización?');">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle"></i> Rechazar
                </button>
            </form>
        @endif

        {{-- Revertir cotizaciones aprobadas o rechazadas --}}
        @if (in_array($cotizacion->estado, ['aprobada', 'rechazada'], true))
            <form action="{{ route('cotizaciones.revertir', $cotizacion) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('¿Deseas regresar la cotización {{ $cotizacion->codigo }} a estado borrador?');">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Revertir
                </button>
            </form>
        @endif

        {{-- Volver al listado --}}
        <a href="{{ route('cotizaciones.index') }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Mensajes de confirmación --}}
@if (session('success') || session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') ?? session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Cerrar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Cerrar"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>No se pudo completar la operación:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Información general --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Cliente:</strong>
                    {{ $cotizacion->cliente->nombre ?? 'Sin cliente' }}
                </p>

                <p class="mb-2">
                    <strong>Estado:</strong>

                    @php
                        $coloresEstado = [
                            'borrador' => 'secondary',
                            'enviada' => 'primary',
                            'aprobada' => 'success',
                            'rechazada' => 'danger',
                        ];
                    @endphp

                    <span class="badge bg-{{ $coloresEstado[$cotizacion->estado] ?? 'secondary' }} text-capitalize">
                        {{ $cotizacion->estado }}
                    </span>
                </p>

                <p class="mb-0">
                    <strong>Cotizador:</strong>
                    {{ $cotizacion->cotizador->name ?? 'No asignado' }}
                </p>
            </div>

            <div class="col-md-6">
                @if ($cotizacion->ticket)
                    <p class="mb-2">
                        <strong>Ticket relacionado:</strong>
                        <a href="{{ route('tickets.show', $cotizacion->ticket) }}">
                            {{ $cotizacion->ticket->codigo }}
                        </a>
                    </p>
                @else
                    <p class="mb-2">
                        <strong>Ticket relacionado:</strong> Ninguno
                    </p>
                @endif

                @if ($cotizacion->fecha)
                    <p class="mb-0">
                        <strong>Fecha:</strong>
                        {{ $cotizacion->fecha->format('d/m/Y') }}
                    </p>
                @endif
            </div>
        </div>

        @if ($cotizacion->observaciones)
            <hr>
            <p class="mb-0">
                <strong>Observaciones:</strong><br>
                {{ $cotizacion->observaciones }}
            </p>
        @endif
    </div>
</div>

{{-- Detalle de la cotización --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Detalle de la cotización</strong>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Concepto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cotizacion->detalles as $detalle)
                        <tr>
                            <td>
                                {{ $detalle->material->nombre ?? 'Material no disponible' }}
                            </td>
                            <td class="text-end">
                                {{ $detalle->cantidad }}
                            </td>
                            <td class="text-end">
                                Q{{ number_format($detalle->precio_unitario, 2) }}
                            </td>
                            <td class="text-end">
                                Q{{ number_format($detalle->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        @if ($cotizacion->servicios->isEmpty() && $cotizacion->gastos->where('cobrar_al_cliente', true)->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    No hay conceptos registrados en esta cotización.
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    @foreach ($cotizacion->servicios as $servicio)
                        <tr>
                            <td>
                                {{ $servicio->descripcion }}
                                <span class="badge bg-info text-dark">Mano de obra</span>
                            </td>
                            <td class="text-end">
                                {{ rtrim(rtrim(number_format($servicio->cantidad, 2), '0'), '.') }}
                            </td>
                            <td class="text-end">
                                Q{{ number_format($servicio->precio_unitario, 2) }}
                            </td>
                            <td class="text-end">
                                Q{{ number_format($servicio->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach

                    @foreach ($cotizacion->gastos->where('cobrar_al_cliente', true) as $gasto)
                        <tr>
                            <td>
                                {{ $gasto->descripcion }}
                                <span class="badge bg-warning text-dark">Gasto adicional</span>
                            </td>
                            <td class="text-end">1</td>
                            <td class="text-end">
                                Q{{ number_format($gasto->monto, 2) }}
                            </td>
                            <td class="text-end">
                                Q{{ number_format($gasto->monto, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Mano de obra</th>
                        <th class="text-end">
                            Q{{ number_format($cotizacion->mano_obra_total, 2) }}
                        </th>
                    </tr>

                    <tr>
                        <th colspan="3" class="text-end">Gastos adicionales</th>
                        <th class="text-end">
                            Q{{ number_format($cotizacion->gastos_adicionales_total, 2) }}
                        </th>
                    </tr>

                    <tr>
                        <th colspan="3" class="text-end">Subtotal</th>
                        <th class="text-end">
                            Q{{ number_format($cotizacion->subtotal, 2) }}
                        </th>
                    </tr>

                    @if ($cotizacion->iva_aplicado)
                        <tr>
                            <td colspan="3" class="text-end">IVA</td>
                            <td class="text-end">
                                Q{{ number_format($cotizacion->iva_monto, 2) }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">
                            Q{{ number_format($cotizacion->total, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
