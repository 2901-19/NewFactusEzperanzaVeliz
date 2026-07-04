@extends('layouts.app')
@section('titulo', 'Factura #' . $factura->correlativo)
@section('contenido')
<div class="ticket">
    <div class="ticket-header">
        FACTUS<br>
        ESPERANZA VELIZ<br>
        FACTURA DE VENTA
    </div>
    <hr class="sep">
    <div class="row-item">
        <span>N° Factura:</span>
        <span>{{ $factura->correlativo }}</span>
    </div>
    <div class="row-item">
        <span>Fecha:</span>
        <span>{{ $factura->fecha_venta }}</span>
    </div>
    @if ($factura->cliente)
    <div class="row-item">
        <span>Cliente:</span>
        <span>{{ $factura->cliente->nombre }}</span>
    </div>
    @endif
    <hr class="sep">

    @foreach ($factura->items as $item)
    <div class="row-item">
        <span class="desc">{{ $item->cantidad }}x {{ $item->producto->nombre ?? 'Producto' }}</span>
        <span class="monto">Bs {{ number_format($item->subtotal, 2) }}</span>
    </div>
    @endforeach

    <hr class="sep">
    <div class="totales">
        <div class="row-item">
            <span>Subtotal Bs:</span>
            <span>Bs {{ number_format($factura->subtotal_bs, 2) }}</span>
        </div>
        <div class="row-item">
            <span>IVA (16%):</span>
            <span>Bs {{ number_format($factura->iva_bs, 2) }}</span>
        </div>
        <hr class="sep-double">
        <div class="row-item total-final">
            <span>TOTAL Bs:</span>
            <span>Bs {{ number_format($factura->total_bs, 2) }}</span>
        </div>
        <div class="row-item">
            <span>TOTAL USD:</span>
            <span>$ {{ number_format($factura->total_usd, 2) }}</span>
        </div>
    </div>
    <hr class="sep-double">
    <div class="text-center small" style="margin-top:0.5rem;">
        ¡Gracias por su compra!
    </div>
</div>

<div class="text-center mt-3 no-print">
    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    <button class="btn btn-outline-primary ms-2" id="btn-imprimir-ticket">
        <i class="bi bi-printer"></i> Imprimir Ticket
    </button>
</div>

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
@endsection
