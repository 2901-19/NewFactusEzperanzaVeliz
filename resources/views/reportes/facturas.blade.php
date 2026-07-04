@extends('layouts.app')
@section('titulo', 'Reporte de Facturas')
@section('contenido')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-auto">
                <label class="form-label small">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
            </div>
            <div class="col-auto">
                <label class="form-label small">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
            </div>
            <div class="col-auto d-flex align-items-end">
                <button class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table id="reporteFacturasTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Correlativo</th>
                <th class="text-start">Cliente</th>
                <th>Total Bs</th>
                <th>Total USD</th>
                <th>Fecha</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturas as $f)
            <tr>
                <td><a href="{{ route('facturas.show', $f->id) }}">{{ $f->correlativo }}</a></td>
                <td class="text-start">{{ $f->cliente->nombre ?? 'Contado' }}</td>
                <td>Bs {{ number_format($f->total_bs, 2) }}</td>
                <td>${{ number_format($f->total_usd, 2) }}</td>
                <td>{{ $f->fecha_venta }}</td>
                <td>
                    @if ($f->estado === 'credito')
                        <span class="badge bg-warning text-dark">Crédito</span>
                    @else
                        <span class="badge bg-success">Contado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="2" class="text-end">Totales:</td>
                <td>Bs {{ number_format($totalBs, 2) }}</td>
                <td>${{ number_format($totalUsd, 2) }}</td>
                <td colspan="2">{{ $facturas->count() }} facturas</td>
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
        order: [[4, 'desc']],
        pageLength: 25,
    });
});
</script>
@endpush
