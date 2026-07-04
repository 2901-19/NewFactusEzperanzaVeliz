@extends('layouts.app')
@section('titulo', 'Clientes')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Cliente
    </a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->ci }}</td>
                <td>{{ $c->nombre }}</td>
                <td>{{ $c->telefono ?? '-' }}</td>
                <td>
                    <a href="{{ route('clientes.edit', $c->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('clientes.destroy', $c->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este cliente?')"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-muted small">Total: {{ $clientes->count() }} clientes</div>
@endsection
