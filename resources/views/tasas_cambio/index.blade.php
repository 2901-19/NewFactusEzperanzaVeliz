@extends('layouts.app')
@section('titulo', 'Tasas de Cambio')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Tasas de Cambio</h2>
    <a href="{{ route('tasas-cambio.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva Tasa</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Moneda</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasas as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td><span class="badge bg-info">{{ ucfirst($t->tipo) }}</span></td>
                <td>{{ $t->moneda }}</td>
                <td>{{ number_format($t->monto, 2) }}</td>
                <td>{{ $t->fecha }}</td>
                <td>
                    <a href="{{ route('tasas-cambio.edit', $t->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('tasas-cambio.destroy', $t->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta tasa?')"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-muted small">Total: {{ $tasas->count() }} tasas</div>
@endsection
