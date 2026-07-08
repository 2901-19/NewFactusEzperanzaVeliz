@extends('layouts.app')
@section('titulo', 'Actualizar Inventario')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Actualizar Inventario</h2>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Productos
    </a>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-inventario" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start">Producto</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Cant. Ajustar</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                    <tr x-data="inventarioRow({{ $p->id }}, {{ $p->stock_paquetes }}, {{ $p->stock_unidades }}, {{ $p->unidades_por_paquete }})">
                        <td class="text-start">{{ $p->nombre }}</td>
                        <td>{{ $p->categoria->nombre ?? '-' }}</td>
                        <td><span x-text="stockPaq + ' paq / ' + stockUnd + ' und'"></span></td>
                        <td>
                            <input type="number" min="1" class="form-control form-control-sm" x-model="cantidad" placeholder="0">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success me-1" @click="ajustar('+')" :disabled="cargando || !cantidad">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" @click="ajustar('-')" :disabled="cargando || !cantidad">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <span x-show="cargando" class="spinner-border spinner-border-sm ms-1"></span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function inventarioRow(id, paq, und, upp) {
    return {
        stockPaq: paq,
        stockUnd: und,
        unidadesPorPaquete: upp,
        cantidad: null,
        cargando: false,
        ajustar(operacion) {
            if (!this.cantidad || this.cantidad < 1) return;
            this.cargando = true;
            fetch('/productos/' + id + '/ajustar-inventario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ cantidad: this.cantidad, operacion })
            })
            .then(r => r.json())
            .then(data => {
                this.cargando = false;
                if (data.success) {
                    this.stockPaq = data.stock_paquetes;
                    this.stockUnd = data.stock_unidades;
                    this.cantidad = null;
                    Swal.fire({ icon: 'success', title: 'Actualizado', text: data.message, timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(() => {
                this.cargando = false;
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión' });
            });
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable) {
        $('#tabla-inventario').DataTable({
            columnDefs: [{ orderable: false, targets: -1 }],
            language: window.DataTableSpanish,
        });
    }
});
</script>
@endpush