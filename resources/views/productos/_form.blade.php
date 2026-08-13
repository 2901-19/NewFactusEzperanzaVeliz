@php
    $presentacionesIniciales = old('presentaciones', $producto?->presentaciones ?? [['nombre' => 'Unidad', 'factor_conversion' => 1, 'margen' => 0, 'activa' => true]]);
    $costoUsdInicial = old('costo_usd', $producto?->costo_usd ?? 0);
    $fuenteTasaInicial = old('fuente_tasa', $producto?->fuente_tasa ?? $opcionesTasa->keys()->first() ?? 'promedio');
    $controlaInventario = old('controla_inventario', $producto?->controla_inventario ?? true) ? 'true' : 'false';
    $unidadMedidaInicial = old('unidad_medida', $producto?->unidad_medida ?? 'unidad');
    $stockInicial = old('stock_actual', $producto?->stock_actual ?? 0);
    $tieneImagen = ! empty($producto?->imagen);
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
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto?->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                        <option value="">Sin categoría</option>
                        @foreach (\App\Models\Categoria::orderBy('nombre')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto?->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="disponible" {{ old('estado', $producto?->estado ?? 'disponible') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="no_disponible" {{ old('estado', $producto?->estado ?? 'disponible') == 'no_disponible' ? 'selected' : '' }}>No Disponible</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="text-center" x-data="{ preview: null, tieneImagen: {{ $tieneImagen ? 'true' : 'false' }} }">
                    <div class="marco-imagen mx-auto">
                        <img x-show="preview" :src="preview" class="marco-imagen-img" alt="Vista previa de la imagen">
                        <img x-show="!preview && tieneImagen" src="{{ $producto?->imagen ? asset('storage/'.$producto->imagen) : '' }}" class="marco-imagen-img" alt="{{ $producto?->nombre ?? '' }}">
                        <div x-show="!preview && !tieneImagen" class="marco-imagen-placeholder">
                            <i class="bi bi-image fs-1"></i>
                            <span>Sin imagen</span>
                        </div>
                    </div>
                    <label class="btn btn-outline-secondary btn-sm mt-3 mb-0">
                        <i class="bi bi-upload me-1"></i>
                        <span x-text="tieneImagen || preview ? 'Cambiar imagen' : 'Seleccionar imagen'"></span>
                        <input type="file" name="imagen" accept="image/*" class="d-none" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                    @error('imagen') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div x-data="presentacionesEditor({{ $costoUsdInicial }}, window.__productoForm.presentaciones, window.__productoForm.mapaTasas, window.__productoForm.fuenteTasa)">
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Costo e Inventario</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Costo unitario (USD) *</label>
                    <input type="number" step="0.01" min="0" name="costo_usd" x-model.number="costoUsd" class="form-control @error('costo_usd') is-invalid @enderror" value="{{ old('costo_usd', $producto?->costo_usd) }}">
                    @error('costo_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Tasa del producto</label>
                    <select name="fuente_tasa" x-model="fuenteTasa" class="form-select">
                        @forelse ($opcionesTasa as $tipo => $nombre)
                            <option value="{{ $tipo }}" {{ old('fuente_tasa', $producto?->fuente_tasa ?? $opcionesTasa->keys()->first() ?? '') == $tipo ? 'selected' : '' }}>{{ $nombre }}</option>
                        @empty
                            <option value="" disabled>No hay tasas activas. Créalas en Tasas de Cambio.</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Impuesto</label>
                    <select name="impuesto_id" class="form-select">
                        <option value="">No tiene</option>
                        @foreach ($impuestos as $imp)
                            <option value="{{ $imp->id }}" {{ (string) old('impuesto_id', $producto?->impuesto_id) === (string) $imp->id ? 'selected' : '' }}>
                                {{ $imp->nombre }} ({{ $imp->porcentaje }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6" x-data="inventarioControl(
                {{ $controlaInventario }},
                window.__productoForm.unidadMedida,
                {{ $stockInicial }}
            )">
                <div class="mb-3">
                    <label class="form-label">Control de inventario</label>
                    <div class="switch-form-control">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="controla_inventario" id="controla_inventario" x-model="controla" value="1">
                            <label class="form-check-label" for="controla_inventario">
                                <span x-text="controla ? 'Activo' : 'Inactivo'"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unidad de medida</label>
                    <select name="unidad_medida" class="form-select" x-model="unidadMedida">
                        @php $unidadesPredefinidas = ['unidad', 'kg', 'g', 'litro', 'ml', 'metro', 'caja', 'paquete']; @endphp
                        @if (! in_array($unidadMedidaInicial, $unidadesPredefinidas, true))
                            <option value="{{ $unidadMedidaInicial }}">{{ ucfirst($unidadMedidaInicial) }}</option>
                        @endif
                        @foreach ($unidadesPredefinidas as $um)
                            <option value="{{ $um }}">{{ ucfirst($um) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" x-show="controla">
                    <label class="form-label">Existencia actual</label>
                    <input type="number" name="stock_actual" step="0.001" min="0" x-model.number="stockActual" class="form-control @error('stock_actual') is-invalid @enderror" value="{{ old('stock_actual', $producto?->stock_actual ?? 0) }}">
                    @error('stock_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-boxes me-1"></i> Presentaciones y Precios</div>
    <div class="card-body">
        <div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start" style="width:24%">Presentación</th>
                            <th class="text-center" style="width:110px">Unidades</th>
                            <th class="text-center" style="width:110px">Margen (%)</th>
                            <th class="text-end" style="width:110px">Precio USD</th>
                            <th class="text-end" style="width:130px">Precio Bs</th>
                            <th class="text-center text-nowrap" style="width:140px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(pres, i) in presentaciones" :key="i">
                            <tr>
                                <td class="text-start">
                                    <input type="hidden" :name="'presentaciones[' + i + '][id]'" :value="pres.id || ''">
                                    <input type="text" class="form-control" :name="'presentaciones[' + i + '][nombre]'" x-model="pres.nombre" required>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0.0001" class="form-control text-center" :name="'presentaciones[' + i + '][factor_conversion]'" x-model.number="pres.factor_conversion" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control text-center" :name="'presentaciones[' + i + '][margen]'" x-model.number="pres.margen" required>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" x-text="'$' + precioPor(i).toFixed(2)"></span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" x-text="'Bs ' + (precioPor(i) * (tasaActual() || 1)).toFixed(2)"></span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <input type="hidden" :name="'presentaciones[' + i + '][activa]'" :value="pres.activa ? '1' : '0'">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" x-model="pres.activa" :id="'activa-' + i">
                                            <label class="form-check-label small text-muted" :for="'activa-' + i" x-text="pres.activa ? 'Sí' : 'No'"></label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="quitar(i)" :disabled="presentaciones.length <= 1" title="Quitar presentación">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" @click="agregar">
                <i class="bi bi-plus-lg"></i> Agregar presentación
            </button>
            @error('presentaciones') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
</div>
