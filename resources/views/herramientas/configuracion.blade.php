@extends('layouts.app')
@section('titulo', 'Configuración del Negocio')
@section('contenido')
<div class="card">
    <div class="card-header">Datos del Negocio</div>
    <div class="card-body">
        <form method="POST" action="{{ route('herramientas.configuracion.guardar') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Negocio *</label>
                    <input type="text" name="nombre_negocio" class="form-control @error('nombre_negocio') is-invalid @enderror"
                        value="{{ old('nombre_negocio', $configs['nombre_negocio'] ?? '') }}" required>
                    @error('nombre_negocio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">RIF *</label>
                    <input type="text" name="rif" class="form-control @error('rif') is-invalid @enderror"
                        value="{{ old('rif', $configs['rif'] ?? '') }}" required>
                    @error('rif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dirección *</label>
                    <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror"
                        value="{{ old('direccion', $configs['direccion'] ?? '') }}" required>
                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                        value="{{ old('telefono', $configs['telefono'] ?? '') }}" required>
                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
</div>
@endsection
