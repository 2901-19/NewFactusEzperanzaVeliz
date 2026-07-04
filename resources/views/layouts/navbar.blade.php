<nav class="navbar navbar-light bg-white shadow-sm px-4">
    <span class="navbar-text">
        <i class="bi bi-person"></i> {{ Auth::user()->name }}
        <span class="badge bg-info ms-2">{{ Auth::user()->rol }}</span>
    </span>
</nav>
