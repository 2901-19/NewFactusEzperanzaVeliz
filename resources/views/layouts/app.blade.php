<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Factus') }} - @yield('titulo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Flash messages ocultos para SweetAlert2 --}}
    @if (session('success'))
        <input type="hidden" id="flash-success" value="{{ session('success') }}">
    @endif
    @if (session('error') || $errors->any())
        <input type="hidden" id="flash-error" value="{{ session('error') ?: $errors->first() }}">
    @endif

    {{-- Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- Sidebar fijo --}}
    @include('layouts.sidebar')

    {{-- Contenido principal --}}
    <div class="main-content">
        @include('layouts.navbar')
        <main class="p-3 p-md-4">
            @yield('contenido')
        </main>
    </div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    </script>
    @stack('scripts')
</body>
</html>
