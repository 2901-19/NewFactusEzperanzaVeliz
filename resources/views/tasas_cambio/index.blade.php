@extends('layouts.app')
@section('titulo', 'Tasas de Cambio')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Tasas de Cambio</h2>
    <a href="{{ route('tasas-cambio.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva Tasa</a>
</div>
<div class="table-responsive">
    <table id="dt-tasas" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
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
                <td><span class="badge bg-info">{{ ucfirst($t->tipo) }}</span></td>
                <td>{{ $t->moneda }}</td>
                <td>{{ number_format($t->monto, 2) }}</td>
                <td>{{ $t->fecha }}</td>
                <td>
                    <a href="{{ route('tasas-cambio.edit', $t->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('tasas-cambio.destroy', $t->id) }}"><i class="bi bi-trash"></i></button>
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
    if ($.fn.DataTable) { $('#dt-tasas').DataTable({ columnDefs: [{ orderable: false, targets: -1 }] }); }
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Eliminar esta tasa?',
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
