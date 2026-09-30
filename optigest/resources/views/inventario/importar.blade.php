@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Importar inventario desde Excel</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
            <ul>
                @foreach (session('errores_importacion', []) as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="archivo_excel" class="form-label">Archivo Excel (.xlsx, .xls o .csv)</label>
            <input type="file" name="archivo_excel" id="archivo_excel" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Cargar inventario</button>
        <a href="{{ asset('plantillas/plantilla_materiales.csv') }}" class="btn btn-link">
            Descargar plantilla de ejemplo
        </a>
    </form>
</div>
@endsection
