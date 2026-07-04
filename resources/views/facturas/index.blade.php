@extends('layouts.app')
@section('titulo', 'Facturas')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Todas las Facturas</h2>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Correlativo</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Total Bs</th>
                <th>Total USD</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturas as $f)
            <tr class="{{ $f->estado === 'anulada' ? 'table-danger text-muted' : '' }}">
                <td>{{ $f->id }}</td>
                <td>{{ $f->correlativo }}</td>
                <td>{{ $f->cliente->nombre ?? 'Contado' }}</td>
                <td>{{ $f->user->usuario ?? 'N/A' }}</td>
                <td>{{ number_format($f->total_bs, 2) }}</td>
                <td>${{ number_format($f->total_usd, 2) }}</td>
                <td>{{ $f->fecha_venta }}</td>
                <td>
                    @if ($f->estado === 'anulada')
                        <span class="badge bg-danger">Anulada</span>
                    @elseif ($f->estado === 'credito')
                        <span class="badge bg-warning text-dark">
                            Crédito {{ $f->estado_credito === 'cancelado' ? '(Cancelado)' : '(Pendiente)' }}
                        </span>
                    @else
                        <span class="badge bg-success">Contado</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('facturas.show', $f->id) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if ($f->estado !== 'anulada')
                        <button class="btn btn-sm btn-danger btn-anular" data-url="{{ route('facturas.anular', $f->id) }}" data-correlativo="{{ $f->correlativo }}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted">No hay facturas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $facturas->links() }}
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.btn-anular', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Anular factura?',
            text: 'Se restaurará el stock de todos los productos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
        }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf }).then(() => location.reload()); });
    });
});
</script>
@endpush
