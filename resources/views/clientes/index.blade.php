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
    <table id="dt-clientes" class="table table-bordered table-striped">
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
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('clientes.destroy', $c->id) }}"><i class="bi bi-trash"></i></button>
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
    if ($.fn.DataTable) { $('#dt-clientes').DataTable({ order: [[0, 'desc']], columnDefs: [{ orderable: false, targets: -1 }] }); }
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Eliminar cliente?',
            text: 'Las facturas asociadas quedarán sin cliente.',
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
