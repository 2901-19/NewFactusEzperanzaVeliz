@php
    $presentacionesIniciales = old('presentaciones', $producto?->presentaciones ?? [['nombre' => 'Unidad', 'factor_conversion' => 1, 'margen' => 0, 'activa' => true]]);
    $costoUsdInicial = old('costo_usd', $producto?->costo_usd ?? 0);
    $fuenteTasaInicial = old('fuente_tasa', $producto?->fuente_tasa ?? $opcionesTasa->keys()->first() ?? 'promedio');
    $controlaInventario = old('controla_inventario', $producto?->controla_inventario ?? true) ? 'true' : 'false';
    $unidadMedidaInicial = old('unidad_medida', $producto?->unidad_medida ?? 'unidad');
    $stockInicial = old('stock_actual', $producto?->stock_actual ?? 0);
@endphp
<script>
    window.__productoForm = {
        mapaTasas: @json($mapaTasas),
        presentaciones: @json($presentacionesIniciales),
        fuenteTasa: @json($fuenteTasaInicial),
        unidadMedida: @json($unidadMedidaInicial),
    };
</script>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-info-circle me-1"></i> Información del Producto</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto?->nombre) }}" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                    <option value="">Sin categoría</option>
                    @foreach (\App\Models\Categoria::orderBy('nombre')->get() as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id', $producto?->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="disponible" {{ old('estado', $producto?->estado ?? 'disponible') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="no_disponible" {{ old('estado', $producto?->estado ?? 'disponible') == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Imagen</label>
                <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" accept="image/*">
                @if ($producto?->imagen) <small class="text-muted d-block">Imagen actual: {{ $producto->imagen }}</small> @endif
                @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="2">{{ old('descripcion', $producto?->descripcion) }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Costo e Inventario</div>
    <div class="card-body">
        <div class="row" x-data="costoCalc({{ $costoUsdInicial }}, window.__productoForm.mapaTasas, window.__productoForm.fuenteTasa)">
            <div class="col-md-4 mb-3">
                <label class="form-label">Costo unitario base (USD) *</label>
                <input type="number" step="0.01" min="0" name="costo_usd" x-model.number="costoUsd" class="form-control @error('costo_usd') is-invalid @enderror" value="{{ old('costo_usd', $producto?->costo_usd) }}">
                @error('costo_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-text text-muted">El costo de la unidad base, compartido por todas las presentaciones.</small>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-box">
                    <span class="info-label">Costo en Bs</span>
                    <span class="info-value" x-text="'Bs ' + costoBs.toFixed(2)"></span>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tasa del Producto</label>
                <select name="fuente_tasa" x-model="fuenteTasa" class="form-select">
                    @forelse ($opcionesTasa as $tipo => $nombre)
                        <option value="{{ $tipo }}" {{ old('fuente_tasa', $producto?->fuente_tasa ?? $opcionesTasa->keys()->first() ?? '') == $tipo ? 'selected' : '' }}>{{ $nombre }}</option>
                    @empty
                        <option value="" disabled>No hay tasas activas. Créalas en Tasas de Cambio.</option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="row" x-data="inventarioControl(
            {{ $controlaInventario }},
            window.__productoForm.unidadMedida,
            {{ $stockInicial }}
        )">
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="controla_inventario" id="controla_inventario" x-model="controla" value="1">
                    <label class="form-check-label" for="controla_inventario">Control de inventario</label>
                </div>
                <small class="form-text text-muted">Si lo desactivas, el producto es pesable o sin stock y se vende sin descontar existencias.</small>
            </div>
            <div class="col-md-4 mb-3" x-show="controla">
                <label class="form-label">Unidad de medida</label>
                <select name="unidad_medida" class="form-select" x-model="unidadMedida" :disabled="!controla">
                    @foreach (['unidad', 'kg', 'g', 'litro', 'ml', 'metro', 'caja', 'paquete'] as $um)
                        <option value="{{ $um }}">{{ ucfirst($um) }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">También puede escribir una personalizada.</small>
            </div>
            <div class="col-md-4 mb-3" x-show="controla">
                <label class="form-label">Existencia actual</label>
                <input type="number" name="stock_actual" step="0.001" min="0" x-model.number="stockActual" class="form-control @error('stock_actual') is-invalid @enderror" value="{{ old('stock_actual', $producto?->stock_actual ?? 0) }}">
                @error('stock_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 mb-3" x-show="!controla">
                <label class="form-label">Unidad de medida</label>
                <input type="text" name="unidad_medida" class="form-control" x-model="unidadMedida" :disabled="controla" placeholder="kg, g, litro...">
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-boxes me-1"></i> Presentaciones y Precios</span>
        <small class="text-muted">Cada presentación tiene su propio margen de ganancia sobre el costo de la unidad base.</small>
    </div>
    <div class="card-body">
        <div x-data="presentacionesEditor({{ $costoUsdInicial }}, window.__productoForm.presentaciones, window.__productoForm.mapaTasas)">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Presentación</th>
                            <th style="width:130px">Factor (unid. base)</th>
                            <th style="width:130px">Margen (%)</th>
                            <th style="width:140px">Precio (USD)</th>
                            <th class="text-center" style="width:90px">Activa</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(pres, i) in presentaciones" :key="i">
                            <tr>
                                <td class="text-start">
                                    <input type="hidden" :name="'presentaciones[' + i + '][id]'" :value="pres.id || ''">
                                    <input type="text" class="form-control form-control-sm" :name="'presentaciones[' + i + '][nombre]'" x-model="pres.nombre" placeholder="Ej: Bulto 20 unid." required>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm text-center" :name="'presentaciones[' + i + '][factor_conversion]'" x-model.number="pres.factor_conversion" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-center" :name="'presentaciones[' + i + '][margen]'" x-model.number="pres.margen" required>
                                </td>
                                <td>
                                    <span class="fw-bold" x-text="'$' + precioPor(i).toFixed(2)"></span>
                                    <small class="text-muted d-block" x-text="'Bs ' + (precioPor(i) * (tasaActual() || 1)).toFixed(2)"></small>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" :name="'presentaciones[' + i + '][activa]'" :value="pres.activa ? '1' : '0'">
                                    <input type="checkbox" class="form-check-input" x-model="pres.activa" :id="'activa-' + i">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="quitar(i)" :disabled="presentaciones.length <= 1" title="Quitar presentación">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" @click="agregar">
                <i class="bi bi-plus-lg"></i> Agregar presentación
            </button>
            <div class="mt-2 small text-muted">
                Ejemplo: costo 1.68, presentación "Bulto 20 unid." factor 20 y margen 20% →
                precio = round(1.68 × 1.20, 2) × 20 = <span class="fw-bold">$40.40</span>
            </div>
            @error('presentaciones') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-receipt me-1"></i> Impuestos</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Impuesto</label>
                <select name="tiene_iva" class="form-select">
                    <option value="0" {{ old('tiene_iva', $producto?->tiene_iva ?? 0) == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('tiene_iva', $producto?->tiene_iva ?? 0) == '1' ? 'selected' : '' }}>Sí</option>
                </select>
                <small class="form-text text-muted">Aplica el porcentaje de IVA registrado en Impuestos.</small>
            </div>
        </div>
    </div>
</div>
