<nav class="bg-dark text-white p-3" style="width: 240px; min-height: 100vh;">
    <h5 class="text-center mb-4">
        <a href="{{ route('dashboard') }}" class="text-white text-decoration-none">
            <i class="bi bi-shop"></i> Factus
        </a>
    </h5>
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('facturas.pos') }}" class="nav-link text-white {{ request()->routeIs('facturas.pos') ? 'active' : '' }}">
                <i class="bi bi-cart3"></i> Punto de Venta
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('productos.index') }}" class="nav-link text-white {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('clientes.index') }}" class="nav-link text-white {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Clientes
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('impuestos.index') }}" class="nav-link text-white {{ request()->routeIs('impuestos.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Impuestos
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('tasas-cambio.index') }}" class="nav-link text-white {{ request()->routeIs('tasas-cambio.*') ? 'active' : '' }}">
                <i class="bi bi-currency-exchange"></i> Tasas de Cambio
            </a>
        </li>
        <hr class="text-secondary">
        <li class="nav-item">
            <a href="{{ route('profile.edit') }}" class="nav-link text-white">
                <i class="bi bi-person-circle"></i> Mi Perfil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
