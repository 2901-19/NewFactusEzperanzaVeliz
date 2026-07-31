<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Factus') }} - Iniciar Sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <div class="login-page-bg" aria-hidden="true"></div>
    <div class="login-page-overlay" aria-hidden="true"></div>

    <main class="login-wrapper">
        <section class="login-ticket">
            <div class="login-brand">
                <div class="login-logo">F</div>
                <div class="login-brand-text">
                    <h1 class="login-title">Factus</h1>
                    <p class="login-subtitle">Esperanza Veliz</p>
                </div>
            </div>

            <div class="login-divider">
                <div class="login-barcode" aria-hidden="true"></div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="login-field">
                    <label for="usuario" class="login-label">Usuario</label>
                    <div class="login-input-wrap">
                        <i class="bi bi-person login-input-icon"></i>
                        <input id="usuario" type="text" name="usuario" placeholder="Ingresa tu usuario"
                               class="login-input @error('usuario') is-invalid @enderror"
                               value="{{ old('usuario') }}" required autofocus autocomplete="username">
                    </div>
                    @error('usuario')
                        <div class="login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="login-field">
                    <label for="password" class="login-label">Contraseña</label>
                    <div class="login-input-wrap">
                        <i class="bi bi-lock login-input-icon"></i>
                        <input id="password" type="password" name="password" placeholder="Ingresa tu contraseña"
                               class="login-input login-input--password @error('password') is-invalid @enderror"
                               required autocomplete="current-password">
                        <button type="button" class="login-eye" aria-label="Mostrar contraseña"
                                data-login-toggle-password>
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="login-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Ingresar
                </button>
            </form>

            <div class="login-footer">
                © {{ date('Y') }} Factus Esperanza Veliz · Punto de Venta
            </div>
        </section>
    </main>

    <script>
        document.querySelector('[data-login-toggle-password]')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            this.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    </script>
</body>
</html>
