<p class="text-muted small mb-3">Una vez eliminada tu cuenta, todos los datos asociados se perderán permanentemente.</p>

<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    Eliminar Cuenta
</button>

<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf @method('delete')
                <div class="modal-header">
                    <h5 class="modal-title">¿Eliminar cuenta?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esta acción es irreversible. Ingresa tu contraseña para confirmar.</p>
                    <div class="mb-3">
                        <label for="delete-password" class="form-label">Contraseña</label>
                        <input id="delete-password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Tu contraseña">
                        @error('password', 'userDeletion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>