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
                        <th>Existencia Actual</th>
                        <th>Cant. Ajustar</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                    <tr x-data="inventarioRow({{ $p->id }}, {{ $p->stock_actual }}, {{ $p->controla_inventario ? 'true' : 'false' }})">
                        <td class="text-start">{{ $p->nombre }}</td>
                        <td>{{ $p->categoria->nombre ?? '-' }}</td>
                        <td>
                            <template x-if="controlaInventario">
                                <span class="fw-bold" x-text="stockActual + ' {{ $p->unidad_medida ?? 'unidad' }}'"></span>
                            </template>
                            <template x-if="!controlaInventario">
                                <span class="badge bg-secondary">No lleva inventario</span>
                            </template>
                        </td>
                        <td>
                            <input type="number" min="0.01" step="0.001" class="form-control form-control-sm" x-model="cantidad" placeholder="0" :disabled="!controlaInventario">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success me-1" @click="ajustar('+')" :disabled="cargando || !cantidad || !controlaInventario">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" @click="ajustar('-')" :disabled="cargando || !cantidad || !controlaInventario">
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
function inventarioRow(id, stock, controla) {
    return {
        stockActual: stock,
        controlaInventario: !!controla,
        cantidad: null,
        cargando: false,
        ajustar(operacion) {
            if (!this.cantidad || this.cantidad < 0.01) return;
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
                    this.stockActual = data.stock_actual;
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
