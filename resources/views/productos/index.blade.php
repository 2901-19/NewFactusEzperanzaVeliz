@extends('layouts.app')
@section('titulo', 'Productos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Productos</h2>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Stock Total</th>
                <th>Precio Unitario</th>
                <th>Precio Mayor</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            <tr class="{{ $p->trashed() ? 'table-secondary text-muted' : '' }}">
                <td>{{ $p->id }}</td>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->stock_total }} uds</td>
                <td>${{ number_format($p->precio_unitario_usd, 2) }}</td>
                <td>${{ number_format($p->precio_mayor_usd, 2) }}</td>
                <td>
                    @if ($p->trashed())
                        <span class="badge bg-secondary">Inactivo</span>
                    @else
                        <span class="badge bg-success">{{ $p->estado }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('productos.edit', $p->id) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if ($p->trashed())
                        <form action="{{ route('productos.restore', $p->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success" onclick="return confirm('¿Activar este producto?')">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('productos.destroy', $p->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Desactivar este producto?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-muted small">Total: {{ $productos->count() }} productos</div>
@endsection
