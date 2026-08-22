<?php

namespace App\Http\Controllers;

use App\Services\Notificaciones\RegistroNotificaciones;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function __construct(private RegistroNotificaciones $registro) {}

    public function pendientes(Request $request)
    {
        return response()->json([
            'data' => $this->registro->pendientes($request->user()),
        ]);
    }

    public function posponer(Request $request, string $tipo)
    {
        $notificacion = collect($this->registro->tipos())
            ->first(fn ($n) => $n->tipo() === $tipo);

        if (! $notificacion) {
            return response()->json(['success' => false], 404);
        }

        session([$this->registro->claveSesion($tipo) => $notificacion->posponerHasta(now())->toDateTimeString()]);

        return response()->json(['success' => true]);
    }
}
