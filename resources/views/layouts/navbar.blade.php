<nav class="navbar navbar-light bg-white shadow-sm px-3 px-md-4">
    <div class="d-flex align-items-center w-100">
        <button class="btn btn-outline-secondary me-2" onclick="toggleSidebar()" title="Mostrar/Ocultar menú">
            <i class="bi bi-list"></i>
        </button>
        <span class="navbar-text ms-auto">
            <i class="bi bi-person"></i> {{ Auth::user()->name }}
            <span class="badge bg-info ms-2">{{ Auth::user()->rol }}</span>
        </span>
    </div>
</nav>
