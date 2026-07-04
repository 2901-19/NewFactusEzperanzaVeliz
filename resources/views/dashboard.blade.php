@extends('layouts.app')
@section('titulo', 'Dashboard')
@section('contenido')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Ventas Hoy</h6>
                <h3>{{ $ventasHoy }}</h3>
                <small>Bs {{ number_format($totalHoyBs, 2) }} / ${{ number_format($totalHoyUsd, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Ventas del Mes</h6>
                <h3>{{ $ventasMes }}</h3>
                <small>Bs {{ number_format($totalMesBs, 2) }} / ${{ number_format($totalMesUsd, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6>Créditos Pendientes</h6>
                <h3>{{ $creditosPendientes }}</h3>
                <small>Bs {{ number_format($totalCreditosPendientesBs, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Inventario</h6>
                <h3>{{ $totalProductos }}</h3>
                <small>{{ $totalClientes }} clientes</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Productos con Stock Bajo (&le;10 uds)</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productosStockBajo as $p)
                        <tr>
                            <td>{{ $p->nombre }}</td>
                            <td><span class="badge bg-danger">{{ $p->stock_total }} uds</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Sin productos con stock bajo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Productos Más Vendidos</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Total Vendido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masVendidos as $p)
                        <tr>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->total }} uds</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Sin ventas aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
