@extends('layouts.app')
@section('titulo', isset($categoria) ? 'Editar Categoría' : 'Nueva Categoría')
@section('contenido')
<div class="card">
    <div class="card-header">{{ isset($categoria) ? 'Editar' : 'Nueva' }} Categoría</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($categoria) ? route('categorias.update', $categoria->id) : route('categorias.store') }}">
            @csrf
            @isset($categoria) @method('PUT') @endisset

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre', $categoria->nombre ?? '') }}" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($categoria) ? 'Actualizar' : 'Guardar' }}</button>
            <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>

        @isset($categoria)
        <hr class="my-4">
        <h5>Productos de esta categoría</h5>
        <p class="text-muted small">Marque los productos que deben pertenecer a esta categoría. Los que desmarque quedarán sin categoría.</p>
        <form method="POST" action="{{ route('categorias.asignar-productos', $categoria) }}">
            @csrf
            <div class="table-responsive">
                <table id="dt-categoria-productos" class="table table-bordered table-striped thumbs-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px"><input type="checkbox" id="check-all"></th>
                            <th>Ref.</th>
                            <th class="text-start">Producto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $p)
                        <tr>
                            <td>
                                <input type="checkbox" name="producto_ids[]" value="{{ $p->id }}"
                                    {{ $p->categoria_id == $categoria->id ? 'checked' : '' }}>
                            </td>
                            <td>
                                @if ($p->imagen_url)
                                    <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="thumb">
                                @else
                                    <span class="text-muted sin-ref">Sin ref</span>
                                @endif
                            </td>
                            <td class="text-start">{{ $p->nombre }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary mt-2">
                <i class="bi bi-check-lg"></i> Guardar asignación
            </button>
        </form>
        @endisset
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable && document.getElementById('dt-categoria-productos')) {
        $('#dt-categoria-productos').DataTable({
            columnDefs: [{ orderable: false, targets: 0 }],
            language: window.DataTableSpanish,
        });
    }
    document.getElementById('check-all')?.addEventListener('change', function () {
        document.querySelectorAll('input[name="producto_ids[]"]').forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endpush
