<div class="col-6 col-md-4 col-xl">
    <div class="card h-100">
        <div class="card-body py-3">
            <div class="small text-muted text-uppercase fw-semibold">{{ $label }}</div>
            <div class="fs-4 fw-bold">
                @if (($formato ?? 'entero') === 'moneda')
                    Bs {{ number_format($valor, 2) }}
                @elseif (($formato ?? 'entero') === 'usd')
                    ${{ number_format($valor, 2) }}
                @else
                    {{ number_format($valor) }}
                @endif
            </div>
        </div>
    </div>
</div>
