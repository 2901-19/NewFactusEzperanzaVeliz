@extends('layouts.app')
@section('titulo', 'Editar Producto')
@section('contenido')
<form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-info-circle me-1"></i> Información del Producto</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                        <option value="">Sin categoría</option>
                        @foreach (\App\Models\Categoria::orderBy('nombre')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="disponible" {{ old('estado', $producto->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="no_disponible" {{ old('estado', $producto->estado) == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" accept="image/*">
                    @if ($producto->imagen) <small class="text-muted d-block">Imagen actual: {{ $producto->imagen }}</small> @endif
                    @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Precios y Cantidades</div>
        <div class="card-body">
            <div x-data="precioCalc({{ old('costo_usd', $producto->costo_usd) }}, {{ old('margen_detal', $producto->margen_detal) }}, {{ old('margen_mayor', $producto->margen_mayor) }}, {{ $tasas['promedio'] ?? 0 }}, {{ $tasas['dolar'] ?? 0 }}, {{ $tasas['bcv'] ?? 0 }}, '{{ old('fuente_tasa', $producto->fuente_tasa) }}')">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo del Producto (USD) *</label>
                        <input type="number" step="0.01" min="0" name="costo_usd" x-model.number="costoUsd" class="form-control @error('costo_usd') is-invalid @enderror" value="{{ old('costo_usd', $producto->costo_usd) }}">
                        @error('costo_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Margen Detal (%) *</label>
                        <input type="number" step="0.01" min="0" name="margen_detal" x-model.number="margenDetal" class="form-control @error('margen_detal') is-invalid @enderror" value="{{ old('margen_detal', $producto->margen_detal) }}">
                        @error('margen_detal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Margen Mayor (%) *</label>
                        <input type="number" step="0.01" min="0" name="margen_mayor" x-model.number="margenMayor" class="form-control @error('margen_mayor') is-invalid @enderror" value="{{ old('margen_mayor', $producto->margen_mayor) }}">
                        @error('margen_mayor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <span class="info-label">Costo en Bs</span>
                            <span class="info-value" x-text="'Bs ' + costoBs.toFixed(2)"></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <span class="info-label">Precio Detal (Bs)</span>
                            <span class="info-value" x-text="'Bs ' + precioDetalBs.toFixed(2) + ' ($' + precioDetalUsd.toFixed(2) + ')'"></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <span class="info-label">Precio Mayor (Bs)</span>
                            <span class="info-value" x-text="'Bs ' + precioMayorBs.toFixed(2) + ' ($' + precioMayorUsd.toFixed(2) + ')'"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Impuesto</label>
                        <select name="tiene_iva" class="form-select">
                            <option value="0" {{ old('tiene_iva', $producto->tiene_iva) == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('tiene_iva', $producto->tiene_iva) == '1' ? 'selected' : '' }}>Sí</option>
                        </select>
                        <small class="form-text text-muted">Aplica el porcentaje de IVA registrado en Impuestos.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tasa del Producto</label>
                        <select name="fuente_tasa" x-model="fuenteTasa" class="form-select">
                            <option value="promedio" {{ old('fuente_tasa', $producto->fuente_tasa) == 'promedio' ? 'selected' : '' }}>Promedio</option>
                            <option value="dolar" {{ old('fuente_tasa', $producto->fuente_tasa) == 'dolar' ? 'selected' : '' }}>Dólar Paralelo</option>
                            <option value="bcv" {{ old('fuente_tasa', $producto->fuente_tasa) == 'bcv' ? 'selected' : '' }}>BCV</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" x-data="stockCalc({{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}, {{ old('stock_paquetes', $producto->stock_paquetes) }}, {{ old('stock_unidades', $producto->stock_unidades) }})">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Unidades por Lote *</label>
                    <input type="number" name="unidades_por_paquete" x-model.number="unidadesPorLote" class="form-control @error('unidades_por_paquete') is-invalid @enderror" value="{{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}" min="1">
                    @error('unidades_por_paquete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock en Lotes *</label>
                    <input type="number" name="stock_paquetes" x-model.number="stockLotes" class="form-control @error('stock_paquetes') is-invalid @enderror" value="{{ old('stock_paquetes', $producto->stock_paquetes) }}" min="0">
                    @error('stock_paquetes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock en Unidades Sueltas *</label>
                    <input type="number" name="stock_unidades" x-model.number="stockUnidades" class="form-control @error('stock_unidades') is-invalid @enderror" value="{{ old('stock_unidades', $producto->stock_unidades) }}" min="0">
                    @error('stock_unidades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-box">
                        <span class="info-label">Total de Unidades</span>
                        <span class="info-value" x-text="stockLotes * unidadesPorLote + stockUnidades"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
@push('scripts')
<script>
function stockCalc(unidadesPorLote, stockLotes, stockUnidades) {
    return {
        unidadesPorLote: unidadesPorLote || 0,
        stockLotes: stockLotes || 0,
        stockUnidades: stockUnidades || 0,
    };
}

function precioCalc(costoUsd, margenDetal, margenMayor, tasaPromedio, tasaDolar, tasaBcv, fuenteTasa) {
    return {
        costoUsd: costoUsd || 0,
        margenDetal: margenDetal || 0,
        margenMayor: margenMayor || 0,
        tasas: { promedio: parseFloat(tasaPromedio) || 0, dolar: parseFloat(tasaDolar) || 0, bcv: parseFloat(tasaBcv) || 0 },
        fuenteTasa: fuenteTasa || 'promedio',
        get tasa() { return this.tasas[this.fuenteTasa] || 0; },
        get precioDetalUsd() { return this.costoUsd * (1 + this.margenDetal / 100); },
        get precioMayorUsd() { return this.costoUsd * (1 + this.margenMayor / 100); },
        get costoBs() { return this.costoUsd * this.tasa; },
        get precioDetalBs() { return this.precioDetalUsd * this.tasa; },
        get precioMayorBs() { return this.precioMayorUsd * this.tasa; },
    };
}
</script>
@endpush
