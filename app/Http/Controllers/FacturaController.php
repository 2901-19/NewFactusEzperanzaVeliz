<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\Impuesto;
use App\Models\ItemFactura;
use App\Models\Producto;
use App\Models\TasaCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function index()
    {
        $facturas = Factura::with('cliente', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('facturas.index', compact('facturas'));
    }

    public function anular(Factura $factura)
    {
        if ($factura->estado === 'anulada') {
            return back()->withErrors(['error' => 'La factura ya está anulada.']);
        }

        if ($factura->estado_credito === 'cancelado') {
            return back()->withErrors(['error' => 'No se puede anular un crédito ya cobrado. Reversa el pago primero.']);
        }

        DB::transaction(function () use ($factura) {
            $factura->update(['estado' => 'anulada', 'estado_credito' => null]);

            foreach ($factura->items as $item) {
                $producto = Producto::withTrashed()->find($item->producto_id);
                if ($producto) {
                    $producto->increment('stock_unidades', $item->cantidad);
                }
            }
        });

        return redirect()->route('facturas.index')->with('success', 'Factura N° '.$factura->correlativo.' anulada. Stock restaurado.');
    }

    public function pos()
    {
        $productos = Producto::where('estado', 'disponible')->whereNull('deleted_at')->get();
        $clientes = Cliente::all();
        $tasas = TasaCambio::ultimasPorTipo();

        $iva = Impuesto::latest('fecha')->first();
        $ivaPorcentaje = $iva ? (float) $iva->porcentaje : 16;
        $tasaReferenciaTipo = Configuracion::obtener('tasa_referencia', 'bcv');

        return view('facturas.pos', compact('productos', 'clientes', 'tasas', 'ivaPorcentaje', 'tasaReferenciaTipo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.tipo_venta' => 'required|in:unitario,mayor',
            'metodo_pago' => 'required|string',
            'pagos' => 'required_if:metodo_pago,mixto|array|size:2',
            'pagos.*.metodo' => 'required|in:efectivo,punto,biopago,divisas,pago_movil,transferencia|distinct',
            'pagos.*.monto' => 'required|numeric|min:0.01',
            'cliente_id' => 'nullable|exists:clientes,id',
            'estado' => 'required|in:contado,credito',
        ]);

        if ($request->estado === 'credito' && ! $request->cliente_id) {
            return back()->withErrors(['cliente_id' => 'Debe seleccionar un cliente para facturas a crédito.']);
        }

        $correlativo = strtoupper(substr(uniqid(), -7));

        $iva = Impuesto::latest('fecha')->first();
        $ivaPorcentaje = $iva ? (float) $iva->porcentaje : 16;

        $itemsData = [];
        $subtotalBs = 0;
        $ivaBs = 0;

        DB::beginTransaction();
        try {
            $productos = Producto::whereIn('id', collect($request->items)->pluck('producto_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($request->items as $item) {
                $producto = $productos[$item['producto_id']] ?? null;
                if (! $producto) {
                    throw new \Exception('Uno de los productos ya no está disponible.');
                }
                $cantidad = (int) $item['cantidad'];

                if ($cantidad > $producto->stock_total) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_total} uds.");
                }

                $esMayor = ($item['tipo_venta'] ?? 'unitario') === 'mayor';
                $precioUsd = $esMayor ? $producto->precio_mayor_usd : $producto->precio_unitario_usd;
                $tipoVenta = $esMayor ? 'mayor' : 'unitario';

                if ((float) $precioUsd <= 0) {
                    throw new \Exception("El producto {$producto->nombre} no tiene precio configurado.");
                }

                $tasa = $this->obtenerTasa($producto->fuente_tasa);
                $precioBs = $precioUsd * $tasa;
                $subtotalItemBs = round($precioBs * $cantidad, 2);

                $this->descontarStock($producto, $cantidad);

                $itemsData[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'tipo_venta' => $tipoVenta,
                    'precio_unitario_usd' => $precioUsd,
                    'precio_unitario_bs' => round($precioBs, 2),
                    'subtotal' => $subtotalItemBs,
                ];

                $subtotalBs = round($subtotalBs + $subtotalItemBs, 2);

                if ($producto->tiene_iva) {
                    $ivaBs = round($ivaBs + $subtotalItemBs * ($ivaPorcentaje / 100), 2);
                }
            }

            $totalBs = round($subtotalBs + $ivaBs, 2);

            $tasaReferencia = $this->obtenerTasa(Configuracion::obtener('tasa_referencia', 'bcv'));
            $totalUsd = round($totalBs / $tasaReferencia, 2);
            $tasaEfectiva = $tasaReferencia;

            $detallePago = null;
            if ($request->metodo_pago === 'mixto') {
                $pagos = $request->input('pagos', []);
                $sumaPagos = round(array_sum(array_column($pagos, 'monto')), 2);

                if (abs($sumaPagos - $totalBs) > 0.01) {
                    throw new \Exception("Los montos del pago mixto deben sumar el total de la factura (Bs {$totalBs}).");
                }

                $detallePago = array_map(function ($p) {
                    return ['metodo' => $p['metodo'], 'monto' => round((float) $p['monto'], 2)];
                }, $pagos);
            }

            $factura = Factura::create([
                'correlativo' => $correlativo,
                'cliente_id' => $request->cliente_id,
                'user_id' => auth()->id(),
                'productos' => $itemsData,
                'tasa_cambio' => round($tasaEfectiva, 2),
                'metodo_pago' => $request->estado === 'credito' ? 'credito' : $request->metodo_pago,
                'detalle_pago' => $detallePago,
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
            ->paginate(20);

        $tasaReferencia = TasaCambio::ultimaDe(Configuracion::obtener('tasa_referencia', 'bcv'));
        $tasaVigente = $tasaReferencia ? (float) $tasaReferencia->monto : null;

        return view('facturas.creditos', compact('facturas', 'tasaVigente'));
    }

    public function show(Factura $factura)
    {
        $factura->load('cliente', 'items.producto');

        return view('facturas.show', compact('factura'));
    }

    public function pagarCredito(Request $request, Factura $factura)
    {
        if ($factura->estado !== 'credito' || $factura->estado_credito === 'cancelado') {
            return back()->withErrors(['error' => 'Esta factura no está pendiente de crédito.']);
        }

        $request->validate([
            'metodo_pago' => 'required|in:efectivo,punto,biopago,divisas,pago_movil,transferencia',
        ]);

        $tasaVigente = $this->obtenerTasa(Configuracion::obtener('tasa_referencia', 'bcv'));
        $pagoUsd = (float) $factura->total_usd;
        $pagoBs = round($pagoUsd * $tasaVigente, 2);

        $factura->update([
            'metodo_pago' => $request->metodo_pago,
            'estado_credito' => 'cancelado',
            'pago_bs' => $pagoBs,
            'fecha_pago' => now(),
        ]);

        return redirect()->route('facturas.creditos')
            ->with('success', 'Crédito N° '.$factura->correlativo.' cobrado correctamente: Bs '.number_format($pagoBs, 2).' (US$ '.number_format($pagoUsd, 2).').');
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
        $tasa = TasaCambio::ultimaDe($fuente);

        if (! $tasa || (float) $tasa->monto <= 0) {
            throw new \RuntimeException("La tasa de cambio '{$fuente}' no está configurada. Actualicela antes de vender.");
        }

        return (float) $tasa->monto;
    }
}
