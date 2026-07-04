@extends('layouts.app')
@section('titulo', 'Categorías')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Categorías</h2>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $c)
            <tr>
                <td>{{ $c->nombre }}</td>
                <td>{{ $c->descripcion ?? '-' }}</td>
                <td><span class="badge bg-info">{{ $c->productos_count }}</span></td>
                <td>
                    <a href="{{ route('categorias.edit', $c->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('categorias.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">No hay categorías registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
