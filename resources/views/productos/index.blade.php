@extends('layouts.app')
@section('titulo', 'Productos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Productos</h2>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </a>
</div>
<div class="table-responsive">
    <table id="dt-productos" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock Total</th>
                <th>Precio Unitario</th>
                <th>Precio Mayor</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            <tr class="{{ $p->trashed() ? 'table-secondary text-muted' : '' }}">
                <td>{{ $p->id }}</td>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->categoria->nombre ?? '-' }}</td>
                <td>{{ $p->stock_total }} uds</td>
                <td>${{ number_format($p->precio_unitario_usd, 2) }}</td>
                <td>${{ number_format($p->precio_mayor_usd, 2) }}</td>
                <td>
                    @if ($p->trashed())
                        <span class="badge bg-secondary">Inactivo</span>
                    @else
                        <span class="badge bg-success">{{ $p->estado }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('productos.edit', $p->id) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if ($p->trashed())
                    <button class="btn btn-sm btn-success btn-restore" data-url="{{ route('productos.restore', $p->id) }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    @else
                    <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('productos.destroy', $p->id) }}">
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
$('#dt-productos').DataTable({
    order: [[0, 'desc']],
    columnDefs: [{ orderable: false, targets: -1 }],
});
$(document).on('click', '.btn-delete', function () {
    const btn = $(this);
    Swal.fire({
        title: '¿Desactivar producto?',
        text: 'El producto quedará inactivo, pero podrás restaurarlo después.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
    }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf, _method: 'DELETE' }).then(() => location.reload()); });
});
$(document).on('click', '.btn-restore', function () {
    const btn = $(this);
    Swal.fire({
        title: '¿Activar producto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar',
    }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf }).then(() => location.reload()); });
});
</script>
@endpush
