<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Factura;
use App\Models\ItemFactura;
use App\Models\Cliente;
use App\Models\TasaCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function pos()
    {
        $productos = Producto::where('estado', 'disponible')->whereNull('deleted_at')->get();
        $clientes = Cliente::all();
        $tasas = TasaCambio::select('tipo', 'monto')
            ->selectRaw('MAX(created_at) as ultima')
            ->groupBy('tipo', 'monto')
            ->get()
            ->keyBy('tipo');

        return view('facturas.pos', compact('productos', 'clientes', 'tasas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|string',
            'cliente_id' => 'nullable|exists:clientes,id',
            'estado' => 'required|in:contado,credito',
        ]);

        if ($request->estado === 'credito' && !$request->cliente_id) {
            return back()->withErrors(['cliente_id' => 'Debe seleccionar un cliente para facturas a crédito.']);
        }

        $correlativo = strtoupper(substr(uniqid(), -7));

        $productos = Producto::whereIn('id', collect($request->items)->pluck('producto_id'))->get()->keyBy('id');

        $itemsData = [];
        $subtotalBs = 0;
        $ivaBs = 0;
        $totalUsd = 0;

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $producto = $productos[$item['producto_id']];
                $cantidad = (int) $item['cantidad'];

                $precioUsd = $cantidad >= $producto->cantidad_minima_mayor
                    ? $producto->precio_mayor_usd
                    : $producto->precio_unitario_usd;

                $tipoVenta = $cantidad >= $producto->cantidad_minima_mayor ? 'mayor' : 'unitario';

                $this->descontarStock($producto, $cantidad);

                $tasa = $this->obtenerTasa($producto->fuente_tasa);
                $precioBs = $precioUsd * $tasa;
                $subtotalItemBs = $precioBs * $cantidad;

                $itemsData[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'tipo_venta' => $tipoVenta,
                    'precio_unitario_usd' => $precioUsd,
                    'precio_unitario_bs' => $precioBs,
                    'subtotal' => $subtotalItemBs,
                ];

                $subtotalBs += $subtotalItemBs;
                $totalUsd += $precioUsd * $cantidad;

                if ($producto->tiene_iva) {
                    $ivaBs += $subtotalItemBs * 0.16;
                }
            }

            $totalBs = $subtotalBs + $ivaBs;

            $factura = Factura::create([
                'correlativo' => $correlativo,
                'cliente_id' => $request->cliente_id,
                'productos' => $itemsData,
                'tasa_cambio' => $tasa,
                'metodo_pago' => $request->metodo_pago,
                'subtotal_bs' => $subtotalBs,
                'iva_bs' => $ivaBs,
                'total_bs' => $totalBs,
                'total_usd' => $totalUsd,
                'estado' => $request->estado,
                'estado_credito' => $request->estado === 'credito' ? 'pendiente' : null,
                'fecha_venta' => now(),
            ]);

            foreach ($itemsData as $item) {
                $item['factura_id'] = $factura->id;
                ItemFactura::create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'correlativo' => $correlativo,
                'factura_id' => $factura->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function creditos()
    {
        $facturas = Factura::where('estado', 'credito')
            ->with('cliente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('facturas.creditos', compact('facturas'));
    }

    public function show(Factura $factura)
    {
        $factura->load('cliente', 'items.producto');
        return view('facturas.show', compact('factura'));
    }

    public function pagarCredito(Factura $factura)
    {
        if ($factura->estado !== 'credito' || $factura->estado_credito === 'cancelado') {
            return back()->withErrors(['error' => 'Esta factura no está pendiente de crédito.']);
        }

        $factura->update(['estado_credito' => 'cancelado']);

        return redirect()->route('facturas.creditos')->with('success', 'Crédito #' . $factura->correlativo . ' cancelado correctamente.');
    }

    private function descontarStock(Producto $producto, int $cantidad): void
    {
        $restantes = $cantidad;

        if ($producto->stock_unidades >= $restantes) {
            $producto->decrement('stock_unidades', $restantes);
            return;
        }

        $restantes -= $producto->stock_unidades;
        $producto->update(['stock_unidades' => 0]);

        $paquetesNecesarios = (int) ceil($restantes / $producto->unidades_por_paquete);

        if ($producto->stock_paquetes < $paquetesNecesarios) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}");
        }

        $producto->decrement('stock_paquetes', $paquetesNecesarios);

        $unidadesGeneradas = $paquetesNecesarios * $producto->unidades_por_paquete;
        $sobrantes = $unidadesGeneradas - $restantes;

        if ($sobrantes > 0) {
            $producto->increment('stock_unidades', $sobrantes);
        }
    }

    private function obtenerTasa(string $fuente): float
    {
        $tasa = TasaCambio::where('tipo', $fuente)->latest()->first();
        return $tasa ? (float) $tasa->monto : 1;
    }
}
