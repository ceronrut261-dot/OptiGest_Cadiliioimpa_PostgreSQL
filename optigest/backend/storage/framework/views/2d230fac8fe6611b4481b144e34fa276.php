<?php $__env->startSection('titulo', 'Catálogo'); ?>
<?php $__env->startSection('contenido'); ?>
<?php $verPrecios = auth()->user()->hasRole(['administrador', 'cotizador']); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Catálogo</h2>
    <?php if($puedeGestionar && $vista === 'servicios'): ?>
        <a href="<?php echo e(route('catalogo.servicios.create')); ?>" class="btn btn-primary btn-sm">+ Nuevo servicio</a>
    <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?php echo e($vista === 'materiales' ? 'active' : ''); ?>" href="<?php echo e(route('catalogo.index', ['vista' => 'materiales'])); ?>">Materiales</a></li>
    <li class="nav-item"><a class="nav-link <?php echo e($vista === 'servicios' ? 'active' : ''); ?>" href="<?php echo e(route('catalogo.index', ['vista' => 'servicios'])); ?>">Servicios y mano de obra</a></li>
</ul>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="vista" value="<?php echo e($vista); ?>">
    <div class="col-md-5"><input type="search" name="q" value="<?php echo e($buscar); ?>" class="form-control form-control-sm" placeholder="Buscar por nombre, código o descripción"></div>
    <?php if($vista === 'materiales'): ?>
        <div class="col-md-4">
            <select name="categoria" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat); ?>" <?php echo e($categoria === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Buscar</button></div>
</form>

<?php if($vista === 'materiales'): ?>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $agotado = $m->stock <= 0;
                $bajo = ! $agotado && $m->bajo_stock;
            ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="small text-muted"><?php echo e($m->codigo); ?><?php if($m->categoria): ?> · <?php echo e($m->categoria); ?><?php endif; ?></div>
                        <h3 class="h6 mt-1"><?php echo e($m->nombre); ?></h3>
                        <?php if($m->descripcion): ?><p class="small text-muted mb-2"><?php echo e(\Illuminate\Support\Str::limit($m->descripcion, 70)); ?></p><?php endif; ?>
                        <div class="d-flex justify-content-between align-items-end">
                            <?php if($verPrecios): ?><span class="fw-semibold">Q<?php echo e(number_format($m->precio, 2)); ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="small text-muted"><?php echo e($m->unidad_medida); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <?php if($agotado): ?>
                            <span class="badge bg-danger">Agotado</span>
                        <?php elseif($bajo): ?>
                            <span class="badge bg-warning text-dark">Stock bajo · <?php echo e($m->stock); ?></span>
                        <?php else: ?>
                            <span class="badge bg-success">Disponible · <?php echo e($m->stock); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12 text-muted">No se encontraron materiales.</div>
        <?php endif; ?>
    </div>
    <div class="mt-3"><?php echo e($materiales->links()); ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm bg-white align-middle">
            <thead class="table-light">
                <tr><th>Código</th><th>Servicio</th><th>Categoría</th><th>Unidad</th><th class="text-end">Precio estándar</th><?php if($puedeGestionar): ?><th></th><?php endif; ?></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($s->activo ? '' : 'text-muted'); ?>">
                    <td><?php echo e($s->codigo); ?></td>
                    <td><?php echo e($s->nombre); ?><?php if (! ($s->activo)): ?> <span class="badge bg-secondary">Inactivo</span><?php endif; ?>
                        <?php if($s->descripcion): ?><div class="small text-muted"><?php echo e($s->descripcion); ?></div><?php endif; ?></td>
                    <td><?php echo e($s->categoria ?? '—'); ?></td>
                    <td><?php echo e($s->unidad); ?></td>
                    <td class="text-end">Q<?php echo e(number_format($s->precio_estandar, 2)); ?></td>
                    <?php if($puedeGestionar): ?>
                        <td class="text-end text-nowrap">
                            <a href="<?php echo e(route('catalogo.servicios.edit', $s)); ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <?php if($s->activo): ?>
                                <form action="<?php echo e(route('catalogo.servicios.destroy', $s)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar este servicio?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-outline-danger btn-sm">Desactivar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-muted text-center py-3">Todavía no hay servicios en el catálogo.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="small text-muted">Estos precios de mano de obra se usan tal cual en las cotizaciones, para que no varíen de un trabajo a otro.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ceron\Downloads\OptiGest_Cadiliompa_PostgreSQL\optigest\backend\resources\views/catalogo/index.blade.php ENDPATH**/ ?>