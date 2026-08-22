@extends('layouts.app')
@section('titulo', 'Productos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Productos</h2>
    <div>
        @if (Auth::user()->hasPermiso('actualizar-precios'))
        <a href="{{ route('productos.ajustar-precios') }}" class="btn btn-warning me-1">
            <i class="bi bi-currency-dollar"></i> Actualizar Precios
        </a>
        @endif
        @if (Auth::user()->hasPermiso('actualizar-inventarios'))
        <a href="{{ route('productos.ajustar-inventario') }}" class="btn btn-info me-1 text-white">
            <i class="bi bi-box"></i> Actualizar Inventario
        </a>
        @endif
        <a href="{{ route('productos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="dt-productos" class="table table-bordered table-striped thumbs-table">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Nombre</th>
                <th>Ref.</th>
                <th>Categoría</th>
                <th>Existencia</th>
                <th>Precios (presentaciones)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            <tr class="{{ $p->trashed() ? 'table-secondary text-muted' : '' }}">
                <td class="text-start">{{ $p->nombre }}</td>
                <td>
                    @if ($p->imagen_url)
                        <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="thumb">
                    @else
                        <span class="text-muted sin-ref">Sin referencia</span>
                    @endif
                </td>
                <td>{{ $p->categoria->nombre ?? '-' }}</td>
                <td>
                    @if ($p->controla_inventario)
                        {{ number_format($p->stock_actual, 2, ',', '.') }} {{ $p->unidad_medida ?? 'unidad' }}
                    @else
                        <span class="badge bg-secondary">Sin inventario</span>
                    @endif
                </td>
                <td>
                    @forelse ($p->presentaciones as $pres)
                        @if ($pres->activa)
                        @php $tasaPres = $tasas->get($pres->fuente_tasa); @endphp
                        <div class="small text-nowrap">
                            {{ $pres->nombre }}:
                            @if ($tasaPres)
                                <span class="fw-bold">Bs {{ number_format($pres->precio_usd * $tasaPres, 2) }}</span>
                                <small class="text-muted">(${{ number_format($pres->precio_usd, 2) }})</small>
                            @else
                                <span class="badge bg-danger" title="Configure la tasa '{{ $pres->fuente_tasa }}' en Tasas de Cambio">Sin tasa</span>
                            @endif
                        </div>
                        @endif
                    @empty
                        <span class="text-muted">Sin precios</span>
                    @endforelse
                </td>
                <td>
                    @if ($p->trashed())
                        <span class="badge bg-secondary">Inactivo</span>
                    @else
                        <span class="badge bg-success">{{ $p->estado }}</span>
                    @endif
                </td>
                <td>
                    @unless ($p->trashed())
                        <a href="{{ route('productos.edit', $p->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endunless
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
document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable) {
        $('#dt-productos').DataTable({
            columnDefs: [{ orderable: false, targets: -1 }],
        });
    }
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
});
</script>
@endpush
