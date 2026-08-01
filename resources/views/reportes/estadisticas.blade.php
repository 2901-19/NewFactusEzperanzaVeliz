@extends('layouts.app')
@section('titulo', 'Estadísticas de Ventas')
@section('contenido')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            @include('reportes.partials.selector-rango')
            <div class="col-6 col-md-4 col-xl">
                <label class="form-label small text-muted fw-semibold">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
            </div>
            <div class="col-6 col-md-4 col-xl">
                <label class="form-label small text-muted fw-semibold">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
            </div>
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 border-top pt-3 mt-2">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="{{ route('reportes.estadisticas') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $kpiCards = [
        ['label' => 'Ingresos Bs', 'valor' => $kpis['total_bs'], 'formato' => 'moneda'],
        ['label' => 'Ingresos USD', 'valor' => $kpis['total_usd'], 'formato' => 'usd'],
        ['label' => 'Facturas', 'valor' => $kpis['cantidad'], 'formato' => 'entero'],
        ['label' => 'Ticket promedio', 'valor' => $kpis['ticket_promedio'], 'formato' => 'moneda'],
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
            <div class="card-header">
                <i class="bi bi-layers"></i> Detal vs Mayor
                <div class="small text-muted fw-normal">
                    Detal: venta por unidad. Mayor: venta por caja o paquete. Agrupado por {{ $agrupadoPor }} del período
                </div>
            </div>
            <div class="card-body">
                @if (count($semanaLabels) > 0)
                <div class="d-flex gap-2 mb-2">
                    <span class="badge rounded-pill text-white" style="background-color:#198754">Detal {{ $detalResumen['pct'] }}%</span>
                    <span class="badge rounded-pill text-white" style="background-color:#fd7e14">Mayor {{ $mayorResumen['pct'] }}%</span>
                </div>
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
                @else
                <div class="text-center text-muted py-4">Sin ventas en el período.</div>
                @endif
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

    <div class="col-lg-6">
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
    <div class="col-lg-6">
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
@include('reportes.partials.filtro-fechas')
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

    @if (count($semanaLabels) > 0)
    new Chart(document.getElementById('chartDetalMayor'), {
        type: 'bar',
        data: {
            labels: @json($semanaLabels),
            datasets: [
                { label: 'Detal', data: @json($detalSeries), backgroundColor: '#198754', stack: 'ventas' },
                { label: 'Mayor', data: @json($mayorSeries), backgroundColor: '#fd7e14', stack: 'ventas' },
            ],
        },
        options: {
            scales: {
                x: { stacked: true },
                y: { stacked: true, ticks: { callback: (v) => 'Bs ' + v.toLocaleString('es-VE') } },
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const unidades = ctx.datasetIndex === 0 ? @json($detalUnidades) : @json($mayorUnidades);
                            return ' ' + ctx.dataset.label + ': Bs ' + ctx.parsed.y.toLocaleString('es-VE') + ' · ' + (unidades[ctx.dataIndex] ?? 0) + ' unidades';
                        },
                    },
                },
            },
        },
    });
    @endif

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
