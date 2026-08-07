@extends('layouts.app')
@section('titulo', 'Tasas de Cambio')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Tasas de Cambio</h2>
        <p class="text-muted small mb-0">Actualiza el monto de cada tasa, crea nuevas o marca la tasa de referencia para los cálculos de venta.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaTasa">
        <i class="bi bi-plus-circle"></i> Nueva Tasa
    </button>
</div>

<div class="table-responsive">
    <table id="dt-tasas" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Código</th>
                <th>Monto</th>
                <th>Última Actualización</th>
                <th>Estado</th>
                <th>Referencia de Venta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasas as $tasa)
                @php
                    $esReferencia = $tasa->tipo === $tasaReferencia;
                @endphp
                <tr class="{{ $tasa->activo ? '' : 'table-secondary' }}">
                    <td>
                        {{ $tasa->nombre ?: ucfirst($tasa->tipo) }}
                        @if ($esReferencia)
                            <span class="badge bg-success ms-1">REFERENCIA</span>
                        @endif
                    </td>
                    <td><code>{{ $tasa->tipo }}</code></td>
                    <td>
                        <form method="POST" action="{{ route('tasas-cambio.actualizar') }}" class="row g-1 align-items-center">
                            @csrf
                            <input type="hidden" name="tipo" value="{{ $tasa->tipo }}">
                            <div class="col-6 col-md-4">
                                <input type="number" step="0.01" name="monto" class="form-control form-control-sm" value="{{ number_format($tasa->monto, 2, '.', '') }}" min="0" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-success" title="Actualizar monto">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </div>
                        </form>
                    </td>
                    <td>{{ $tasa->fecha }} <small class="text-muted d-block">{{ $tasa->updated_at->diffForHumans() }}</small></td>
                    <td>
                        @if ($tasa->activo)
                            <span class="badge bg-success">Activa</span>
                        @else
                            <span class="badge bg-secondary">Inactiva</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($tasa->activo)
                            <form method="POST" action="{{ route('tasas-cambio.referencia') }}">
                                @csrf
                                <input type="hidden" name="referencia" value="{{ $tasa->tipo }}">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           onchange="this.form.submit()"
                                           {{ $esReferencia ? 'checked' : '' }}
                                           title="Usar como tasa de referencia">
                                </div>
                            </form>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <form method="POST" action="{{ route('tasas-cambio.toggle', $tasa) }}" class="d-inline"
                              onsubmit="return confirm('¿{{ $tasa->activo ? 'Desactivar' : 'Activar' }} la tasa " + '{{ addslashes($tasa->nombre ?: ucfirst($tasa->tipo)) }}' + '?')">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $tasa->activo ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                <i class="bi {{ $tasa->activo ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                {{ $tasa->activo ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal Nueva Tasa --}}
<div class="modal fade" id="modalNuevaTasa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tasas-cambio.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-1"></i> Nueva Tasa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Dólar Zelle" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Código (tipo) *</label>
                    <input type="text" name="tipo" class="form-control" placeholder="Ej: zelle" pattern="[a-z0-9_]+" required>
                    <small class="form-text text-muted">Solo minúsculas, números y guiones bajos. Sin espacios.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto *</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control" placeholder="Ej: 60.50" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Crear</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#dt-tasas').DataTable({
        paging: false,
        info: false,
        searching: false,
        ordering: false,
    });
});
</script>
@endpush