@extends('layouts.app')
@section('titulo', 'Nuevo Producto')
@section('contenido')
<div class="card">
    <div class="card-header">Nuevo Producto</div>
    <div class="card-body">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" accept="image/*">
                    @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="2">{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unidades por Paquete *</label>
                    <input type="number" name="unidades_por_paquete" class="form-control @error('unidades_por_paquete') is-invalid @enderror" value="{{ old('unidades_por_paquete', 1) }}" min="1">
                    @error('unidades_por_paquete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stock en Paquetes *</label>
                    <input type="number" name="stock_paquetes" class="form-control @error('stock_paquetes') is-invalid @enderror" value="{{ old('stock_paquetes', 0) }}" min="0">
                    @error('stock_paquetes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stock en Unidades Sueltas *</label>
                    <input type="number" name="stock_unidades" class="form-control @error('stock_unidades') is-invalid @enderror" value="{{ old('stock_unidades', 0) }}" min="0">
                    @error('stock_unidades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Unitario (USD) *</label>
                    <input type="number" step="0.01" name="precio_unitario_usd" class="form-control @error('precio_unitario_usd') is-invalid @enderror" value="{{ old('precio_unitario_usd') }}" min="0">
                    @error('precio_unitario_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Mayorista (USD) *</label>
                    <input type="number" step="0.01" name="precio_mayor_usd" class="form-control @error('precio_mayor_usd') is-invalid @enderror" value="{{ old('precio_mayor_usd') }}" min="0">
                    @error('precio_mayor_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cantidad Mínima para Mayor *</label>
                    <input type="number" name="cantidad_minima_mayor" class="form-control @error('cantidad_minima_mayor') is-invalid @enderror" value="{{ old('cantidad_minima_mayor', 0) }}" min="0">
                    @error('cantidad_minima_mayor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tiene IVA</label>
                    <select name="tiene_iva" class="form-select">
                        <option value="1" {{ old('tiene_iva', '1') == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ old('tiene_iva', '1') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fuente de Tasa</label>
                    <select name="fuente_tasa" class="form-select">
                        <option value="promedio" {{ old('fuente_tasa') == 'promedio' ? 'selected' : '' }}>Promedio</option>
                        <option value="dolar" {{ old('fuente_tasa') == 'dolar' ? 'selected' : '' }}>Dólar Paralelo</option>
                        <option value="bcv" {{ old('fuente_tasa') == 'bcv' ? 'selected' : '' }}>BCV</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="disponible" {{ old('estado', 'disponible') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="no_disponible" {{ old('estado', 'disponible') == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
