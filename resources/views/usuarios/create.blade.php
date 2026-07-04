@extends('layouts.app')
@section('titulo', 'Nuevo Usuario')
@section('contenido')
<div class="card">
    <div class="card-header">Nuevo Usuario</div>
    <div class="card-body">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario *</label>
                    <input type="text" name="usuario" class="form-control @error('usuario') is-invalid @enderror" value="{{ old('usuario') }}" required>
                    @error('usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-select @error('rol') is-invalid @enderror" required>
                        <option value="cajero" {{ old('rol') == 'cajero' ? 'selected' : '' }}>Cajero</option>
                        <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Permisos adicionales</label>
                <div class="text-muted small mb-2">Los permisos marcados en <span class="badge bg-success">verde</span> los otorga el rol automáticamente. Marca permisos extra para dar acceso adicional.</div>
                <div class="row">
                    @foreach ($permisos as $permiso)
                        @php $delRol = in_array($permiso->id, $permisosDelRol); @endphp
                        <div class="col-md-4 mb-1">
                            @if ($delRol)
                                <span class="badge bg-success me-1" title="Otorgado por el rol">
                                    <i class="bi bi-check-circle"></i> {{ $permiso->nombre }}
                                </span>
                            @else
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                        class="form-check-input" id="permiso-{{ $permiso->id }}"
                                        {{ old('permisos') && in_array($permiso->id, old('permisos')) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="permiso-{{ $permiso->id }}">
                                        {{ $permiso->nombre }}
                                    </label>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('permisos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
