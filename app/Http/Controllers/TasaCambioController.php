<?php

namespace App\Http\Controllers;

use App\Models\TasaCambio;
use Illuminate\Http\Request;

class TasaCambioController extends Controller
{
    public function index()
    {
        $tasas = TasaCambio::all();
        return view('tasas_cambio.index', compact('tasas'));
    }

    public function create()
    {
        return view('tasas_cambio.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|in:promedio,dolar,bcv',
            'moneda' => 'required|string|max:10',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
        ]);

        TasaCambio::create($data);

        return redirect()->route('tasas-cambio.index')->with('success', 'Tasa de cambio creada correctamente.');
    }

    public function edit(TasaCambio $tasaCambio)
    {
        return view('tasas_cambio.edit', compact('tasaCambio'));
    }

    public function update(Request $request, TasaCambio $tasaCambio)
    {
        $data = $request->validate([
            'tipo' => 'required|in:promedio,dolar,bcv',
            'moneda' => 'required|string|max:10',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
        ]);

        $tasaCambio->update($data);

        return redirect()->route('tasas-cambio.index')->with('success', 'Tasa de cambio actualizada correctamente.');
    }

    public function destroy(TasaCambio $tasaCambio)
    {
        $tasaCambio->delete();
        return redirect()->route('tasas-cambio.index')->with('success', 'Tasa de cambio eliminada correctamente.');
    }
}
