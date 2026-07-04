@extends('layouts.app')
@section('titulo', 'Impuestos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Impuestos</h2>
    <a href="{{ route('impuestos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo Impuesto</a>
</div>
<div class="table-responsive">
    <table id="dt-impuestos" class="table table-bordered table-striped">
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
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('impuestos.destroy', $i->id) }}"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
$('#dt-impuestos').DataTable({ order: [[0, 'desc']], columnDefs: [{ orderable: false, targets: -1 }] });
$(document).on('click', '.btn-delete', function () {
    const btn = $(this);
    Swal.fire({
        title: '¿Eliminar impuesto?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf, _method: 'DELETE' }).then(() => location.reload()); });
});
</script>
@endpush
