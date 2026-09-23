<?php ($ruta = request()->route()->getName() ?? ''); ?>
<div class="list-group list-group-flush py-2">
    <a href="<?php echo e(route('dashboard')); ?>" class="list-group-item list-group-item-action <?php echo e($ruta === 'dashboard' ? 'active' : ''); ?>">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard / KPIs
    </a>

    <a href="<?php echo e(route('tickets.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'tickets.') ? 'active' : ''); ?>">
        <i class="bi bi-tools me-2"></i> Tickets / Servicios
    </a>

    <a href="<?php echo e(route('clientes.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'clientes.') ? 'active' : ''); ?>">
        <i class="bi bi-people me-2"></i> Clientes
    </a>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'administrador|tecnico|cotizador')): ?>
        <a href="<?php echo e(route('inventario.movimientos.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'inventario.movimientos') ? 'active' : ''); ?>">
            <i class="bi bi-arrow-left-right me-2"></i> Movimientos Inventario
        </a>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'administrador|cotizador')): ?>
        <a href="<?php echo e(route('inventario.materiales.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'inventario.materiales') ? 'active' : ''); ?>">
            <i class="bi bi-box-seam me-2"></i> Materiales
        </a>

        <a href="<?php echo e(route('proveedores.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'proveedores.') ? 'active' : ''); ?>">
            <i class="bi bi-truck me-2"></i> Proveedores
        </a>

        <a href="<?php echo e(route('cotizaciones.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'cotizaciones.') ? 'active' : ''); ?>">
            <i class="bi bi-receipt me-2"></i> Cotizaciones
        </a>

        <a href="<?php echo e(route('salidas.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'salidas.') ? 'active' : ''); ?>">
            <i class="bi bi-box-arrow-up me-2"></i> Salidas de Materiales
        </a>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'administrador')): ?>
        <a href="<?php echo e(route('inventario.reportes.stock')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'inventario.reportes') ? 'active' : ''); ?>">
            <i class="bi bi-graph-up me-2"></i> Reportes de Inventario
        </a>
    <?php endif; ?>

    <a href="<?php echo e(route('asistente.index')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'asistente.') ? 'active' : ''); ?>">
        <i class="bi bi-robot me-2"></i> Asistente IA
    </a>

    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'administrador')): ?>
        <a href="<?php echo e(route('usuarios.index')); ?>" class="list-group-item list-group-item-action <?php echo e($ruta === 'usuarios.index' ? 'active' : ''); ?>">
            <i class="bi bi-people-fill me-2"></i> Usuarios
        </a>

        <a href="<?php echo e(route('usuarios.create')); ?>" class="list-group-item list-group-item-action <?php echo e(str_starts_with($ruta, 'usuarios.') ? 'active' : ''); ?>">
            <i class="bi bi-person-plus me-2"></i> Crear Usuario
        </a>
    <?php endif; ?>
</div>

<?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/layouts/partials/menu.blade.php ENDPATH**/ ?>