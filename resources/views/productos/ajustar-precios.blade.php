@extends('layouts.app')
@section('titulo', 'Actualizar Precios')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Actualizar Precios</h2>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Productos
    </a>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-precios" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start">Producto</th>
                        <th>Categoría</th>
                        <th>Precio Unit. USD</th>
                        <th>Precio May. USD</th>
                        <th>Cant. Mín. Mayor</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                    <tr x-data="precioRow({{ $p->id }}, {{ $p->precio_unitario_usd }}, {{ $p->precio_mayor_usd }}, {{ $p->cantidad_minima_mayor }})">
                        <td class="text-start">{{ $p->nombre }}</td>
                        <td>{{ $p->categoria->nombre ?? '-' }}</td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" x-model="precio_unitario_usd">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" x-model="precio_mayor_usd">
                        </td>
                        <td>
                            <input type="number" min="0" class="form-control form-control-sm" x-model="cantidad_minima_mayor">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" @click="guardar" :disabled="cargando">
                                <i class="bi bi-check-lg" x-show="!cargando"></i>
                                <span x-show="cargando" class="spinner-border spinner-border-sm"></span>
                            </button>
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
function precioRow(id, pu, pm, cmm) {
    return {
        precio_unitario_usd: pu,
        precio_mayor_usd: pm,
        cantidad_minima_mayor: cmm,
        cargando: false,
        guardar() {
            this.cargando = true;
            fetch('/productos/' + id + '/ajustar-precio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    precio_unitario_usd: this.precio_unitario_usd,
                    precio_mayor_usd: this.precio_mayor_usd,
                    cantidad_minima_mayor: this.cantidad_minima_mayor
                })
            })
            .then(r => r.json())
            .then(data => {
                this.cargando = false;
                if (data.success) {
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
        $('#tabla-precios').DataTable({
            columnDefs: [{ orderable: false, targets: -1 }],
            language: window.DataTableSpanish,
        });
    }
});
</script>
@endpush