@extends('layouts.app')
@section('titulo', 'Reporte de Ventas')
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
                <label class="form-label small">Método de pago</label>
                <select name="metodo_pago" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($nombresMetodo as $valor => $nombre)
                        <option value="{{ $valor }}" {{ request('metodo_pago') == $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="contado" {{ request('estado') == 'contado' ? 'selected' : '' }}>Contado</option>
                    <option value="credito" {{ request('estado') == 'credito' ? 'selected' : '' }}>Crédito</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Vendedor</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($vendedores as $v)
                        <option value="{{ $v->id }}" {{ request('user_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Cliente</label>
                <select name="cliente_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id }}" {{ request('cliente_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('reportes.facturas') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('reportes.facturas', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <a href="{{ route('reportes.facturas', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel"></i> CSV</a>
            </div>
        </form>
    </div>
</div>

@php
    $kpiCards = [
        ['label' => 'Ingresos Bs', 'valor' => $kpis['total_bs']['valor'], 'variacion' => $kpis['total_bs']['variacion'], 'formato' => 'moneda'],
        ['label' => 'Ingresos USD', 'valor' => $kpis['total_usd']['valor'], 'variacion' => $kpis['total_usd']['variacion'], 'formato' => 'usd'],
        ['label' => 'IVA cobrado', 'valor' => $kpis['iva_bs']['valor'], 'variacion' => $kpis['iva_bs']['variacion'], 'formato' => 'moneda'],
        ['label' => 'Facturas', 'valor' => $kpis['cantidad']['valor'], 'variacion' => $kpis['cantidad']['variacion'], 'formato' => 'entero'],
        ['label' => 'Ticket promedio', 'valor' => $kpis['ticket_promedio']['valor'], 'variacion' => $kpis['ticket_promedio']['variacion'], 'formato' => 'moneda'],
    ];
@endphp
<div class="row g-2 mb-3">
    @foreach ($kpiCards as $card)
        @include('reportes.partials.kpi-card', $card)
    @endforeach
</div>

@php $totalDesglose = array_sum($desglose); @endphp
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-wallet2"></i> Ingresos por método de pago</div>
    <div class="card-body">
        <div class="row g-2">
            @foreach ($desglose as $metodo => $monto)
                @if ($monto > 0)
                <div class="col-md-4 col-lg-3">
                    <div class="border rounded p-2">
                        <div class="small text-muted">{{ $nombresMetodo[$metodo] ?? $metodo }}</div>
                        <div class="fw-bold">Bs {{ number_format($monto, 2) }}
                            <small class="text-muted">({{ $totalDesglose > 0 ? round(($monto / $totalDesglose) * 100, 1) : 0 }}%)</small>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
            @if ($totalDesglose == 0)
                <div class="text-muted small">Sin ventas en el período.</div>
            @endif
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="reporteFacturasTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Correlativo</th>
                <th class="text-start">Cliente</th>
                <th class="text-start">Método</th>
                <th class="text-start">Vendedor</th>
                <th>Total Bs</th>
                <th>IVA</th>
                <th>Total USD</th>
                <th>Fecha</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturas as $f)
            <tr>
                <td><a href="{{ route('facturas.show', $f->id) }}">{{ $f->correlativo }}</a></td>
                <td class="text-start">{{ $f->cliente->nombre ?? 'Contado' }}</td>
                <td class="text-start">{{ $nombresMetodo[$f->metodo_pago] ?? $f->metodo_pago }}</td>
                <td class="text-start">{{ $f->user->name ?? '—' }}</td>
                <td>Bs {{ number_format($f->total_bs, 2) }}</td>
                <td>Bs {{ number_format($f->iva_bs, 2) }}</td>
                <td>${{ number_format($f->total_usd, 2) }}</td>
                <td>{{ $f->fecha_venta?->format('d/m/Y') }}</td>
                <td>
                    @if ($f->estado === 'credito')
                        <span class="badge bg-warning text-dark">Crédito</span>
                    @else
                        <span class="badge bg-success">Contado</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">No hay facturas en el período.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="4" class="text-end">Totales:</td>
                <td>Bs {{ number_format($kpis['total_bs']['valor'], 2) }}</td>
                <td>Bs {{ number_format($kpis['iva_bs']['valor'], 2) }}</td>
                <td>${{ number_format($kpis['total_usd']['valor'], 2) }}</td>
                <td colspan="2">{{ number_format($kpis['cantidad']['valor']) }} facturas</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#reporteFacturasTable').DataTable({
        language: window.DataTableSpanish,
        order: [[7, 'desc']],
        pageLength: 25,
    });
});
</script>
@endpush
