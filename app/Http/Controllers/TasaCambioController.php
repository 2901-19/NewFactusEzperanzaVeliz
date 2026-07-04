<?php

namespace App\Http\Controllers;

use App\Models\TasaCambio;
use Illuminate\Http\Request;

class TasaCambioController extends Controller
{
    public function index()
    {
        $tasas = TasaCambio::all()->keyBy('tipo');
        return view('tasas_cambio.index', compact('tasas'));
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|in:promedio,dolar,bcv',
            'monto' => 'required|numeric|min:0',
        ]);

        TasaCambio::updateOrCreate(
            ['tipo' => $data['tipo']],
            [
                'moneda' => 'USD',
                'monto' => $data['monto'],
                'fecha' => now()->toDateString(),
            ]
        );

        return redirect()->route('tasas-cambio.index')
            ->with('success', 'Tasa ' . ucfirst($data['tipo']) . ' actualizada a ' . number_format($data['monto'], 2) . ' USD.');
    }
}
