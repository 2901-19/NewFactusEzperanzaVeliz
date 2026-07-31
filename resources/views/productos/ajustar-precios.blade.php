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
                        <th>Costo USD</th>
                        <th>Margen Detal %</th>
                        <th>Margen Mayor %</th>
                        <th>Precio Detal</th>
                        <th>Precio Mayor</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                    @php $tasaAjuste = $tasas[$p->fuente_tasa] ?? 1; @endphp
                    <tr x-data="precioRow({{ $p->id }}, {{ $p->costo_usd }}, {{ $p->margen_detal }}, {{ $p->margen_mayor }}, {{ $tasaAjuste }})">
                        <td class="text-start">{{ $p->nombre }}</td>
                        <td>{{ $p->categoria->nombre ?? '-' }}</td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" x-model="costo_usd">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" x-model="margen_detal">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" x-model="margen_mayor">
                        </td>
                        <td>
                            <span class="small fw-bold" x-text="'Bs ' + precioDetalBs.toFixed(2)"></span>
                            <small class="text-muted d-block" x-text="'($' + precioDetalUsd.toFixed(2) + ')'"></small>
                        </td>
                        <td>
                            <span class="small fw-bold" x-text="'Bs ' + precioMayorBs.toFixed(2)"></span>
                            <small class="text-muted d-block" x-text="'($' + precioMayorUsd.toFixed(2) + ')'"></small>
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
function precioRow(id, costoUsd, margenDetal, margenMayor, tasa) {
    return {
        costo_usd: costoUsd,
        margen_detal: margenDetal,
        margen_mayor: margenMayor,
        tasa: parseFloat(tasa) || 0,
        cargando: false,
        get precioDetalUsd() { return this.costo_usd * (1 + this.margen_detal / 100); },
        get precioMayorUsd() { return this.costo_usd * (1 + this.margen_mayor / 100); },
        get precioDetalBs() { return this.precioDetalUsd * this.tasa; },
        get precioMayorBs() { return this.precioMayorUsd * this.tasa; },
        guardar() {
            this.cargando = true;
            fetch('/productos/' + id + '/ajustar-precio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    costo_usd: this.costo_usd,
                    margen_detal: this.margen_detal,
                    margen_mayor: this.margen_mayor
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
