@extends('layouts.app')
@section('titulo', 'Nuevo Producto')
@section('contenido')
<form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('productos._form', ['producto' => null])
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
@push('scripts')
<script>
function costoCalc(costoUsd, tasas, fuenteTasa) {
    return {
        costoUsd: costoUsd || 0,
        tasas: tasas || {},
        fuenteTasa: fuenteTasa || Object.keys(tasas || {})[0] || '',
        get tasa() { return parseFloat(this.tasas[this.fuenteTasa]) || 0; },
        get costoBs() { return this.costoUsd * this.tasa; },
    };
}

function inventarioControl(controla, unidadMedida, stockActual) {
    return {
        controla: !!controla,
        unidadMedida: unidadMedida || 'unidad',
        stockActual: stockActual || 0,
    };
}

function presentacionesEditor(costoUsd, presentaciones, tasas) {
    return {
        costoUsd: costoUsd || 0,
        tasas: tasas || {},
        presentaciones: (presentaciones && presentaciones.length ? presentaciones : [{ nombre: 'Unidad', factor_conversion: 1, margen: 0, activa: true }]),
        tasaActual() {
            const sel = document.querySelector('[name="fuente_tasa"]');
            return parseFloat((sel && this.tasas[sel.value]) || 0) || 0;
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
