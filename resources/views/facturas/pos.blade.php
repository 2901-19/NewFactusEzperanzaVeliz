@extends('layouts.app')
@section('titulo', 'Punto de Venta')
@section('contenido')
<div x-data="pos()" class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-body">
                <input type="text" class="form-control form-control-lg" placeholder="Buscar producto..." x-model="busqueda" @input="filtrarProductos">
            </div>
        </div>
        <div class="row g-2" style="max-height: 60vh; overflow-y: auto;">
            <template x-for="p in productosFiltrados" :key="p.id">
                <div class="col-md-4">
                    <div class="card h-100 text-center cursor-pointer" style="cursor: pointer;" @click="agregar(p)">
                        <div class="card-body p-2">
                            <strong x-text="p.nombre" class="small"></strong>
                            <div class="text-muted small" x-text="'Stock: ' + p.stock_total + ' uds'"></div>
                            <div class="mt-1">
                                <span class="badge bg-primary" x-text="'$' + parseFloat(p.precio_unitario_usd).toFixed(2)"></span>
                                <span class="badge bg-success" x-text="'$' + parseFloat(p.precio_mayor_usd).toFixed(2) + ' (' + p.cantidad_minima_mayor + '+)'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <div class="col-12 text-center text-muted" x-show="productosFiltrados.length === 0">
                No se encontraron productos.
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-cart"></i> Carrito</span>
                <span class="badge bg-secondary" x-text="carrito.length + ' items'"></span>
            </div>
            <div class="card-body p-2" style="max-height: 35vh; overflow-y: auto;">
                <template x-for="(item, index) in carrito" :key="index">
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong class="small" x-text="item.nombre"></strong>
                            <button class="btn btn-sm btn-outline-danger py-0" @click="carrito.splice(index, 1)">&times;</button>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <button class="btn btn-sm btn-outline-secondary" @click="item.cantidad > 1 && item.cantidad--">-</button>
                            <input type="number" x-model="item.cantidad" @input="item.cantidad = Math.max(1, parseInt(item.cantidad) || 1)" class="form-control form-control-sm text-center" style="width: 60px;" min="1">
                            <button class="btn btn-sm btn-outline-secondary" @click="item.cantidad++">+</button>
                            <span class="ms-auto small" x-text="'$' + (item.precioUnitario * item.cantidad).toFixed(2)"></span>
                        </div>
                        <div class="text-muted small" x-text="'Precio: $' + item.precioUnitario.toFixed(2) + (item.cantidad >= item.cantidad_minima_mayor ? ' (Mayor)' : ' (Unitario)')"></div>
                    </div>
                </template>
                <div class="text-center text-muted small py-3" x-show="carrito.length === 0">
                    Carrito vacío. Seleccione productos.
                </div>
            </div>

            <div class="card-footer">
                <div class="mb-2">
                    <label class="form-label small">Método de Pago</label>
                    <select x-model="metodoPago" class="form-select form-select-sm">
                        <option value="efectivo">Efectivo</option>
                        <option value="punto">Punto de Venta</option>
                        <option value="biopago">Biopago</option>
                        <option value="divisas">Divisas</option>
                        <option value="pago_movil">Pago Móvil</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Tipo de Factura</label>
                    <select x-model="tipoFactura" class="form-select form-select-sm" @change="clienteId = ''">
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito</option>
                    </select>
                </div>
                <div x-show="tipoFactura === 'credito'" class="mb-2">
                    <label class="form-label small">Cliente *</label>
                    <select x-model="clienteId" class="form-select form-select-sm" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->ci }})</option>
                        @endforeach
                    </select>
                </div>
                <hr>
                <div class="d-flex justify-content-between small">
                    <span>Subtotal Bs:</span>
                    <span x-text="subtotalBs.toFixed(2)"></span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>IVA (16%):</span>
                    <span x-text="ivaBs.toFixed(2)"></span>
                </div>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total Bs:</span>
                    <span x-text="totalBs.toFixed(2)"></span>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Total USD:</span>
                    <span x-text="'$' + totalUsd.toFixed(2)"></span>
                </div>
                <button class="btn btn-success w-100 mt-3" @click="guardarFactura" :disabled="carrito.length === 0 || (tipoFactura === 'credito' && !clienteId)">
                    <i class="bi bi-check-lg"></i> Generar Factura
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pos() {
    return {
        busqueda: '',
        productos: @json($productos),
        clientes: @json($clientes),
        tasas: @json($tasas),
        carrito: [],
        metodoPago: 'efectivo',
        tipoFactura: 'contado',
        clienteId: '',

        get productosFiltrados() {
            if (!this.busqueda) return this.productos;
            const b = this.busqueda.toLowerCase();
            return this.productos.filter(p =>
                p.nombre.toLowerCase().includes(b) ||
                (p.descripcion && p.descripcion.toLowerCase().includes(b))
            );
        },

        agregar(p) {
            const existente = this.carrito.find(i => i.id === p.id);
            if (existente) {
                existente.cantidad++;
            } else {
                this.carrito.push({
                    id: p.id,
                    nombre: p.nombre,
                    cantidad: 1,
                    precio_unitario_usd: parseFloat(p.precio_unitario_usd),
                    precio_mayor_usd: parseFloat(p.precio_mayor_usd),
                    cantidad_minima_mayor: p.cantidad_minima_mayor,
                    tiene_iva: p.tiene_iva,
                    fuente_tasa: p.fuente_tasa,
                    get precioUnitario() {
                        return this.cantidad >= this.cantidad_minima_mayor
                            ? this.precio_mayor_usd
                            : this.precio_unitario_usd;
                    }
                });
            }
        },

        get subtotalBs() {
            return this.carrito.reduce((sum, i) => {
                const tasa = this.tasas[i.fuente_tasa]
                    ? parseFloat(this.tasas[i.fuente_tasa].monto)
                    : 1;
                return sum + (i.precioUnitario * i.cantidad * tasa);
            }, 0);
        },

        get ivaBs() {
            return this.carrito.reduce((sum, i) => {
                if (!i.tiene_iva) return sum;
                const tasa = this.tasas[i.fuente_tasa]
                    ? parseFloat(this.tasas[i.fuente_tasa].monto)
                    : 1;
                return sum + (i.precioUnitario * i.cantidad * tasa * 0.16);
            }, 0);
        },

        get totalBs() {
            return this.subtotalBs + this.ivaBs;
        },

        get totalUsd() {
            return this.carrito.reduce((sum, i) => {
                return sum + (i.precioUnitario * i.cantidad);
            }, 0);
        },

        async guardarFactura() {
            if (this.carrito.length === 0) return;
            if (this.tipoFactura === 'credito' && !this.clienteId) {
                alert('Debe seleccionar un cliente para facturas a crédito.');
                return;
            }

            const items = this.carrito.map(i => ({
                producto_id: i.id,
                cantidad: i.cantidad,
            }));

            try {
                const res = await fetch('{{ route("facturas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        items,
                        metodo_pago: this.metodoPago,
                        cliente_id: this.tipoFactura === 'credito' ? this.clienteId : null,
                        estado: this.tipoFactura,
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    alert('Factura #' + data.correlativo + ' generada correctamente.');
                    this.carrito = [];
                    this.metodoPago = 'efectivo';
                    this.tipoFactura = 'contado';
                    this.clienteId = '';
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Error al guardar la factura.');
            }
        },
    };
}
</script>
@endpush
@endsection
