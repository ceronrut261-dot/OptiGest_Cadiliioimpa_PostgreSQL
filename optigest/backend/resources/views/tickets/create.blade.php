@extends('layouts.app')
@section('titulo', isset($ticket) ? 'Editar ticket' : 'Nuevo ticket')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($ticket) ? 'Editar ticket' : 'Nuevo ticket' }}</h2>
<form method="POST" action="{{ isset($ticket) ? route('tickets.update', $ticket) : route('tickets.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    @csrf
    @if(isset($ticket)) @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label small">Cliente</label>
        <div class="d-flex gap-2">
            <select name="cliente_id" class="form-select" required>
                <option value="">— Selecciona un cliente —</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ old('cliente_id', $ticket->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nombre }}{{ $cliente->telefono ? ' — '.$cliente->telefono : '' }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('clientes.create', ['origen' => 'ticket']) }}" target="_blank" class="btn btn-outline-secondary text-nowrap">Cliente nuevo</a>
        </div>
        <div class="form-text">¿No aparece? Créalo en la pestaña nueva y refresca esta lista.</div>
    </div>
    <div class="mb-3"><label class="form-label small">Descripción</label><textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $ticket->descripcion ?? '') }}</textarea></div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Prioridad</label>
            <select name="prioridad" class="form-select">
                @foreach(['baja','media','alta','urgente'] as $p)
                    <option value="{{ $p }}" {{ old('prioridad', $ticket->prioridad ?? 'media') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Técnico asignado</label>
            <select name="tecnico_id" class="form-select">
                <option value="">— Sin asignar —</option>
                @foreach($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $ticket->tecnico_id ?? '') == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @php
        $puedeVerMontos = auth()->user()->hasRole(['administrador', 'cotizador']);
        $miembros = collect(old('equipo', isset($ticket)
            ? $ticket->tecnicos->where('pivot.rol', 'apoyo')->map(fn ($t) => ['user_id' => $t->id, 'monto_trato' => $t->pivot->monto_trato])->values()->all()
            : []));
        $montoPrincipal = old('monto_trato_principal', isset($ticket) && $ticket->tecnico_id
            ? data_get($ticket->tecnicos->firstWhere('id', $ticket->tecnico_id), 'pivot.monto_trato', 0)
            : 0);
    @endphp

    @if($puedeVerMontos)
        <div class="mb-3">
            <label class="form-label small">Pago pactado por trato al técnico responsable (Q)</label>
            <input type="number" step="0.01" min="0" name="monto_trato_principal" class="form-control" style="max-width:220px;" value="{{ $montoPrincipal }}">
        </div>

        <div class="mb-3">
            <label class="form-label small">Técnicos de apoyo (si el trabajo requiere 3 o más personas)</label>
            <div id="equipo-lista">
                @foreach($miembros as $i => $m)
                    <div class="row g-2 mb-2 fila-equipo">
                        <div class="col-7">
                            <select name="equipo[{{ $i }}][user_id]" class="form-select form-select-sm">
                                <option value="">— Técnico —</option>
                                @foreach($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ (string) ($m['user_id'] ?? '') === (string) $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="number" step="0.01" min="0" name="equipo[{{ $i }}][monto_trato]" class="form-control form-control-sm" placeholder="Pago por trato (Q)" value="{{ $m['monto_trato'] ?? 0 }}">
                        </div>
                        <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger js-quitar-miembro">×</button></div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="agregar-miembro">+ Agregar técnico de apoyo</button>
        </div>

        <template id="plantilla-miembro">
            <div class="row g-2 mb-2 fila-equipo">
                <div class="col-7">
                    <select name="equipo[__I__][user_id]" class="form-select form-select-sm">
                        <option value="">— Técnico —</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4"><input type="number" step="0.01" min="0" name="equipo[__I__][monto_trato]" class="form-control form-control-sm" placeholder="Pago por trato (Q)" value="0"></div>
                <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger js-quitar-miembro">×</button></div>
            </div>
        </template>
    @endif

    <div class="mb-3"><label class="form-label small">Fecha programada</label><input type="datetime-local" name="fecha_programada" class="form-control" value="{{ old('fecha_programada', isset($ticket->fecha_programada) ? $ticket->fecha_programada->format('Y-m-d\TH:i') : '') }}"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection

@section('scripts')
<script>
(function () {
    const lista = document.getElementById('equipo-lista');
    const boton = document.getElementById('agregar-miembro');
    if (!lista || !boton) return;
    let indice = lista.querySelectorAll('.fila-equipo').length + 100;
    boton.addEventListener('click', () => {
        const html = document.getElementById('plantilla-miembro').innerHTML.replaceAll('__I__', indice++);
        lista.insertAdjacentHTML('beforeend', html);
    });
    lista.addEventListener('click', e => {
        if (e.target.classList.contains('js-quitar-miembro')) e.target.closest('.fila-equipo').remove();
    });
})();
</script>
@endsection
