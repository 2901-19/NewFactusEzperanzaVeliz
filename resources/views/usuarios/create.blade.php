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
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection