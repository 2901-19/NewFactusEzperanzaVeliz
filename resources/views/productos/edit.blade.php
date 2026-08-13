@extends('layouts.app')
@section('titulo', 'Editar Producto')
@section('contenido')
<form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('productos._form', ['producto' => $producto])
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
@push('scripts')
<script>
function inventarioControl(controla, unidadMedida, stockActual) {
    return {
        controla: !!controla,
        unidadMedida: unidadMedida || 'unidad',
        stockActual: stockActual || 0,
    };
}

function presentacionesEditor(costoUsd, presentaciones, tasas, fuenteTasa) {
    return {
        costoUsd: costoUsd || 0,
        tasas: tasas || {},
        fuenteTasa: fuenteTasa || Object.keys(tasas || {})[0] || '',
        presentaciones: (presentaciones && presentaciones.length ? presentaciones : [{ nombre: 'Unidad', factor_conversion: 1, margen: 0, activa: true }]),
        tasaActual() {
            return parseFloat(this.tasas[this.fuenteTasa] || 0) || 0;
        },
        agregar() {
            this.presentaciones.push({ nombre: '', factor_conversion: 1, margen: 0, activa: true });
        },
        quitar(i) {
            if (this.presentaciones.length <= 1) return;
            this.presentaciones.splice(i, 1);
        },
        precioPor(i) {
            const p = this.presentaciones[i] || {};
            const base = this.redon2((this.costoUsd || 0) * (1 + (parseFloat(p.margen) || 0) / 100));
            return this.redon2(base * (parseFloat(p.factor_conversion) || 1));
        },
        redon2(n) { return Math.round((n + Number.EPSILON) * 100) / 100; },
    };
}
</script>
@endpush
