@extends('layouts.app')
@section('titulo', 'Editar Producto')
@section('contenido')
<div class="card">
    <div class="card-header">Editar Producto: {{ $producto->nombre }}</div>
    <div class="card-body">
        <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                        <option value="">Sin categoría</option>
                        @foreach (\App\Models\Categoria::orderBy('nombre')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" accept="image/*">
                    @if ($producto->imagen) <small class="text-muted d-block">Imagen actual: {{ $producto->imagen }}</small> @endif
                    @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unidades por Paquete *</label>
                    <input type="number" name="unidades_por_paquete" class="form-control @error('unidades_por_paquete') is-invalid @enderror" value="{{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}" min="1">
                    @error('unidades_por_paquete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stock en Paquetes *</label>
                    <input type="number" name="stock_paquetes" class="form-control @error('stock_paquetes') is-invalid @enderror" value="{{ old('stock_paquetes', $producto->stock_paquetes) }}" min="0">
                    @error('stock_paquetes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stock en Unidades Sueltas *</label>
                    <input type="number" name="stock_unidades" class="form-control @error('stock_unidades') is-invalid @enderror" value="{{ old('stock_unidades', $producto->stock_unidades) }}" min="0">
                    @error('stock_unidades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Unitario (USD) *</label>
                    <input type="number" step="0.01" name="precio_unitario_usd" class="form-control @error('precio_unitario_usd') is-invalid @enderror" value="{{ old('precio_unitario_usd', $producto->precio_unitario_usd) }}" min="0">
                    @error('precio_unitario_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Mayorista (USD) *</label>
                    <input type="number" step="0.01" name="precio_mayor_usd" class="form-control @error('precio_mayor_usd') is-invalid @enderror" value="{{ old('precio_mayor_usd', $producto->precio_mayor_usd) }}" min="0">
                    @error('precio_mayor_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cantidad Mínima para Mayor *</label>
                    <input type="number" name="cantidad_minima_mayor" class="form-control @error('cantidad_minima_mayor') is-invalid @enderror" value="{{ old('cantidad_minima_mayor', $producto->cantidad_minima_mayor) }}" min="0">
                    @error('cantidad_minima_mayor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tiene IVA</label>
                    <select name="tiene_iva" class="form-select">
                        <option value="1" {{ old('tiene_iva', $producto->tiene_iva) == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ old('tiene_iva', $producto->tiene_iva) == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fuente de Tasa</label>
                    <select name="fuente_tasa" class="form-select">
                        <option value="promedio" {{ old('fuente_tasa', $producto->fuente_tasa) == 'promedio' ? 'selected' : '' }}>Promedio</option>
                        <option value="dolar" {{ old('fuente_tasa', $producto->fuente_tasa) == 'dolar' ? 'selected' : '' }}>Dólar Paralelo</option>
                        <option value="bcv" {{ old('fuente_tasa', $producto->fuente_tasa) == 'bcv' ? 'selected' : '' }}>BCV</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="disponible" {{ old('estado', $producto->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="no_disponible" {{ old('estado', $producto->estado) == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
