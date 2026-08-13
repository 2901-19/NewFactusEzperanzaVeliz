<?php

namespace App\Http\Controllers;

use App\Models\Impuesto;
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
        $productos = Producto::withTrashed()->with(['categoria', 'presentaciones'])->get();
        $tasas = TasaCambio::mapaMontos();

        return view('productos.index', compact('productos', 'tasas'));
    }

    public function create()
    {
        ['mapaTasas' => $mapaTasas, 'opcionesTasa' => $opcionesTasa] = $this->tasasParaVista();
        $impuestos = Impuesto::orderBy('nombre')->get();

        return view('productos.create', compact('mapaTasas', 'opcionesTasa', 'impuestos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['controla_inventario'] = $request->boolean('controla_inventario');
        $data['stock_actual'] = $data['controla_inventario'] ? (float) ($data['stock_actual'] ?? 0) : 0;

        $presentaciones = $this->prepararPresentaciones($request, $data['costo_usd'], $request->input('presentaciones', []));

        $producto = Producto::create(collect($data)->except('presentaciones')->toArray());
        $producto->presentaciones()->createMany($presentaciones);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        ['mapaTasas' => $mapaTasas, 'opcionesTasa' => $opcionesTasa] = $this->tasasParaVista();
        if (! $opcionesTasa->has($producto->fuente_tasa) && $mapaTasas->has($producto->fuente_tasa)) {
            $opcionesTasa->put($producto->fuente_tasa, $mapaTasas[$producto->fuente_tasa].' (inactiva)');
        }
        $producto->load('presentaciones');
        $impuestos = Impuesto::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'mapaTasas', 'opcionesTasa', 'impuestos'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $this->validar($request);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['controla_inventario'] = $request->boolean('controla_inventario');
        if ($data['controla_inventario']) {
            $data['stock_actual'] = (float) ($data['stock_actual'] ?? $producto->stock_actual ?? 0);
        } else {
            $data['stock_actual'] = 0;
        }

        $producto->update(collect($data)->except('presentaciones')->toArray());
        $this->sincronizarPresentaciones($request, $producto, $data['costo_usd'], $request->input('presentaciones', []));

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

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'costo_usd' => 'required|numeric|min:0',
            'controla_inventario' => 'boolean',
            'unidad_medida' => 'required|string|max:50',
            'stock_actual' => 'nullable|numeric|min:0',
            'presentaciones' => 'required|array|min:1',
            'presentaciones.*.nombre' => 'required|string|max:100',
            'presentaciones.*.factor_conversion' => 'required|numeric|min:0.0001',
            'presentaciones.*.margen' => 'required|numeric|min:0',
            'presentaciones.*.activa' => 'boolean',
            'impuesto_id' => 'nullable|exists:impuestos,id',
            'fuente_tasa' => ['required', Rule::exists('tasa_cambios', 'tipo')],
            'estado' => 'required|in:disponible,no_disponible',
        ]);
    }

    private function prepararPresentaciones(Request $request, float $costoUsd, array $presentaciones): array
    {
        $resultado = [];
        foreach ($presentaciones as $index => $presentacion) {
            $margen = (float) $presentacion['margen'];
            $factor = (float) $presentacion['factor_conversion'];
            $resultado[] = [
                'nombre' => $presentacion['nombre'],
                'factor_conversion' => $factor,
                'margen' => $margen,
                'precio_usd' => PrecioService::precioPresentacion($costoUsd, $margen, $factor),
                'activa' => $request->boolean("presentaciones.{$index}.activa"),
            ];
        }

        return $resultado;
    }

    private function sincronizarPresentaciones(Request $request, Producto $producto, float $costoUsd, array $presentaciones): void
    {
        $idsEnviados = collect($presentaciones)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($idsEnviados->isNotEmpty()) {
            $producto->presentaciones()
                ->whereNotIn('id', $idsEnviados)
                ->delete();
        }

        foreach ($presentaciones as $index => $presentacion) {
            $margen = (float) $presentacion['margen'];
            $factor = (float) $presentacion['factor_conversion'];
            $datos = [
                'nombre' => $presentacion['nombre'],
                'factor_conversion' => $factor,
                'margen' => $margen,
                'precio_usd' => PrecioService::precioPresentacion($costoUsd, $margen, $factor),
                'activa' => $request->boolean("presentaciones.{$index}.activa"),
            ];

            if (! empty($presentacion['id'])) {
                $producto->presentaciones()->whereKey($presentacion['id'])->update($datos);
            } else {
                $producto->presentaciones()->create($datos);
            }
        }
    }
}
