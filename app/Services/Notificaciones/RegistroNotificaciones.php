<?php

namespace App\Services\Notificaciones;

use App\Models\User;
use App\Services\Notificaciones\Contracts\Notificacion;
use Illuminate\Support\Str;

class RegistroNotificaciones
{
    /**
     * Instancias de todos los tipos registrados en config/notificaciones.php.
     *
     * @return Notificacion[]
     */
    public function tipos(): array
    {
        return collect(config('notificaciones.tipos', []))
            ->map(fn ($clase) => app($clase))
            ->filter(fn ($notificacion) => $notificacion instanceof Notificacion)
            ->values()
            ->all();
    }

    /**
     * Notificaciones vencidas para el usuario, sin las pospuestas en sesión.
     */
    public function pendientes(User $user): array
    {
        return collect($this->tipos())
            ->filter(fn (Notificacion $n) => $n->permisoRequerido() === null
                || $user->hasPermiso($n->permisoRequerido()))
            ->filter(fn (Notificacion $n) => ! $this->estaPospuesta($n))
            ->filter(fn (Notificacion $n) => $n->debeMostrar(now()))
            ->map(fn (Notificacion $n) => [
                'tipo' => $n->tipo(),
                'titulo' => $n->titulo(),
                'mensaje' => $n->mensaje(),
                'accion_url' => $n->accionUrl(),
                'texto_accion' => $n->textoAccion(),
            ])
            ->all();
    }

    /**
     * Clave de sesión que guarda el posponer de un tipo.
     */
    public function claveSesion(string $tipo): string
    {
        return 'notif_pospuesto_'.Str::slug($tipo);
    }

    public function estaPospuesta(Notificacion $notificacion): bool
    {
        $hasta = session($this->claveSesion($notificacion->tipo()));

        return is_string($hasta) && now()->lt($hasta);
    }
}
