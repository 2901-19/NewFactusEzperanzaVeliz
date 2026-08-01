@extends('layouts.app')
@section('titulo', 'Rentabilidad por Producto')
@section('contenido')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
            </div>
            <div class="col-auto">
                <label class="form-label small">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
            </div>
            <div class="col-auto">
                <label class="form-label small">Tipo de venta</label>
                <select name="tipo_venta" class="form-select form-select-sm">
                    <option value="">Detal y Mayor</option>
                    <option value="unitario" {{ $tipoVenta === 'unitario' ? 'selected' : '' }}>Detal</option>
                    <option value="mayor" {{ $tipoVenta === 'mayor' ? 'selected' : '' }}>Mayor</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('reportes.rentabilidad', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel"></i> CSV</a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info small py-2">
    <i class="bi bi-info-circle"></i> La ganancia es <strong>estimada</strong>: se calcula restando al ingreso vendido el costo del producto
    (<code>costo_usd × tasa actual</code>). El margen real puede variar si la tasa cambia entre la compra y la venta.
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Top 10 por ganancia estimada</div>
            <div class="card-body">
                <canvas id="chartTopGanancia" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        @php
            $sumIngreso = $filas->sum('ingreso_bs');
            $sumCosto = $filas->sum('costo_bs');
            $sumGanancia = $filas->sum('ganancia_bs');
            $margenGlobal = $sumIngreso > 0 ? round(($sumGanancia / $sumIngreso) * 100, 1) : 0;
        @endphp
        <div class="row g-2 h-100">
            <div class="col-6">
                <div class="card h-100 bg-success-subtle">
                    <div class="card-body py-3">
                        <div class="small text-muted text-uppercase">Ganancia estimada</div>
                        <div class="fs-4 fw-bold text-success">Bs {{ number_format($sumGanancia, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="small text-muted text-uppercase">Margen global</div>
                        <div class="fs-4 fw-bold">{{ number_format($margenGlobal, 1) }}%</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="small text-muted text-uppercase">Ingresos</div>
                        <div class="fs-5 fw-bold">Bs {{ number_format($sumIngreso, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="small text-muted text-uppercase">Costo estimado</div>
                        <div class="fs-5 fw-bold">Bs {{ number_format($sumCosto, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="rentabilidadTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Producto</th>
                <th>Unidades</th>
                <th>Ingreso Bs</th>
                <th>Ingreso USD</th>
                <th>Costo est. Bs</th>
                <th>Ganancia est. Bs</th>
                <th>Margen</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $f)
            <tr>
                <td class="text-start">{{ $f->nombre }}</td>
                <td>{{ number_format($f->unidades) }}</td>
                <td>Bs {{ number_format($f->ingreso_bs, 2) }}</td>
                <td>${{ number_format($f->ingreso_usd, 2) }}</td>
                <td>Bs {{ number_format($f->costo_bs, 2) }}</td>
                <td class="fw-bold">Bs {{ number_format($f->ganancia_bs, 2) }}</td>
                <td>
                    @if ($f->margen < 5)
                        <span class="badge bg-danger">{{ $f->margen }}%</span>
                    @elseif ($f->margen < 15)
                        <span class="badge bg-warning text-dark">{{ $f->margen }}%</span>
                    @else
                        <span class="badge bg-success">{{ $f->margen }}%</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Sin ventas en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const top = @json($topGanancia);

    new Chart(document.getElementById('chartTopGanancia'), {
        type: 'bar',
        data: {
            labels: top.map((p) => p.nombre),
            datasets: [{ label: 'Ganancia est. Bs', data: top.map((p) => p.ganancia_bs), backgroundColor: '#198754' }],
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
        },
    });

    $('#rentabilidadTable').DataTable({
        language: window.DataTableSpanish,
        pageLength: 25,
        order: [[5, 'desc']],
    });
});
</script>
@endpush
