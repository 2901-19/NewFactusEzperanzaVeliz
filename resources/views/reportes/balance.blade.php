@extends('layouts.app')
@section('titulo', 'Balance Mensual')
@section('contenido')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small">Año</label>
                <select name="anio" class="form-select form-select-sm">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Ver</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Mes</th>
                <th>Facturas</th>
                <th>Total Bs</th>
                <th>Total USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meses as $num => $nombre)
                @php $m = $mensual->get($num); @endphp
                <tr>
                    <td>{{ $nombre }}</td>
                    <td>{{ $m->cantidad ?? 0 }}</td>
                    <td>{{ number_format($m->total_bs ?? 0, 2) }}</td>
                    <td>${{ number_format($m->total_usd ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="fw-bold">
            <tr>
                <td>Total</td>
                <td>{{ $mensual->sum('cantidad') }}</td>
                <td>{{ number_format($mensual->sum('total_bs'), 2) }}</td>
                <td>${{ number_format($mensual->sum('total_usd'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
