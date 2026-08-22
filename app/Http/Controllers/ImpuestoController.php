<?php

namespace App\Http\Controllers;

use App\Models\Impuesto;
use App\Models\Producto;
use Illuminate\Http\Request;

class ImpuestoController extends Controller
{
    public function index()
    {
        $impuestos = Impuesto::all();

        return view('impuestos.index', compact('impuestos'));
    }

    public function create()
    {
        return view('impuestos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        Impuesto::create($data);

        return redirect()->route('impuestos.index')->with('success', 'Impuesto creado correctamente.');
    }

    public function edit(Impuesto $impuesto)
    {
        return view('impuestos.edit', compact('impuesto'));
    }

    public function update(Request $request, Impuesto $impuesto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        $impuesto->update($data);

        return redirect()->route('impuestos.index')->with('success', 'Impuesto actualizado correctamente.');
    }

    public function destroy(Impuesto $impuesto)
    {
        $productos = Producto::where('impuesto_id', $impuesto->id)->count();

        if ($productos > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar el impuesto "'.$impuesto->nombre.'" porque lo usan '.$productos.' producto(s).']);
        }

        $impuesto->delete();

        return redirect()->route('impuestos.index')->with('success', 'Impuesto eliminado correctamente.');
    }
}
