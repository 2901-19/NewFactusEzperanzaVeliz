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
