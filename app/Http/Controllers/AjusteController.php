<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AjusteController extends Controller
{
    public function editarPrecios()
    {
        $productos = Producto::whereNull('deleted_at')->with('categoria')->get();
        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');
        return view('productos.ajustar-precios', compact('productos', 'tasas'));
    }

    public function guardarPrecio(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'costo_usd' => 'required|numeric|min:0',
                'margen_detal' => 'required|numeric|min:0',
                'margen_mayor' => 'required|numeric|min:0',
            ]);

            $precioUnitario = round($validated['costo_usd'] * (1 + $validated['margen_detal'] / 100), 2);
            $precioMayor = round($validated['costo_usd'] * (1 + $validated['margen_mayor'] / 100), 2);

            $producto->update($validated + [
                'precio_unitario_usd' => $precioUnitario,
                'precio_mayor_usd' => $precioMayor,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente',
                'precio_unitario_usd' => $precioUnitario,
                'precio_mayor_usd' => $precioMayor,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        }
    }

    public function editarInventario()
    {
        $productos = Producto::whereNull('deleted_at')->with('categoria')->get();
        return view('productos.ajustar-inventario', compact('productos'));
    }

    public function ajustarInventario(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'cantidad' => 'required|integer|min:1',
                'operacion' => 'required|in:+,-',
            ]);

            $totalActual = ($producto->stock_paquetes * $producto->unidades_por_paquete) + $producto->stock_unidades;
            $upp = $producto->unidades_por_paquete;

            if ($upp <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto debe tener al menos 1 unidad por paquete para ajustar inventario',
                ], 422);
            }

            if ($validated['operacion'] === '+') {
                $nuevoTotal = $totalActual + $validated['cantidad'];
            } else {
                $nuevoTotal = $totalActual - $validated['cantidad'];
                if ($nuevoTotal < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes restar más unidades de las que hay en stock',
                    ], 422);
                }
            }

            $nuevosPaquetes = intdiv($nuevoTotal, $upp);
            $nuevasUnidades = $nuevoTotal % $upp;

            $producto->update([
                'stock_paquetes' => $nuevosPaquetes,
                'stock_unidades' => $nuevasUnidades,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventario actualizado correctamente',
                'stock_paquetes' => $nuevosPaquetes,
                'stock_unidades' => $nuevasUnidades,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        }
    }
}
