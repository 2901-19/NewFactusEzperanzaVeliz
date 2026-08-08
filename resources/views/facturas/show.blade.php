@extends('layouts.app')
@section('titulo', 'Factura N° ' . $factura->correlativo)
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
    @php
        $esCredito = $factura->estado === 'credito';
        $tasaCambio = (float) $factura->tasa_cambio ?: 1;
        $nombresMetodo = [
            'efectivo' => 'Efectivo',
            'punto' => 'Punto de Venta',
            'biopago' => 'Biopago',
            'divisas' => 'Divisas',
            'pago_movil' => 'Pago Móvil',
            'transferencia' => 'Transferencia',
            'mixto' => 'Mixto',
            'credito' => 'Crédito',
        ];
    @endphp

    <div class="row-item row-head">
        <span class="t-cant">CANT</span>
        <span class="desc">DESC</span>
        <span class="t-precio-u">PREC U</span>
        @if (!$esCredito)<span class="t-precio-usd">USD</span>@endif
        <span class="monto">PREC T</span>
    </div>
    @foreach ($factura->items as $item)
    <div class="row-item ticket-item">
        <span class="t-cant">{{ $item->cantidad }}</span>
        <span class="desc">
            {{ $item->producto->nombre ?? 'Producto' }}
            @if ($item->presentacion_nombre)<small> ({{ $item->presentacion_nombre }})</small>@endif
        </span>
        @if ($esCredito)
        <span class="t-precio-u">$ {{ number_format($item->precio_unitario_bs / $tasaCambio, 2) }}</span>
        <span class="monto">$ {{ number_format($item->subtotal / $tasaCambio, 2) }}</span>
        @else
        <span class="t-precio-u">{{ number_format($item->precio_unitario_bs, 2) }}</span>
        <span class="t-precio-usd">{{ number_format($item->precio_unitario_bs / $tasaCambio, 2) }}</span>
        <span class="monto">{{ number_format($item->subtotal, 2) }}</span>
        @endif
    </div>
    @endforeach

    <hr class="sep">
    <div class="totales">
        <div class="row-item">
            <span>{{ $esCredito ? 'Subtotal USD:' : 'Subtotal Bs:' }}</span>
            <span>{{ $esCredito ? '$ ' . number_format($factura->subtotal_bs / $tasaCambio, 2) : 'Bs ' . number_format($factura->subtotal_bs, 2) }}</span>
        </div>
        <div class="row-item">
            <span>IVA (16%):</span>
            <span>{{ $esCredito ? '$ ' . number_format($factura->iva_bs / $tasaCambio, 2) : 'Bs ' . number_format($factura->iva_bs, 2) }}</span>
        </div>
        <hr class="sep-double">
        <div class="row-item total-final">
            <span>{{ $esCredito ? 'TOTAL USD:' : 'TOTAL Bs:' }}</span>
            <span>{{ $esCredito ? '$ ' . number_format($factura->total_usd, 2) : 'Bs ' . number_format($factura->total_bs, 2) }}</span>
        </div>
        @if (!$esCredito)
        <div class="row-item">
            <span>TOTAL USD:</span>
            <span>$ {{ number_format($factura->total_usd, 2) }}</span>
        </div>
        @endif
        <hr class="sep">
        @if ($factura->metodo_pago === 'mixto' && !$esCredito)
        <div class="row-item">
            <span>Pago Mixto:</span>
            <span></span>
        </div>
        @foreach ($factura->detalle_pago ?? [] as $pago)
        <div class="row-item">
            <span>{{ $nombresMetodo[$pago['metodo']] ?? $pago['metodo'] }}:</span>
            <span>Bs {{ number_format($pago['monto'], 2) }}</span>
        </div>
        @endforeach
        @elseif ($factura->estado === 'credito' && $factura->estado_credito === 'cancelado')
        <div class="row-item">
            <span>Pago:</span>
            <span>{{ $nombresMetodo[$factura->metodo_pago] ?? $factura->metodo_pago }}</span>
        </div>
        @elseif ($factura->estado !== 'credito')
        <div class="row-item">
            <span>Pago:</span>
            <span>{{ $nombresMetodo[$factura->metodo_pago] ?? $factura->metodo_pago }}</span>
        </div>
        @endif
        @if ($factura->estado === 'credito')
        <hr class="sep">
        @if ($factura->estado_credito === 'cancelado')
        <div class="row-item">
            <span>Crédito cobrado (US$):</span>
            <span>$ {{ number_format($factura->total_usd, 2) }}</span>
        </div>
        <div class="row-item">
            <span>Cobrado en Bs:</span>
            <span>Bs {{ number_format($factura->pago_bs, 2) }}</span>
        </div>
        <div class="row-item">
            <span>Fecha de cobro:</span>
            <span>{{ $factura->fecha_pago?->format('d/m/Y') ?? '—' }}</span>
        </div>
        @else
        <div class="row-item">
            <span>Crédito pendiente (US$):</span>
            <span>$ {{ number_format($factura->total_usd, 2) }}</span>
        </div>
        @endif
        @endif
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
