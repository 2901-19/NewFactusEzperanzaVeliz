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
