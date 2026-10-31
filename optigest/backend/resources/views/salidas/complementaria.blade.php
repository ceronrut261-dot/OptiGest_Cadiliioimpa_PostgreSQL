@extends('layouts.app')

@section('titulo', 'Vale complementario')

@section('contenido')

<div class="container-fluid">

    <h2 class="h4 mb-3">Generar vale complementario</h2>

    <div class="alert alert-info">
        <strong>Vale original:</strong> {{ $salida->codigo }}<br>
        <strong>Proyecto:</strong> {{ $salida->proyecto }}<br>
        <strong>Persona que recibe:</strong> {{ $salida->persona_recibe }}

        <p class="mb-0 mt-2">
            Selecciona únicamente las cantidades que todavía están pendientes
            de entregar.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($pendientes->isEmpty())
        <div class="alert alert-success">
            No hay materiales pendientes de entregar para este vale.
        </div>

        <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
            Volver a vales
        </a>
    @else

        <form
            method="POST"
            action="{{ route('salidas.complementaria.store', $salida) }}"
            class="bg-white p-4 rounded shadow-sm"
            style="max-width: 900px;"
        >
            @csrf

            <h5 class="mb-3">Materiales pendientes</h5>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th class="text-center">Solicitado</th>
                            <th class="text-center">Entregado</th>
                            <th class="text-center">Pendiente</th>
                            <th class="text-center">Stock actual</th>
                            <th style="width: 160px;">Cantidad a entregar</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($pendientes as $index => $material)
                            @php
                                $maximo = min(
                                    (int) $material->cantidad_pendiente,
                                    (int) $material->stock
                                );
                            @endphp

                            <tr>
                                <td>
                                    {{ $material->descripcion }}

                                    <input
                                        type="hidden"
                                        name="materiales[{{ $index }}][id]"
                                        value="{{ $material->material_id }}"
                                    >
                                </td>

                                <td class="text-center">
                                    {{ $material->cantidad_solicitada }}
                                </td>

                                <td class="text-center">
                                    {{ $material->cantidad_entregada }}
                                </td>

                                <td class="text-center fw-bold">
                                    {{ $material->cantidad_pendiente }}
                                </td>

                                <td class="text-center">
                                    {{ $material->stock }}
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="materiales[{{ $index }}][cantidad]"
                                        class="form-control form-control-sm"
                                        min="0"
                                        max="{{ $maximo }}"
                                        step="1"
                                        value="{{ old('materiales.' . $index . '.cantidad', $maximo) }}"
                                        required
                                    >

                                    @if($maximo === 0)
                                        <small class="text-danger">
                                            Sin stock disponible
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea
                    name="observaciones"
                    class="form-control"
                    rows="2"
                >{{ old('observaciones') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">¿Falta algo por comprar?</label>
                <textarea
                    name="falta_comprar"
                    class="form-control"
                    rows="2"
                >{{ old('falta_comprar') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus"></i>
                Registrar vale complementario
            </button>

            <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </form>

    @endif

</div>

@endsection
