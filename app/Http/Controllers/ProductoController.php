<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::withTrashed()->with('categoria')->get();
        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');
        return view('productos.index', compact('productos', 'tasas'));
    }

    public function create()
    {
        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');
        return view('productos.create', compact('tasas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'unidades_por_paquete' => 'required|integer|min:1',
            'stock_paquetes' => 'required|integer|min:0',
            'stock_unidades' => 'required|integer|min:0',
            'costo_usd' => 'required|numeric|min:0',
            'margen_detal' => 'required|numeric|min:0',
            'margen_mayor' => 'required|numeric|min:0',
            'tiene_iva' => 'boolean',
            'fuente_tasa' => 'required|in:promedio,dolar,bcv',
            'estado' => 'required|in:disponible,no_disponible',
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['precio_unitario_usd'] = round($data['costo_usd'] * (1 + $data['margen_detal'] / 100), 2);
        $data['precio_mayor_usd'] = round($data['costo_usd'] * (1 + $data['margen_mayor'] / 100), 2);

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');
        return view('productos.edit', compact('producto', 'tasas'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'unidades_por_paquete' => 'required|integer|min:1',
            'stock_paquetes' => 'required|integer|min:0',
            'stock_unidades' => 'required|integer|min:0',
            'costo_usd' => 'required|numeric|min:0',
            'margen_detal' => 'required|numeric|min:0',
            'margen_mayor' => 'required|numeric|min:0',
            'tiene_iva' => 'boolean',
            'fuente_tasa' => 'required|in:promedio,dolar,bcv',
            'estado' => 'required|in:disponible,no_disponible',
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['precio_unitario_usd'] = round($data['costo_usd'] * (1 + $data['margen_detal'] / 100), 2);
        $data['precio_mayor_usd'] = round($data['costo_usd'] * (1 + $data['margen_mayor'] / 100), 2);

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto desactivado correctamente.');
    }

    public function restore($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);
        $producto->restore();
        return redirect()->route('productos.index')->with('success', 'Producto activado nuevamente.');
    }
}
