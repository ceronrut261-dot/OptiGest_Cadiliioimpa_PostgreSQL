@extends('layouts.app')

@section('titulo', 'Auditoría del sistema')

@section('contenido')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="h4 mb-1">Auditoría del sistema</h2>
        <p class="text-muted small mb-0">
            Historial de acciones realizadas en OptiGest.
        </p>
    </div>
</div>

<form method="GET" action="{{ route('auditoria.index') }}" class="row g-2 mb-3">

    <div class="col-md-3">
        <label for="accion" class="form-label small">Acción</label>
        <select name="accion" id="accion" class="form-select form-select-sm">
            <option value="">Todas las acciones</option>
            <option value="crear" {{ request('accion') === 'crear' ? 'selected' : '' }}>Crear</option>
            <option value="actualizar" {{ request('accion') === 'actualizar' ? 'selected' : '' }}>Actualizar</option>
            <option value="activar" {{ request('accion') === 'activar' ? 'selected' : '' }}>Activar</option>
            <option value="desactivar" {{ request('accion') === 'desactivar' ? 'selected' : '' }}>Desactivar</option>
            <option value="eliminar" {{ request('accion') === 'eliminar' ? 'selected' : '' }}>Eliminar</option>
        </select>
    </div>

    <div class="col-md-3">
        <label for="modulo" class="form-label small">Módulo</label>
        <select name="modulo" id="modulo" class="form-select form-select-sm">
            <option value="">Todos los módulos</option>

            @foreach($registros->getCollection()->pluck('modulo')->filter()->unique()->sort() as $modulo)
                <option value="{{ $modulo }}" {{ request('modulo') === $modulo ? 'selected' : '' }}>
                    {{ $modulo }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="buscar" class="form-label small">Buscar</label>
        <input
            type="text"
            name="buscar"
            id="buscar"
            value="{{ request('buscar') }}"
            class="form-control form-control-sm"
            placeholder="Descripción, registro o IP"
        >
    </div>

    <div class="col-md-3">
        <label for="desde" class="form-label small">Desde</label>
        <input
            type="date"
            name="desde"
            id="desde"
            value="{{ request('desde') }}"
            class="form-control form-control-sm"
        >
    </div>

    <div class="col-md-3">
        <label for="hasta" class="form-label small">Hasta</label>
        <input
            type="date"
            name="hasta"
            id="hasta"
            value="{{ request('hasta') }}"
            class="form-control form-control-sm"
        >
    </div>

    <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search"></i> Filtrar
        </button>

        <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary btn-sm">
            Limpiar
        </a>
    </div>

</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="h6 mb-0">Registro de actividades</h3>

            <span class="badge bg-secondary">
                {{ $registros->total() }} registros
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover bg-white align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Módulo</th>
                        <th>Registro</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th>Detalles</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($registros as $registro)

                        @php
                            $colorAccion = match($registro->accion) {
                                'crear' => 'success',
                                'actualizar' => 'primary',
                                'activar' => 'success',
                                'desactivar' => 'warning',
                                'eliminar' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <tr>
                            <td class="text-nowrap">
                                {{ $registro->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                            </td>

                            <td>
                                {{ $registro->usuario?->name ?? 'Usuario no disponible' }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $colorAccion }}">
                                    {{ ucfirst($registro->accion) }}
                                </span>
                            </td>

                            <td>{{ $registro->modulo }}</td>

                            <td class="text-nowrap">
                                {{ $registro->registro_tipo ?? '—' }}

                                @if($registro->registro_id !== null)
                                    <span class="text-muted">
                                        #{{ $registro->registro_id }}
                                    </span>
                                @endif
                            </td>

                            <td>{{ $registro->descripcion ?? '—' }}</td>

                            <td>{{ $registro->ip_address ?? '—' }}</td>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-info"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#detalleAuditoria{{ $registro->id }}"
                                    aria-expanded="false"
                                    aria-controls="detalleAuditoria{{ $registro->id }}"
                                >
                                    Ver
                                </button>
                            </td>
                        </tr>

                        {{-- Detalles desplegables --}}
                        <tr class="collapse" id="detalleAuditoria{{ $registro->id }}">
                            <td colspan="8" class="bg-light">

                                @php
                                    $anteriores = $registro->valores_anteriores ?? [];
                                    $nuevos = $registro->valores_nuevos ?? [];

                                    $campos = array_unique(array_merge(
                                        array_keys($anteriores),
                                        array_keys($nuevos)
                                    ));

                                    $camposExcluidos = [
                                        'created_at',
                                        'updated_at',
                                        'deleted_at',
                                    ];

                                    $campos = array_diff($campos, $camposExcluidos);

                                    $formatear = function ($valor) {
                                        if (is_array($valor)) {
                                            return json_encode(
                                                $valor,
                                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                            );
                                        }

                                        if (is_bool($valor)) {
                                            return $valor ? 'Sí' : 'No';
                                        }

                                        return $valor ?? '—';
                                    };
                                @endphp

                                <div class="p-3">

                                    <h4 class="h6 mb-3">
                                        <i class="bi bi-info-circle"></i>
                                        Detalles de la operación
                                    </h4>

                                    @if(in_array($registro->accion, ['actualizar', 'activar', 'desactivar']))

                                        @if(count($campos) > 0)

                                            <div class="table-responsive mb-3">
                                                <table class="table table-sm table-bordered bg-white">

                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Campo</th>
                                                            <th>Valor anterior</th>
                                                            <th>Valor nuevo</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach($campos as $campo)

                                                            <tr>
                                                                <td>
                                                                    <strong>
                                                                        {{ ucfirst(str_replace('_', ' ', $campo)) }}
                                                                    </strong>
                                                                </td>

                                                                <td>
                                                                    {{ $formatear($anteriores[$campo] ?? '—') }}
                                                                </td>

                                                                <td>
                                                                    {{ $formatear($nuevos[$campo] ?? '—') }}
                                                                </td>
                                                            </tr>

                                                        @endforeach
                                                    </tbody>

                                                </table>
                                            </div>

                                        @else

                                            <p class="text-muted small">
                                                No hay cambios detallados disponibles.
                                            </p>

                                        @endif

                                    @else

                                        <div class="row g-3 mb-3">

                                            <div class="col-md-6">
                                                <h5 class="small fw-bold">
                                                    Valores anteriores
                                                </h5>

                                                <pre class="small text-break text-wrap bg-white p-2 border rounded">{{ json_encode($anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Sin datos anteriores' }}</pre>
                                            </div>

                                            <div class="col-md-6">
                                                <h5 class="small fw-bold">
                                                    Valores nuevos
                                                </h5>

                                                <pre class="small text-break text-wrap bg-white p-2 border rounded">{{ json_encode($nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Sin datos nuevos' }}</pre>
                                            </div>

                                        </div>

                                    @endif

                                    <div>
                                        <strong class="small">Navegador:</strong>

                                        <span class="small text-break">
                                            {{ $registro->user_agent ?? '—' }}
                                        </span>
                                    </div>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No hay registros de auditoría que coincidan con la búsqueda.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{ $registros->links() }}

    </div>
</div>

@endsection
