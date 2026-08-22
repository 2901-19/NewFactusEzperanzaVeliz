<?php

namespace App\Services\Notificaciones\Contracts;

use Carbon\CarbonInterface;

interface Notificacion
{
    /**
     * Identificador único del tipo de notificación (slug).
     */
    public function tipo(): string;

    /**
     * Slug del permiso requerido para verla; null significa todos los usuarios.
     */
    public function permisoRequerido(): ?string;

    /**
     * Decide si la notificación está vencida y debe mostrarse ahora.
     */
    public function debeMostrar(CarbonInterface $ahora): bool;

    public function titulo(): string;

    public function mensaje(): string;

    public function accionUrl(): ?string;

    public function textoAccion(): ?string;

    /**
     * Hasta cuándo silenciar la notificación cuando el usuario pospone.
     */
    public function posponerHasta(CarbonInterface $ahora): CarbonInterface;
}
