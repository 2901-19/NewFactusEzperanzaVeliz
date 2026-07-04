@extends('layouts.app')
@section('titulo', 'Tasas de Cambio')
@section('contenido')
<div class="mb-3">
    <h2>Tasas de Cambio</h2>
    <p class="text-muted small">Actualiza el monto de cada tasa cuando cambie. La fecha se actualizará automáticamente.</p>
</div>
<div class="table-responsive">
    <table id="dt-tasas" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Tipo</th>
                <th>Moneda</th>
                <th>Monto</th>
                <th>Última Actualización</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tipos = [
                    'promedio' => ['label' => 'Promedio', 'class' => 'bg-info'],
                    'dolar' => ['label' => 'Dólar Paralelo', 'class' => 'bg-primary'],
                    'bcv' => ['label' => 'BCV', 'class' => 'bg-secondary'],
                ];
            @endphp
            @foreach ($tipos as $tipo => $info)
                @php $t = $tasas->get($tipo); @endphp
                <tr>
                    <td><span class="badge {{ $info['class'] }}">{{ $info['label'] }}</span></td>
                    <td>USD</td>
                    <td>
                        <form method="POST" action="{{ route('tasas-cambio.actualizar') }}" class="row g-1 align-items-center">
                            @csrf
                            <input type="hidden" name="tipo" value="{{ $tipo }}">
                            <div class="col-6 col-md-4">
                                <input type="number" step="0.01" name="monto" class="form-control form-control-sm" value="{{ $t ? number_format($t->monto, 2, '.', '') : '' }}" min="0" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </div>
                        </form>
                    </td>
                    <td>{{ $t ? $t->fecha : '—' }}</td>
                    <td>{{ $t ? $t->updated_at->diffForHumans() : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#dt-tasas').DataTable({
        language: window.DataTableSpanish,
        paging: false,
        info: false,
        searching: false,
        ordering: false,
    });
});
</script>
@endpush
