@extends('layouts.app')
@section('titulo', 'Dashboard')
@section('contenido')
<div class="row">
    <div class="col-12">
        <h2>Dashboard</h2>
        <p class="text-muted">Bienvenido, {{ Auth::user()->name }}.</p>
    </div>
</div>
@endsection
