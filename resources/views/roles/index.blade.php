@extends('layouts.app')
@section('titulo', 'Roles')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Roles</h2>
    <a href="{{ route('roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Rol
    </a>
</div>
@if ($roles->where('protegido', false)->isEmpty())
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i> No hay roles creados aún. El rol <strong>Administrador</strong> está protegido y gestiona todo el sistema. Crea los roles que necesites (ej. Cajero, Supervisor) y asígnales los permisos.
</div>
@endif
<div class="table-responsive">
    <table id="dt-roles" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Identificador</th>
                <th>Descripción</th>
                <th>Permisos</th>
                <th>Usuarios</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $rol)
            <tr>
                <td class="text-start fw-semibold">{{ $rol->nombre }}</td>
                <td><code>{{ $rol->slug }}</code></td>
                <td class="text-start">{{ $rol->descripcion ?: '-' }}</td>
                <td><span class="badge bg-primary">{{ $rol->permisos_count }}</span></td>
                <td><span class="badge bg-secondary">{{ $rol->usuarios_count }}</span></td>
                <td>
                    @if ($rol->protegido)
                        <span class="badge bg-danger">Protegido</span>
                    @else
                        <span class="badge bg-info text-dark">Personalizado</span>
                    @endif
                </td>
                <td>
                    @unless ($rol->protegido)
                    <a href="{{ route('roles.edit', $rol->id) }}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('roles.destroy', $rol->id) }}" data-name="{{ $rol->nombre }}">
                        <i class="bi bi-trash"></i>
                    </button>
                    @else
                    <span class="text-muted small">—</span>
                    @endunless
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
    if ($.fn.DataTable) { $('#dt-roles').DataTable({ columnDefs: [{ orderable: false, targets: -1 }] }); }
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Eliminar rol?',
            text: 'Se eliminará el rol "' + btn.data('name') + '" y sus permisos asociados.',
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
