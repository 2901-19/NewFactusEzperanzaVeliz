<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\TasaCambio;
use App\Services\PrecioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    private function tasasParaVista()
    {
        $tasas = TasaCambio::ultimasPorTipo();

        return [
            'mapaTasas' => $tasas->map(fn ($t) => (float) $t->monto),
            'opcionesTasa' => $tasas->filter(fn ($t) => $t->activo)->map(fn ($t) => $t->nombre),
        ];
    }

    public function index()
    {
        $productos = Producto::withTrashed()->with('categoria')->get();
        $tasas = TasaCambio::mapaMontos();

        return view('productos.index', compact('productos', 'tasas'));
    }

    public function create()
    {
        ['mapaTasas' => $mapaTasas, 'opcionesTasa' => $opcionesTasa] = $this->tasasParaVista();

        return view('productos.create', compact('mapaTasas', 'opcionesTasa'));
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
            'fuente_tasa' => ['required', Rule::exists('tasa_cambios', 'tipo')],
            'estado' => 'required|in:disponible,no_disponible',
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data += PrecioService::preciosDesdeMargenes($data['costo_usd'], $data['margen_detal'], $data['margen_mayor']);

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        ['mapaTasas' => $mapaTasas, 'opcionesTasa' => $opcionesTasa] = $this->tasasParaVista();
        if (! $opcionesTasa->has($producto->fuente_tasa) && $mapaTasas->has($producto->fuente_tasa)) {
            $opcionesTasa->put($producto->fuente_tasa, $mapaTasas[$producto->fuente_tasa].' (inactiva)');
        }

        return view('productos.edit', compact('producto', 'mapaTasas', 'opcionesTasa'));
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
            'fuente_tasa' => ['required', Rule::exists('tasa_cambios', 'tipo')],
            'estado' => 'required|in:disponible,no_disponible',
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data += PrecioService::preciosDesdeMargenes($data['costo_usd'], $data['margen_detal'], $data['margen_mayor']);

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
