<?php

namespace App\Services;

use App\Models\Impuesto;

class ImpuestoService
{
    public static function porcentajeVigente(): float
    {
        $impuesto = Impuesto::latest('fecha')->first();

        return $impuesto ? (float) $impuesto->porcentaje : 16;
    }
}
