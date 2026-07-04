<form method="post" action="{{ route('profile.update') }}">
    @csrf @method('patch')
    <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input id="usuario" name="usuario" type="text" class="form-control @error('usuario') is-invalid @enderror" value="{{ old('usuario', $user->usuario) }}" required>
        @error('usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Guardar</button>
        @if (session('status') === 'profile-updated')
            <small class="text-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">Guardado.</small>
        @endif
    </div>
</form>