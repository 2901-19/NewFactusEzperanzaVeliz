@extends('layouts.app')
@section('titulo', 'Exportar / Importar Datos')
@section('contenido')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Exportar Datos</div>
            <div class="card-body">
                <p class="text-muted">Selecciona los datos a exportar y descarga el archivo JSON.</p>
                <form method="GET" action="{{ route('herramientas.exportar') }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo de dato:</label>
                        <select class="form-select" name="tipos[]">
                            @foreach ($tiposDisponibles as $key => $label)
                            <option value="{{ $key }}" @selected($key === 'precios')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download"></i> Exportar JSON
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Importar Datos</div>
            <div class="card-body">
                <p class="text-muted">Sube un archivo JSON y selecciona qué datos importar.</p>
                <form method="POST" action="{{ route('herramientas.importar') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo de dato:</label>
                        <select class="form-select" name="tipos[]">
                            @foreach ($tiposDisponibles as $key => $label)
                            <option value="{{ $key }}" @selected($key === 'precios')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="archivo" class="form-control" accept=".json" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-upload"></i> Importar JSON
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection