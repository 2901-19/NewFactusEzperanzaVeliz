@extends('layouts.app')
@section('titulo', 'Categorías')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Categorías</h2>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva</a>
</div>
<div class="table-responsive">
    <table id="dt-categorias" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categorias as $c)
            <tr>
                <td>{{ $c->nombre }}</td>
                <td>{{ $c->descripcion ?? '-' }}</td>
                <td><span class="badge bg-info">{{ $c->productos_count }}</span></td>
                <td>
                    <a href="{{ route('categorias.edit', $c->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('categorias.destroy', $c->id) }}"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable) { $('#dt-categorias').DataTable({ columnDefs: [{ orderable: false, targets: -1 }] }); }
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Eliminar categoría?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf, _method: 'DELETE' }).then(() => location.reload()); });
    });
});
</script>
@endpush
