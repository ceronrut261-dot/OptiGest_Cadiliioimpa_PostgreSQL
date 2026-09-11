@extends('layouts.app')

@section('titulo', 'Importar Inventario')

@section('contenido')
<h2 class="h4 mb-4">Importar Inventario desde Excel</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label for="archivo" class="form-label">Selecciona archivo Excel/CSV</label>
        <input type="file" name="archivo" id="archivo" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload"></i> Cargar Inventario
    </button>
</form>
@endsection
