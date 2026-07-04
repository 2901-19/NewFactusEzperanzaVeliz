@extends('layouts.app')
@section('titulo', 'Mi Perfil')
@section('contenido')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Información del Perfil</div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Cambiar Contraseña</div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header text-danger">Eliminar Cuenta</div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
