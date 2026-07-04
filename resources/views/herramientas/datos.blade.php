@extends('layouts.app')
@section('titulo', 'Exportar / Importar Datos')
@section('contenido')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Exportar Datos</div>
            <div class="card-body">
                <p class="text-muted">Descarga un archivo JSON con todos los productos, clientes, impuestos y tasas de cambio.</p>
                <a href="{{ route('herramientas.exportar') }}" class="btn btn-primary">
                    <i class="bi bi-download"></i> Exportar JSON
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Importar Datos</div>
            <div class="card-body">
                <p class="text-muted">Sube un archivo JSON previamente exportado para restaurar productos, clientes, impuestos y tasas.</p>
                <form method="POST" action="{{ route('herramientas.importar') }}" enctype="multipart/form-data">
                    @csrf
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
