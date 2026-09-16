@extends('layouts.app')

@section('titulo', 'Importar Inventario')

@section('contenido')
<h2 class="h4 mb-4">Importar Inventario desde CSV</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
        <ul class="mb-0">
            @foreach (session('errores_importacion', []) as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<p class="text-muted small">
    Columnas esperadas (primera fila del archivo):
    <code>codigo,nombre,categoria,descripcion,precio,stock,stock_minimo,unidad_medida,proveedor</code><br>
    Solo <strong>codigo</strong> y <strong>nombre</strong> son obligatorios.
    Si tienes un archivo .xlsx, ábrelo en Excel/Google Sheets y usa
    "Guardar como" → CSV antes de subirlo aquí.
</p>

<form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label for="archivo" class="form-label">Selecciona archivo CSV</label>
        <input type="file" name="archivo" id="archivo" class="form-control" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload"></i> Cargar Inventario
    </button>
</form>
@endsection
