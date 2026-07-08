<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        $hoyStats = Factura::whereDate('fecha_venta', $hoy)
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total_bs), 0) as total_bs, COALESCE(SUM(total_usd), 0) as total_usd')
            ->first();

        $mesStats = Factura::whereDate('fecha_venta', '>=', $inicioMes)
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total_bs), 0) as total_bs, COALESCE(SUM(total_usd), 0) as total_usd')
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
            ->get()
            ->filter(fn ($p) => $p->stock_total <= 10)
            ->take(10);

        $totalProductos = Producto::whereNull('deleted_at')->count();
        $totalClientes = Cliente::count();

        $masVendidos = DB::table('items_factura')
            ->join('productos', 'items_factura.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(items_factura.cantidad) as total'))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'ventasHoy', 'totalHoyBs', 'totalHoyUsd',
            'ventasMes', 'totalMesBs', 'totalMesUsd',
            'creditosPendientes', 'totalCreditosPendientesBs',
            'productosStockBajo', 'totalProductos', 'totalClientes',
            'masVendidos'
        ));
    }
}
