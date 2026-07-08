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
        return view('productos.ajustar-precios', compact('productos'));
    }

    public function guardarPrecio(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'precio_unitario_usd' => 'required|numeric|min:0',
                'precio_mayor_usd' => 'required|numeric|min:0',
                'cantidad_minima_mayor' => 'required|integer|min:0',
            ]);

            $producto->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente',
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
