@extends('layouts.app')
@section('titulo', 'Impuestos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Impuestos</h2>
    <a href="{{ route('impuestos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo Impuesto</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Porcentaje</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($impuestos as $i)
            <tr>
                <td>{{ $i->id }}</td>
                <td>{{ $i->nombre }}</td>
                <td>{{ $i->porcentaje }}%</td>
                <td>{{ $i->fecha }}</td>
                <td>
                    <a href="{{ route('impuestos.edit', $i->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('impuestos.destroy', $i->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este impuesto?')"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-muted small">Total: {{ $impuestos->count() }} impuestos</div>
@endsection
