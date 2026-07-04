@extends('layouts.app')
@section('titulo', isset($categoria) ? 'Editar Categoría' : 'Nueva Categoría')
@section('contenido')
<div class="card">
    <div class="card-header">{{ isset($categoria) ? 'Editar' : 'Nueva' }} Categoría</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($categoria) ? route('categorias.update', $categoria->id) : route('categorias.store') }}">
            @csrf
            @isset($categoria) @method('PUT') @endisset

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre', $categoria->nombre ?? '') }}" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($categoria) ? 'Actualizar' : 'Guardar' }}</button>
            <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
