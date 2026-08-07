<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\TasaCambio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TasaCambioController extends Controller
{
    private const TASA_REFERENCIA_CLAVE = 'tasa_referencia';

    public function index()
    {
        $tasas = TasaCambio::orderByRaw('activo DESC, tipo ASC')->get()->keyBy('tipo');
        $tasaReferencia = Configuracion::obtener(self::TASA_REFERENCIA_CLAVE, 'bcv');

        return view('tasas_cambio.index', compact('tasas', 'tasaReferencia'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('tasa_cambios', 'tipo')],
            'nombre' => 'nullable|string|max:255',
            'monto' => 'required|numeric|gt:0',
        ]);

        TasaCambio::create([
            'tipo' => $data['tipo'],
            'nombre' => $data['nombre'] ?: ucwords(str_replace('_', ' ', $data['tipo'])),
            'monto' => $data['monto'],
            'fecha' => now()->toDateString(),
            'activo' => true,
        ]);

        return redirect()->route('tasas-cambio.index')
            ->with('success', 'Tasa "' . ($data['nombre'] ?: $data['tipo']) . '" creada correctamente.');
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|exists:tasa_cambios,tipo',
            'monto' => 'required|numeric|gt:0',
        ]);

        $tasa = TasaCambio::where('tipo', $data['tipo'])->latest('id')->first();

        $tasa->update([
            'monto' => $data['monto'],
            'fecha' => now()->toDateString(),
        ]);

        return redirect()->route('tasas-cambio.index')
            ->with('success', 'Tasa ' . $tasa->nombre . ' actualizada a ' . number_format($data['monto'], 2) . ' USD.');
    }

    public function toggleEstado(TasaCambio $tasa)
    {
        $tasaReferencia = Configuracion::obtener(self::TASA_REFERENCIA_CLAVE, 'bcv');

        if ($tasa->activo && $tasa->tipo === $tasaReferencia) {
            return back()->withErrors(['error' => 'No se puede desactivar la tasa de referencia. Elige otra tasa como referencia primero.']);
        }

        $tasa->update(['activo' => !$tasa->activo]);

        $accion = $tasa->activo ? 'activada' : 'desactivada';

        return redirect()->route('tasas-cambio.index')
            ->with('success', 'Tasa "' . $tasa->nombre . '" ' . $accion . ' correctamente.');
    }

    public function fijarReferencia(Request $request)
    {
        $request->validate([
            'referencia' => ['required', 'string', Rule::exists('tasa_cambios', 'tipo')->where('activo', true)],
        ]);

        Configuracion::updateOrCreate(
            ['clave' => self::TASA_REFERENCIA_CLAVE],
            ['valor' => $request->referencia]
        );

        $tasa = TasaCambio::where('tipo', $request->referencia)->latest('id')->first();

        return redirect()->route('tasas-cambio.index')
            ->with('success', 'La tasa "' . $tasa->nombre . '" ahora es la referencia para los cálculos de venta.');
    }
}