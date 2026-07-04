@extends('layouts.app')
@section('titulo', 'Stock Bajo')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Productos con Stock Bajo (&le;10 uds)</h2>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Producto</th>
                <th>Paquetes</th>
                <th>Unidades</th>
                <th>Stock Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $p)
            <tr>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->stock_paquetes }}</td>
                <td>{{ $p->stock_unidades }}</td>
                <td>
                    <span class="badge bg-{{ $p->stock_total <= 5 ? 'danger' : 'warning text-dark' }}">
                        {{ $p->stock_total }} uds
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Todos los productos tienen stock suficiente.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
