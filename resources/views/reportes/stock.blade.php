@extends('layouts.app')
@section('titulo', 'Stock Bajo')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Productos con Stock Bajo (&le;10 uds)</h2>
</div>
<div class="table-responsive">
    <table id="stockTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Producto</th>
                <th>Paquetes</th>
                <th>Unidades</th>
                <th>Stock Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            <tr>
                <td class="text-start">{{ $p->nombre }}</td>
                <td>{{ $p->stock_paquetes }}</td>
                <td>{{ $p->stock_unidades }}</td>
                <td>
                    <span class="badge bg-{{ $p->stock_total <= 5 ? 'danger' : 'warning text-dark' }}">
                        {{ $p->stock_total }} uds
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#stockTable').DataTable({
        language: window.DataTableSpanish,
        order: [[3, 'asc']],
        pageLength: 25,
    });
});
</script>
@endpush
