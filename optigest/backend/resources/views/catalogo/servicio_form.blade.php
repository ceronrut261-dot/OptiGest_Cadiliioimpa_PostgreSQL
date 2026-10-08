@extends('layouts.app')

@section('titulo', isset($servicio) ? 'Editar Servicio' : 'Nuevo Servicio')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">{{ isset($servicio) ? 'Editar Servicio' : 'Nuevo Servicio' }}</h2>
            <a href="{{ route('catalogo.index', ['tab' => 'servicios']) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver a Servicios
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ isset($servicio) ? route('catalogo.servicios.update', $servicio) : route('catalogo.servicios.store') }}" method="POST">
                    @csrf
                    @if(isset($servicio))
                        @method('PUT')
                    @endif

                    @if(isset($servicio))
                        <div class="mb-3">
                            <label class="form-label text-muted small">Código del servicio</label>
                            <input type="text" class="form-control bg-light" value="{{ $servicio->codigo }}" readonly>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del servicio <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" 
                               value="{{ old('nombre', $servicio->nombre ?? '') }}" 
                               placeholder="Ej. Instalación de tubería PVC" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" name="categoria" id="categoria" class="form-control" 
                               value="{{ old('categoria', $servicio->categoria ?? '') }}" 
                               placeholder="Ej. Plomería, Electricidad, Albañilería">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" 
                                  placeholder="Detalles sobre el trabajo o condiciones...">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="precio_estandar" class="form-label">Precio base (Q) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="precio_estandar" id="precio_estandar" class="form-control" 
                                   value="{{ old('precio_estandar', $servicio->precio_estandar ?? '') }}" 
                                   placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unidad" class="form-label">Unidad de medida <span class="text-danger">*</span></label>
                            <input type="text" name="unidad" id="unidad" class="form-control" 
                                   value="{{ old('unidad', $servicio->unidad ?? '') }}" 
                                   placeholder="Ej. mt, hr, día, pza" required>
                        </div>
                    </div>

                    @if(isset($servicio))
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" 
                                   {{ old('activo', $servicio->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Servicio activo en catálogo</label>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('catalogo.index', ['tab' => 'servicios']) }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            {{ isset($servicio) ? 'Actualizar Servicio' : 'Guardar Servicio' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection