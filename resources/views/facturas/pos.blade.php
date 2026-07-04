@extends('layouts.app')
@section('titulo', 'Punto de Venta')
@section('contenido')
<div x-data="pos" class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box"></i> Productos</span>
                <span class="small text-muted" x-text="'Agregados: ' + carrito.length"></span>
            </div>
            <div class="card-body">
                <table id="productosTable" class="table table-sm table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Stock</th>
                            <th>Precio Unit.</th>
                            <th>Precio Mayor</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->stock_total }} uds</td>
                            <td>${{ number_format($p->precio_unitario_usd, 2) }}</td>
                            <td>${{ number_format($p->precio_mayor_usd, 2) }} <small class="text-muted">({{ $p->cantidad_minima_mayor }}+)</small></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary agregar-producto" data-id="{{ $p->id }}" title="Agregar al carrito">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
                    <span>IVA ({{ $ivaPorcentaje }}%):</span>
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
                <button class="btn btn-success w-100 mt-3" @click="confirmarFactura" :disabled="carrito.length === 0 || (tipoFactura === 'credito' && !clienteId) || cargando">
                    <span x-show="!cargando"><i class="bi bi-check-lg"></i> Generar Factura</span>
                    <span x-show="cargando"><span class="spinner-border spinner-border-sm"></span> Procesando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Items:</strong> <span x-text="carrito.length"></span></p>
                    <p><strong>Subtotal Bs:</strong> <span x-text="subtotalBs.toFixed(2)"></span></p>
                    <p><strong>IVA ({{ $ivaPorcentaje }}%):</strong> <span x-text="ivaBs.toFixed(2)"></span></p>
                    <p class="fw-bold"><strong>Total Bs:</strong> <span x-text="totalBs.toFixed(2)"></span></p>
                    <p><strong>Total USD:</strong> $<span x-text="totalUsd.toFixed(2)"></span></p>
                    <p><strong>Método de Pago:</strong> <span x-text="metodoPago.charAt(0).toUpperCase() + metodoPago.slice(1)"></span></p>
                    <p><strong>Tipo:</strong> <span x-text="tipoFactura.charAt(0).toUpperCase() + tipoFactura.slice(1)"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" @click="guardarFactura">
                        <i class="bi bi-check-lg"></i> Confirmar y Facturar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast de éxito --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="errorToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pos', () => ({
        productos: @json($productos),
        clientes: @json($clientes),
        tasas: @json($tasas),
        ivaPorcentaje: {{ $ivaPorcentaje }},
        carrito: [],
        metodoPago: 'efectivo',
        tipoFactura: 'contado',
        clienteId: '',
        cargando: false,

        init() {
            if ($.fn.DataTable) {
                this.dataTable = $('#productosTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/es-ES.json',
                    },
                    pageLength: 15,
                    lengthMenu: [10, 15, 25, 50],
                    order: [[0, 'asc']],
                    columnDefs: [
                        { targets: 5, orderable: false },
                    ],
                });

                $('#productosTable tbody').on('click', '.agregar-producto', (e) => {
                    const id = parseInt($(e.currentTarget).data('id'));
                    const producto = this.productos.find(p => p.id === id);
                    if (producto) this.agregar(producto);
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    const input = document.querySelector('.dataTables_filter input');
                    if (input) input.focus();
                }
                if (e.key === 'F8' && this.carrito.length > 0) {
                    e.preventDefault();
                    this.confirmarFactura();
                }
            });
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
                return sum + (i.precioUnitario * i.cantidad * tasa * (this.ivaPorcentaje / 100));
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

        confirmarFactura() {
            if (this.carrito.length === 0) return;
            if (this.tipoFactura === 'credito' && !this.clienteId) {
                this.mostrarError('Debe seleccionar un cliente para facturas a crédito.');
                return;
            }
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        },

        async guardarFactura() {
            this.cargando = true;
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            if (modal) modal.hide();

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
                    this.mostrarExito('Factura #' + data.correlativo + ' generada correctamente.');
                    this.carrito = [];
                    this.metodoPago = 'efectivo';
                    this.tipoFactura = 'contado';
                    this.clienteId = '';
                } else {
                    this.mostrarError('Error: ' + data.message);
                }
            } catch (e) {
                this.mostrarError('Error al conectar con el servidor.');
            } finally {
                this.cargando = false;
            }
        },

        mostrarExito(msg) {
            document.getElementById('toastMessage').textContent = msg;
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        },

        mostrarError(msg) {
            document.getElementById('errorToastMessage').textContent = msg;
            const toast = new bootstrap.Toast(document.getElementById('errorToast'));
            toast.show();
        },
    }));
});
</script>
@endpush
@endsection
