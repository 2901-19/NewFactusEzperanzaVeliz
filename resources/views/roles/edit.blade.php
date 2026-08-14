@extends('layouts.app')
@section('titulo', 'Editar Rol')
@section('contenido')
<div class="card">
    <div class="card-header">Editar Rol: {{ $rol->nombre }}</div>
    <div class="card-body">
        <form action="{{ route('roles.update', $rol->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $rol->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $rol->descripcion) }}">
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Permisos del rol</label>
                <div class="text-muted small mb-2">Marca o desmarca los permisos de este rol. Los cambios se aplican de inmediato a los usuarios que lo tengan.</div>
                <div class="row">
                    @foreach ($permisos as $permiso)
                    <div class="col-md-4 mb-1">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                class="form-check-input" id="permiso-{{ $permiso->id }}"
                                {{ $rol->permisos->contains($permiso->id) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="permiso-{{ $permiso->id }}">
                                {{ $permiso->nombre }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('permisos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
