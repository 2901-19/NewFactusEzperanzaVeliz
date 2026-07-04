@extends('layouts.app')
@section('titulo', 'Usuarios')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Usuarios</h2>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Usuario
    </a>
</div>
<div class="table-responsive">
    <table id="dt-usuarios" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
            <tr>
                <td class="text-start">{{ $u->name }}</td>
                <td>{{ $u->usuario }}</td>
                <td>{{ $u->email ?? '-' }}</td>
                <td>
                    @if ($u->rol === 'admin')
                        <span class="badge bg-danger">Admin</span>
                    @else
                        <span class="badge bg-secondary">Cajero</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                    @if ($u->id !== auth()->id())
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('usuarios.destroy', $u->id) }}" data-name="{{ $u->name }}">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
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
    if ($.fn.DataTable) { $('#dt-usuarios').DataTable({ columnDefs: [{ orderable: false, targets: -1 }] }); }
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: 'Se eliminará ' + btn.data('name'),
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