@extends('layouts.app')
@section('titulo', 'Editar Tasa de Cambio')
@section('contenido')
<div class="card">
    <div class="card-header">Editar Tasa de Cambio</div>
    <div class="card-body">
        <form action="{{ route('tasas-cambio.update', $tasaCambio->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Tipo *</label>
                <select name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                    <option value="promedio" {{ old('tipo', $tasaCambio->tipo) == 'promedio' ? 'selected' : '' }}>Promedio</option>
                    <option value="dolar" {{ old('tipo', $tasaCambio->tipo) == 'dolar' ? 'selected' : '' }}>Dólar Paralelo</option>
                    <option value="bcv" {{ old('tipo', $tasaCambio->tipo) == 'bcv' ? 'selected' : '' }}>BCV</option>
                </select>
                @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Moneda *</label>
                <input type="text" name="moneda" class="form-control @error('moneda') is-invalid @enderror" value="{{ old('moneda', $tasaCambio->moneda) }}" required>
                @error('moneda') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Monto *</label>
                <input type="number" step="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $tasaCambio->monto) }}" min="0" required>
                @error('monto') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $tasaCambio->fecha) }}" required>
                @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('tasas-cambio.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
