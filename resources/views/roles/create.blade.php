@extends('layouts.app')
@section('titulo', 'Nuevo Rol')
@section('contenido')
<div class="card">
    <div class="card-header">Nuevo Rol</div>
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required placeholder="Ej. Cajero, Supervisor...">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion') }}">
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Permisos del rol</label>
                <div class="text-muted small mb-2">Marca los permisos que tendrá este rol. Podrás quitarlos o agregarlos luego desde la edición.</div>
                <div class="row">
                    @foreach ($permisos as $permiso)
                    <div class="col-md-4 mb-1">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                class="form-check-input" id="permiso-{{ $permiso->id }}"
                                {{ old('permisos') && in_array($permiso->id, old('permisos')) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="permiso-{{ $permiso->id }}">
                                {{ $permiso->nombre }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('permisos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
