<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Services\CatalogoService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();
        $ayer = now()->subDay()->toDateString();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $mismoDiaMesAnterior = now()->subMonthNoOverflow()->toDateString();

        $hoyStats = Factura::whereDate('fecha_venta', $hoy)
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total_bs), 0) as total_bs, COALESCE(SUM(total_usd), 0) as total_usd')
            ->first();

        $ayerStats = Factura::whereDate('fecha_venta', $ayer)
            ->selectRaw('COALESCE(SUM(total_bs), 0) as total_bs')
            ->first();

        $mesStats = Factura::whereDate('fecha_venta', '>=', $inicioMes)
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total_bs), 0) as total_bs, COALESCE(SUM(total_usd), 0) as total_usd')
            ->first();

        $mesAnteriorStats = Factura::whereDate('fecha_venta', '>=', $inicioMesAnterior)
            ->whereDate('fecha_venta', '<=', $mismoDiaMesAnterior)
            ->selectRaw('COALESCE(SUM(total_bs), 0) as total_bs')
            ->first();

        $creditosStats = Factura::where('estado', 'credito')
            ->where('estado_credito', 'pendiente')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(total_bs), 0) as total_bs')
            ->first();

        $ventasHoy = $hoyStats->ventas;
        $totalHoyBs = $hoyStats->total_bs;
        $totalHoyUsd = $hoyStats->total_usd;

        $ventasMes = $mesStats->ventas;
        $totalMesBs = $mesStats->total_bs;
        $totalMesUsd = $mesStats->total_usd;

        $creditosPendientes = $creditosStats->total;
        $totalCreditosPendientesBs = $creditosStats->total_bs;

        $productosStockBajo = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->where('controla_inventario', true)
            ->get()
            ->filter(fn ($p) => (float) $p->stock_actual <= CatalogoService::UMBRAL_STOCK_BAJO)
            ->take(10);

        $totalProductos = Producto::whereNull('deleted_at')->count();
        $totalClientes = Cliente::count();

        $variacionHoy = $this->variacion((float) $totalHoyBs, (float) $ayerStats->total_bs);
        $variacionMes = $this->variacion((float) $totalMesBs, (float) $mesAnteriorStats->total_bs);

        $porDia7 = $this->porDia7();
        $totalSemanaBs = array_sum($porDia7);

        $metodosPago = CatalogoService::metodosPago();
        $metodosHoy = $this->metodosHoy($metodosPago);

        $ultimasFacturas = Factura::with('cliente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $creditosPendientesLista = Factura::where('estado', 'credito')
            ->where('estado_credito', 'pendiente')
            ->with('cliente')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $tasasVigentes = TasaCambio::ultimasPorTipo();

        $masVendidos = DB::table('items_factura')
            ->join('productos', 'items_factura.producto_id', '=', 'productos.id')
            ->join('facturas', 'items_factura.factura_id', '=', 'facturas.id')
            ->whereDate('facturas.fecha_venta', '>=', now()->subDays(30)->toDateString())
            ->where('facturas.estado', '!=', 'anulada')
            ->select('productos.nombre', DB::raw('SUM(items_factura.cantidad) as total'))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $nombreNegocio = Configuracion::obtener('nombre_negocio', config('app.name', 'Factus'));

        return view('dashboard', compact(
            'ventasHoy', 'totalHoyBs', 'totalHoyUsd',
            'ventasMes', 'totalMesBs', 'totalMesUsd',
            'creditosPendientes', 'totalCreditosPendientesBs',
            'productosStockBajo', 'totalProductos', 'totalClientes',
            'variacionHoy', 'variacionMes',
            'porDia7', 'totalSemanaBs',
            'metodosPago', 'metodosHoy',
            'ultimasFacturas', 'creditosPendientesLista',
            'tasasVigentes',
            'masVendidos',
            'nombreNegocio'
        ));
    }

    private function variacion(float $actual, float $anterior): ?float
    {
        if ($anterior <= 0) {
            return null;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    private function porDia7(): array
    {
        $desde = now()->subDays(6)->startOfDay();
        $dias = [];
        for ($i = 0; $i < 7; $i++) {
            $dias[$desde->copy()->addDays($i)->format('Y-m-d')] = 0.0;
        }

        Factura::whereDate('fecha_venta', '>=', now()->subDays(6)->toDateString())
            ->get(['fecha_venta', 'total_bs'])
            ->each(function ($f) use (&$dias) {
                $clave = $f->fecha_venta->format('Y-m-d');
                if (array_key_exists($clave, $dias)) {
                    $dias[$clave] += (float) $f->total_bs;
                }
            });

        return array_map(fn ($monto) => round($monto, 2), $dias);
    }

    private function metodosHoy(array $metodosPago): array
    {
        $metodosHoy = array_fill_keys(array_keys($metodosPago), 0.0);

        Factura::whereDate('fecha_venta', now()->toDateString())
            ->get(['metodo_pago', 'detalle_pago', 'total_bs'])
            ->each(function ($f) use (&$metodosHoy) {
                if ($f->metodo_pago === 'mixto') {
                    foreach ($f->detalle_pago ?? [] as $pago) {
                        if (isset($metodosHoy[$pago['metodo']])) {
                            $metodosHoy[$pago['metodo']] += (float) $pago['monto'];
                        }
                    }
                } elseif (isset($metodosHoy[$f->metodo_pago])) {
                    $metodosHoy[$f->metodo_pago] += (float) $f->total_bs;
                }
            });

        return array_map(fn ($monto) => round($monto, 2), $metodosHoy);
    }
}
