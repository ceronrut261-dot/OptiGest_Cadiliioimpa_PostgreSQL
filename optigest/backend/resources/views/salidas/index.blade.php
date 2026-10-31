@extends('layouts.app')

@section('titulo', 'Salidas de materiales')

@section('contenido')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Salidas de materiales</h2>

        <a href="{{ route('salidas.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-box-arrow-up"></i>
            Nueva salida
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Cerrar"
            ></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle bg-white">

                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Relación</th>
                            <th>Proyecto</th>
                            <th>Entregó</th>
                            <th>Recibió</th>
                            <th>Fecha</th>
                            <th>Estado de entrega</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($salidas as $salida)

                            <tr>
                                <td class="fw-semibold">
                                    {{ $salida->codigo }}
                                </td>

                                <td>
                                    @if($salida->salida_original_id)
                                        <span class="badge bg-info text-dark">
                                            Complementario
                                        </span>

                                        <div class="small text-muted mt-1">
                                            De {{ $salida->salidaOriginal?->codigo ?? 'Vale no disponible' }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">
                                            Original
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $salida->proyecto }}</td>

                                <td>
                                    {{ $salida->usuario?->name ?? 'No disponible' }}
                                </td>

                                <td>{{ $salida->persona_recibe }}</td>

                                <td>
                                    {{ $salida->fecha?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td>
                                    @if($salida->estado_entrega === 'confirmada')
                                        <span class="badge bg-success">
                                            Confirmada
                                        </span>

                                        @if($salida->confirmado_en)
                                            <div class="small text-muted mt-1">
                                                {{ $salida->confirmado_en->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end text-nowrap">
                                    Q{{ number_format((float) $salida->total, 2) }}
                                </td>

                                <td class="text-end text-nowrap">

                                    @if($salida->estado_entrega === 'pendiente')
                                        <a
                                            href="{{ route('salidas.confirmar', $salida) }}"
                                            class="btn btn-sm btn-success mb-1"
                                        >
                                            <i class="bi bi-check-circle"></i>
                                            Confirmar entrega
                                        </a>
                                    @endif

                                    @if(
                                        $salida->estado_entrega === 'confirmada'
                                        && !$salida->salida_original_id
                                    )
                                        <a
                                            href="{{ route('salidas.complementaria.create', $salida) }}"
                                            class="btn btn-sm btn-outline-primary mb-1"
                                        >
                                            <i class="bi bi-plus-circle"></i>
                                            Vale complementario
                                        </a>
                                    @endif

                                    <a
                                        href="{{ route('salidas.pdf', $salida) }}"
                                        class="btn btn-sm btn-outline-danger mb-1"
                                    >
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        Vale PDF
                                    </a>

                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Aún no hay salidas registradas.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{ $salidas->links() }}

        </div>
    </div>

</div>

@endsection
