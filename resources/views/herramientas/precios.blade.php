@extends('layouts.app')
@section('titulo', 'Lista de Precios')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ $productos->count() }} productos disponibles.</p>
    <div class="d-flex gap-2">
        <a href="{{ route('herramientas.precios.pdf') }}" class="btn btn-danger">
            <i class="bi bi-filetype-pdf"></i> Descargar PDF
        </a>
        <a href="{{ route('herramientas.precios', ['export' => 'json']) }}" class="btn btn-success">
            <i class="bi bi-filetype-json"></i> Descargar JSON
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="preciosTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Producto</th>
                <th>Presentación</th>
                <th>Precio Bs</th>
                <th>Precio USD</th>
                <th>Impuesto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            @foreach ($p->presentaciones->where('activa', true) as $pr)
            @php $tasaDisponible = $tasas->has($p->fuente_tasa); @endphp
            <tr>
                <td class="text-start">{{ $p->nombre }}</td>
                <td>{{ $pr->nombre }}</td>
                <td>
                    @if ($tasaDisponible)
                        Bs {{ number_format($pr->precio_usd * $tasas[$p->fuente_tasa], 2) }}
                    @else
                        <span class="badge bg-danger" title="Configure la tasa '{{ $p->fuente_tasa }}' en Tasas de Cambio">Sin tasa</span>
                    @endif
                </td>
                <td>${{ number_format($pr->precio_usd, 2) }}</td>
                <td>{{ $p->impuesto?->nombre ?? 'No' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#preciosTable').DataTable({
        language: window.DataTableSpanish,
        order: [[0, 'asc']],
        pageLength: 25,
    });
});
</script>
@endpush
