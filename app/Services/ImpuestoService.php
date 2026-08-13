<?php

namespace App\Services;

use App\Models\Impuesto;

class ImpuestoService
{
    public static function porcentajeDe(?Impuesto $impuesto): float
    {
        return $impuesto ? (float) $impuesto->porcentaje : 0;
    }
}
