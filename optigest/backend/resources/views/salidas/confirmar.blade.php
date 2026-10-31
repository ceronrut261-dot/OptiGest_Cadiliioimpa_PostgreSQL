
@extends('layouts.app')

@section('titulo', 'Confirmar entrega de materiales')

@section('contenido')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Confirmar entrega de materiales</h2>
            <p class="text-muted mb-0">
                Vale: {{ $salida->codigo }}
            </p>
        </div>

        <a href="{{ route('salidas.index') }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
            Volver a salidas
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>No se pudo procesar la confirmación.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <strong>Información del vale</strong>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Proyecto</label>
                    <div class="fw-semibold">{{ $salida->proyecto }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted">Persona que recibe</label>
                    <div class="fw-semibold">{{ $salida->persona_recibe }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted">Registró el vale</label>
                    <div>{{ $salida->usuario?->name ?? 'No disponible' }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted">Fecha del vale</label>
                    <div>
                        {{ $salida->fecha?->format('d/m/Y H:i') ?? 'No disponible' }}
                    </div>
                </div>

                @if ($salida->ticket)
                    <div class="col-md-6">
                        <label class="form-label text-muted">Ticket asociado</label>
                        <div>{{ $salida->ticket->id }}</div>
                    </div>
                @endif

                @if ($salida->observaciones)
                    <div class="col-12">
                        <label class="form-label text-muted">Observaciones del vale</label>
                        <div>{{ $salida->observaciones }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('salidas.confirmar.guardar', $salida) }}"
          onsubmit="return confirm('¿Confirmas que las cantidades indicadas son las que se recibieron realmente?');">

        @csrf
        @method('PATCH')

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Materiales recibidos</strong>
            </div>

            <div class="card-body">
                <p class="text-muted small">
                    Indica la cantidad que se recibió realmente de cada material.
                    Si no se recibió ningún artículo de una línea, coloca 0.
                    La cantidad recibida no puede superar la cantidad solicitada.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th class="text-center">Solicitado</th>
                                <th class="text-center" style="width: 190px;">
                                    Cantidad recibida
                                </th>
                                <th class="text-center">Pendiente de entregar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($salida->detalles as $detalle)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $detalle->descripcion }}
                                        </div>

                                        @if ($detalle->material)
                                            <small class="text-muted">
                                                Código: {{ $detalle->material->codigo }}
                                            </small>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{ $detalle->cantidad }}
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="form-control cantidad-recibida"
                                            name="entregadas[{{ $detalle->id }}]"
                                            min="0"
                                            max="{{ $detalle->cantidad }}"
                                            step="1"
                                            value="{{ old('entregadas.' . $detalle->id, $detalle->cantidad) }}"
                                            required
                                            data-solicitada="{{ $detalle->cantidad }}"
                                        >

                                        @error('entregadas.' . $detalle->id)
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary pendiente-cantidad">
                                            0
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Observaciones de la entrega</strong>
            </div>

            <div class="card-body">
                <label for="observaciones_entrega" class="form-label">
                    Observaciones (opcional)
                </label>

                <textarea
                    id="observaciones_entrega"
                    name="observaciones_entrega"
                    class="form-control"
                    rows="3"
                    maxlength="1000"
                    placeholder="Ejemplo: se recibieron menos unidades de las solicitadas."
                >{{ old('observaciones_entrega') }}</textarea>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
            <a href="{{ route('salidas.index') }}"
               class="btn btn-outline-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i>
                Confirmar entrega
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cantidades = document.querySelectorAll('.cantidad-recibida');

    function actualizarPendiente(input) {
        const solicitada = Number(input.dataset.solicitada);
        const recibida = Number(input.value);
        const fila = input.closest('tr');
        const etiqueta = fila.querySelector('.pendiente-cantidad');

        const pendiente = Math.max(0, solicitada - recibida);

        etiqueta.textContent = pendiente;

        etiqueta.classList.toggle('bg-warning', pendiente > 0);
        etiqueta.classList.toggle('text-dark', pendiente > 0);
        etiqueta.classList.toggle('bg-secondary', pendiente === 0);
    }

    cantidades.forEach(function (input) {
        actualizarPendiente(input);

        input.addEventListener('input', function () {
            actualizarPendiente(input);
        });
    });
});
</script>
@endsection
