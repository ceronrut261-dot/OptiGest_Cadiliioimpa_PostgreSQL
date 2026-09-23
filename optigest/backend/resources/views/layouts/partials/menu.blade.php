@php($ruta = request()->route()->getName() ?? '')
<div class="list-group list-group-flush py-2">
    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ $ruta === 'dashboard' ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard / KPIs
    </a>

    <a href="{{ route('tickets.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'tickets.') ? 'active' : '' }}">
        <i class="bi bi-tools me-2"></i> Tickets / Servicios
    </a>

    <a href="{{ route('clientes.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'clientes.') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i> Clientes
    </a>

    @hasanyrole('administrador|tecnico|cotizador')
        <a href="{{ route('inventario.movimientos.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'inventario.movimientos') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right me-2"></i> Movimientos Inventario
        </a>
    @endhasanyrole

    @hasanyrole('administrador|cotizador')
        <a href="{{ route('inventario.materiales.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'inventario.materiales') ? 'active' : '' }}">
            <i class="bi bi-box-seam me-2"></i> Materiales
        </a>

        <a href="{{ route('proveedores.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'proveedores.') ? 'active' : '' }}">
            <i class="bi bi-truck me-2"></i> Proveedores
        </a>

        <a href="{{ route('cotizaciones.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'cotizaciones.') ? 'active' : '' }}">
            <i class="bi bi-receipt me-2"></i> Cotizaciones
        </a>

        <a href="{{ route('salidas.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'salidas.') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-up me-2"></i> Salidas de Materiales
        </a>
    @endhasanyrole

    @role('administrador')
        <a href="{{ route('inventario.reportes.stock') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'inventario.reportes') ? 'active' : '' }}">
            <i class="bi bi-graph-up me-2"></i> Reportes de Inventario
        </a>
    @endrole

    <a href="{{ route('asistente.index') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'asistente.') ? 'active' : '' }}">
        <i class="bi bi-robot me-2"></i> Asistente IA
    </a>

    @role('administrador')
        <a href="{{ route('usuarios.index') }}" class="list-group-item list-group-item-action {{ $ruta === 'usuarios.index' ? 'active' : '' }}">
            <i class="bi bi-people-fill me-2"></i> Usuarios
        </a>

        <a href="{{ route('usuarios.create') }}" class="list-group-item list-group-item-action {{ str_starts_with($ruta, 'usuarios.') ? 'active' : '' }}">
            <i class="bi bi-person-plus me-2"></i> Crear Usuario
        </a>
    @endrole
</div>

