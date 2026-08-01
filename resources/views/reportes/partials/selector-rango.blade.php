@php
    $rangoDesde = \Carbon\Carbon::parse($desde)->startOfDay();
    $rangoHasta = \Carbon\Carbon::parse($hasta)->startOfDay();
    $hoy = \Carbon\Carbon::today();
    $rangoActivo = null;
    if ($rangoDesde->eq($hoy) && $rangoHasta->eq($hoy)) {
        $rangoActivo = 'hoy';
    } elseif ($rangoDesde->eq($hoy->copy()->startOfWeek()) && $rangoHasta->eq($hoy)) {
        $rangoActivo = 'semana';
    } elseif ($rangoDesde->eq($hoy->copy()->startOfMonth()) && $rangoHasta->eq($hoy)) {
        $rangoActivo = 'mes';
    }
@endphp
<div class="col-6 col-md-4 col-xl-auto">
    <label class="form-label small text-muted fw-semibold">Rango rápido</label>
    <div class="btn-group btn-group-sm w-100" role="group">
        <button type="button" class="btn btn-outline-primary {{ $rangoActivo === 'hoy' ? 'active' : '' }}" data-rango="hoy">Hoy</button>
        <button type="button" class="btn btn-outline-primary {{ $rangoActivo === 'semana' ? 'active' : '' }}" data-rango="semana">Semana</button>
        <button type="button" class="btn btn-outline-primary {{ $rangoActivo === 'mes' ? 'active' : '' }}" data-rango="mes">Mes</button>
    </div>
</div>
