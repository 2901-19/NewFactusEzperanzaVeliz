@extends('layouts.app')
@section('titulo', 'Factura #' . $factura->correlativo)
@section('contenido')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Factura #{{ $factura->correlativo }}</span>
        <span class="badge bg-{{ $factura->estado_credito === 'pendiente' ? 'warning text-dark' : 'success' }}">
            {{ $factura->estado === 'credito' ? 'Crédito: ' . ucfirst($factura->estado_credito) : 'Contado' }}
        </span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Fecha:</strong> {{ $factura->fecha_venta }}<br>
                <strong>Método de Pago:</strong> {{ ucfirst(str_replace('_', ' ', $factura->metodo_pago)) }}<br>
                <strong>Tasa de Cambio:</strong> {{ number_format($factura->tasa_cambio, 2) }}
            </div>
            <div class="col-md-6">
                @if ($factura->cliente)
                    <strong>Cliente:</strong> {{ $factura->cliente->nombre }}<br>
                    <strong>Cédula:</strong> {{ $factura->cliente->ci }}<br>
                    <strong>Teléfono:</strong> {{ $factura->cliente->telefono ?? 'N/A' }}
                @else
                    <span class="text-muted">Factura de contado (sin cliente)</span>
                @endif
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Tipo</th>
                    <th>Precio Bs</th>
                    <th>Precio USD</th>
                    <th>Subtotal Bs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->items as $item)
                <tr>
                    <td>{{ $item->producto->nombre ?? 'Producto' }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($item->tipo_venta) }}</span></td>
                    <td>Bs {{ number_format($item->precio_unitario_bs, 2) }}</td>
                    <td>${{ number_format($item->precio_unitario_usd, 2) }}</td>
                    <td>Bs {{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end"><strong>Subtotal Bs:</strong></td>
                    <td>Bs {{ number_format($factura->subtotal_bs, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end"><strong>IVA (16%):</strong></td>
                    <td>Bs {{ number_format($factura->iva_bs, 2) }}</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="5" class="text-end"><strong>Total Bs:</strong></td>
                    <td>Bs {{ number_format($factura->total_bs, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end"><strong>Total USD:</strong></td>
                    <td>${{ number_format($factura->total_usd, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button class="btn btn-outline-primary ms-2" id="btn-imprimir-ticket">
            <i class="bi bi-printer"></i> Imprimir Ticket
        </button>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-imprimir-ticket')?.addEventListener('click', function () {
        Swal.fire({
            title: '¿Imprimir ticket?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, imprimir',
            cancelButtonText: 'Cancelar',
        }).then((r) => { if (r.isConfirmed) window.location.href = '{{ route("herramientas.imprimir-factura", $factura->id) }}'; });
    });
});
</script>
@endpush
    </div>
</div>
@endsection
