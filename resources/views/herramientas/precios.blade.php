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
                <th>Producto</th>
                <th>Precio Unitario USD</th>
                <th>Precio Mayor USD</th>
                <th>Cant. Mín. Mayor</th>
                <th>IVA</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $p)
            <tr>
                <td>{{ $p->nombre }}</td>
                <td>${{ number_format($p->precio_unitario_usd, 2) }}</td>
                <td>${{ number_format($p->precio_mayor_usd, 2) }}</td>
                <td>{{ $p->cantidad_minima_mayor }}</td>
                <td>{{ $p->tiene_iva ? 'Sí' : 'No' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">No hay productos disponibles.</td></tr>
            @endforelse
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
