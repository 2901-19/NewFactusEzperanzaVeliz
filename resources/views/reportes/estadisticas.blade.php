@extends('layouts.app')
@section('titulo', 'Estadísticas de Ventas')
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
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('reportes.estadisticas') }}" class="btn btn-sm btn-outline-secondary">Últimos 30 días</a>
            </div>
        </form>
    </div>
</div>

@php
    $kpiCards = [
        ['label' => 'Ingresos Bs', 'valor' => $kpis['total_bs']['valor'], 'variacion' => $kpis['total_bs']['variacion'], 'formato' => 'moneda'],
        ['label' => 'Ingresos USD', 'valor' => $kpis['total_usd']['valor'], 'variacion' => $kpis['total_usd']['variacion'], 'formato' => 'usd'],
        ['label' => 'Facturas', 'valor' => $kpis['cantidad']['valor'], 'variacion' => $kpis['cantidad']['variacion'], 'formato' => 'entero'],
        ['label' => 'Ticket promedio', 'valor' => $kpis['ticket_promedio']['valor'], 'variacion' => $kpis['ticket_promedio']['variacion'], 'formato' => 'moneda'],
    ];
@endphp
<div class="row g-2 mb-3">
    @foreach ($kpiCards as $card)
        @include('reportes.partials.kpi-card', $card)
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Ventas Bs por día</div>
            <div class="card-body">
                <canvas id="chartVentasDiarias" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Métodos de pago</div>
            <div class="card-body">
                <canvas id="chartMetodos" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-layers"></i> Detal vs Mayor (Bs por semana)</div>
            <div class="card-body">
                <canvas id="chartDetalMayor" height="120"></canvas>
                <div class="row g-2 mt-2 text-center">
                    <div class="col-6">
                        <div class="border rounded p-2 bg-success-subtle">
                            <div class="small text-muted">Detal</div>
                            <div class="fw-bold">Bs {{ number_format($detalResumen['bs'], 2) }}</div>
                            <div class="small">{{ number_format($detalResumen['unidades']) }} unidades</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 bg-warning-subtle">
                            <div class="small text-muted">Mayor</div>
                            <div class="fw-bold">Bs {{ number_format($mayorResumen['bs'], 2) }}</div>
                            <div class="small">{{ number_format($mayorResumen['unidades']) }} unidades</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Top 10 productos por ingresos</div>
            <div class="card-body">
                <canvas id="chartTopProductos" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people"></i> Ventas por vendedor</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="text-start">Vendedor</th><th>Facturas</th><th>Total Bs</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($porVendedor as $v)
                        <tr>
                            <td class="text-start">{{ $v['nombre'] }}</td>
                            <td>{{ $v['facturas'] }}</td>
                            <td>Bs {{ number_format($v['total_bs'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy"></i> Top clientes</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="text-start">Cliente</th><th>Compras</th><th>Total Bs</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($topClientes as $c)
                        <tr>
                            <td class="text-start">{{ $c['nombre'] }}</td>
                            <td>{{ $c['facturas'] }}</td>
                            <td>Bs {{ number_format($c['total_bs'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 border-danger">
            <div class="card-header"><i class="bi bi-credit-card"></i> Créditos pendientes</div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="fs-4 fw-bold text-danger">Bs {{ number_format($creditos->total_bs, 2) }}</div>
                <div class="text-muted">{{ $creditos->cantidad }} facturas por cobrar</div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const porDia = @json($porDia);

    new Chart(document.getElementById('chartVentasDiarias'), {
        type: 'line',
        data: {
            labels: Object.keys(porDia),
            datasets: [{
                label: 'Ventas Bs',
                data: Object.values(porDia),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.12)',
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: (v) => 'Bs ' + v.toLocaleString('es-VE') } } },
        },
    });

    const nombresMetodo = @json($nombresMetodo);
    const porMetodo = @json($porMetodo);
    const metodos = Object.entries(porMetodo).filter(([, v]) => v > 0);

    new Chart(document.getElementById('chartMetodos'), {
        type: 'doughnut',
        data: {
            labels: metodos.map(([k]) => nombresMetodo[k] || k),
            datasets: [{ data: metodos.map(([, v]) => v), borderWidth: 1 }],
        },
    });

    new Chart(document.getElementById('chartDetalMayor'), {
        type: 'bar',
        data: {
            labels: @json($semanaLabels),
            datasets: [
                { label: 'Detal', data: @json($detalSeries), backgroundColor: '#198754' },
                { label: 'Mayor', data: @json($mayorSeries), backgroundColor: '#fd7e14' },
            ],
        },
        options: {
            scales: {
                x: { stacked: true },
                y: { stacked: true, ticks: { callback: (v) => 'Bs ' + v.toLocaleString('es-VE') } },
            },
        },
    });

    const topProductos = @json($topProductos);

    new Chart(document.getElementById('chartTopProductos'), {
        type: 'bar',
        data: {
            labels: topProductos.map((p) => p.nombre),
            datasets: [{ label: 'Ingresos Bs', data: topProductos.map((p) => p.ingreso_bs), backgroundColor: '#0dcaf0' }],
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
        },
    });
});
</script>
@endpush
