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
            <span class="badge {{ $variacion > 0 ? 'bg-success' : ($variacion < 0 ? 'bg-danger' : 'bg-secondary') }}">
                @if ($variacion > 0)
                    ▲ {{ number_format($variacion, 1) }}%
                @elseif ($variacion < 0)
                    ▼ {{ number_format(abs($variacion), 1) }}%
                @else
                    —
                @endif
            </span>
            <small class="text-muted ms-1">vs período anterior</small>
        </div>
    </div>
</div>
