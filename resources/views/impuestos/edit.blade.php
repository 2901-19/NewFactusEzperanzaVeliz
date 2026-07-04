@extends('layouts.app')
@section('titulo', 'Editar Impuesto')
@section('contenido')
<div class="card">
    <div class="card-header">Editar Impuesto</div>
    <div class="card-body">
        <form action="{{ route('impuestos.update', $impuesto->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $impuesto->nombre) }}" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Porcentaje *</label>
                <input type="number" step="0.01" name="porcentaje" class="form-control @error('porcentaje') is-invalid @enderror" value="{{ old('porcentaje', $impuesto->porcentaje) }}" min="0" max="100" required>
                @error('porcentaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $impuesto->fecha) }}" required>
                @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('impuestos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
