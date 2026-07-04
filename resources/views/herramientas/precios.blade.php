@extends('layouts.app')
@section('titulo', 'Lista de Precios')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ $productos->count() }} productos disponibles.</p>
    <a href="{{ route('herramientas.precios.pdf') }}" class="btn btn-danger">
        <i class="bi bi-filetype-pdf"></i> Descargar PDF
    </a>
</div>
<div class="table-responsive">
    <table id="preciosTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Producto</th>
                <th>Precio Unitario</th>
                <th>Precio Mayor</th>
                <th>Cant. Mín. Mayor</th>
                <th>IVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            @php
                $tasaValor = $tasas[$p->fuente_tasa] ?? 1;
                $puBs = $p->precio_unitario_usd * $tasaValor;
                $pmBs = $p->precio_mayor_usd * $tasaValor;
            @endphp
            <tr>
                <td class="text-start">{{ $p->nombre }}</td>
                <td>Bs {{ number_format($puBs, 2) }} <small class="text-muted">(${{ number_format($p->precio_unitario_usd, 2) }})</small></td>
                <td>Bs {{ number_format($pmBs, 2) }} <small class="text-muted">(${{ number_format($p->precio_mayor_usd, 2) }})</small></td>
                <td>{{ $p->cantidad_minima_mayor }}</td>
                <td>{{ $p->tiene_iva ? 'Sí' : 'No' }}</td>
            </tr>
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
