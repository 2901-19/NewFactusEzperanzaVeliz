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

        $data['unidad_medida'] = $request->input('unidad_medida');
        $data['stock_actual'] = $this->esPesable($data['unidad_medida']) ? 0 : (float) ($data['stock_actual'] ?? 0);

        $presentaciones = $this->prepararPresentaciones($request, $data['costo_usd'], $request->input('presentaciones', []), $data['unidad_medida']);

        $producto = Producto::create(collect($data)->except('presentaciones')->toArray());
        $producto->presentaciones()->createMany($presentaciones);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);

        if ($producto->trashed()) {
            return redirect()->route('productos.index')
                ->withErrors(['error' => 'El producto "'.$producto->nombre.'" está desactivado. Actívalo para poder editarlo.']);
        }

        ['mapaTasas' => $mapaTasas, 'opcionesTasa' => $opcionesTasa] = $this->tasasParaVista();
        $producto->load('presentaciones');

        $producto->presentaciones->each(function ($presentacion) use ($mapaTasas, $opcionesTasa) {
            if (! $opcionesTasa->has($presentacion->fuente_tasa) && $mapaTasas->has($presentacion->fuente_tasa)) {
                $opcionesTasa->put($presentacion->fuente_tasa, $mapaTasas[$presentacion->fuente_tasa].' (inactiva)');
            }
        });

        $impuestos = Impuesto::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'mapaTasas', 'opcionesTasa', 'impuestos'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);

        if ($producto->trashed()) {
            return redirect()->route('productos.index')
                ->withErrors(['error' => 'El producto "'.$producto->nombre.'" está desactivado. Actívalo para poder editarlo.']);
        }

        $data = $this->validar($request);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['unidad_medida'] = $request->input('unidad_medida');
        if ($this->esPesable($data['unidad_medida'])) {
            $data['stock_actual'] = 0;
        } else {
            $data['stock_actual'] = (float) ($data['stock_actual'] ?? $producto->stock_actual ?? 0);
        }

        $producto->update(collect($data)->except('presentaciones')->toArray());
        $this->sincronizarPresentaciones($request, $producto, $data['costo_usd'], $request->input('presentaciones', []), $data['unidad_medida']);

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
            'unidad_medida' => 'required|in:unidad,kg',
            'stock_actual' => 'nullable|numeric|min:0',
            'presentaciones' => 'required|array|min:1',
            'presentaciones.*.nombre' => 'required|string|max:100',
            'presentaciones.*.factor_conversion' => 'required|numeric|min:0.0001',
            'presentaciones.*.margen' => 'required|numeric|min:0',
            'presentaciones.*.fuente_tasa' => ['required', Rule::exists('tasa_cambios', 'tipo')],
            'presentaciones.*.activa' => 'boolean',
            'impuesto_id' => 'nullable|exists:impuestos,id',
            'estado' => 'required|in:disponible,no_disponible',
        ]);
    }

    private function esPesable(string $unidadMedida): bool
    {
        return $unidadMedida === 'kg';
    }

    private function prepararPresentaciones(Request $request, float $costoUsd, array $presentaciones, string $unidadMedida): array
    {
        if ($this->esPesable($unidadMedida)) {
            $margen = (float) ($presentaciones[0]['margen'] ?? 0);

            return [[
                'nombre' => 'Kilogramo',
                'factor_conversion' => 1,
                'margen' => $margen,
                'fuente_tasa' => $presentaciones[0]['fuente_tasa'] ?? 'promedio',
                'precio_usd' => PrecioService::precioPresentacion($costoUsd, $margen, 1),
                'activa' => true,
            ]];
        }

        $resultado = [];
        foreach ($presentaciones as $index => $presentacion) {
            $margen = (float) $presentacion['margen'];
            $factor = (float) $presentacion['factor_conversion'];
            $resultado[] = [
                'nombre' => $presentacion['nombre'],
                'factor_conversion' => $factor,
                'margen' => $margen,
                'fuente_tasa' => $presentacion['fuente_tasa'],
                'precio_usd' => PrecioService::precioPresentacion($costoUsd, $margen, $factor),
                'activa' => $request->boolean("presentaciones.{$index}.activa"),
            ];
        }

        return $resultado;
    }

    private function sincronizarPresentaciones(Request $request, Producto $producto, float $costoUsd, array $presentaciones, string $unidadMedida): void
    {
        if ($this->esPesable($unidadMedida)) {
            $idEnviado = (int) ($presentaciones[0]['id'] ?? 0);
            $margen = (float) ($presentaciones[0]['margen'] ?? 0);
            $datos = [
                'nombre' => 'Kilogramo',
                'factor_conversion' => 1,
                'margen' => $margen,
                'fuente_tasa' => $presentaciones[0]['fuente_tasa'] ?? 'promedio',
                'precio_usd' => PrecioService::precioPresentacion($costoUsd, $margen, 1),
                'activa' => true,
            ];

            if ($idEnviado) {
                $producto->presentaciones()->whereKeyNot($idEnviado)->delete();
                $producto->presentaciones()->whereKey($idEnviado)->update($datos);
            } else {
                $producto->presentaciones()->delete();
                $producto->presentaciones()->create($datos);
            }

            return;
        }

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
                'fuente_tasa' => $presentacion['fuente_tasa'],
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
