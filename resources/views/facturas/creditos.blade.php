@extends('layouts.app')
@section('titulo', 'Créditos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Facturas a Crédito</h2>
</div>
<div class="table-responsive">
    <table id="creditosTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Correlativo</th>
                <th>Cliente</th>
                <th>Total Bs</th>
                <th>Total USD</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturas as $f)
            <tr class="{{ $f->estado_credito === 'cancelado' ? 'table-success' : '' }}">
                <td>{{ $f->correlativo }}</td>
                <td>{{ $f->cliente->nombre ?? 'N/A' }}</td>
                <td>{{ number_format($f->total_bs, 2) }}</td>
                <td>${{ number_format($f->total_usd, 2) }}</td>
                <td>{{ $f->fecha_venta }}</td>
                <td>
                    @if ($f->estado_credito === 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @else
                        <span class="badge bg-success">Cancelado</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('facturas.show', $f->id) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if ($f->estado_credito === 'pendiente')
                        <button class="btn btn-sm btn-success btn-pagar" data-url="{{ route('facturas.pagar-credito', $f->id) }}" data-correlativo="{{ $f->correlativo }}">
                            <i class="bi bi-check-lg"></i> Pagar
                        </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-muted small">Total: {{ $facturas->count() }} facturas</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#creditosTable').DataTable({
        language: window.DataTableSpanish,
        order: [[4, 'desc']],
        pageLength: 25,
    });
    $(document).on('click', '.btn-pagar', function () {
        const btn = $(this);
        Swal.fire({
            title: '¿Marcar como pagado?',
            text: 'Crédito #' + btn.data('correlativo'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Sí, pagado',
            cancelButtonText: 'Cancelar',
        }).then((r) => { if (r.isConfirmed) $.post(btn.data('url'), { _token: csrf }).then(() => location.reload()); });
    });
});
</script>
@endpush
